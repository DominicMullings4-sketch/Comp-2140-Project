<?php

/**
 * CustomerMgmt: Application Logic layer.
 * Feature owner: P3 (see the Track-It demo build plan whiteboard).
 * Ported from CustomerService on the based-on-old-srs branch.
 *
 * When a rule fails, the method throws ONE Exception holding every problem, one per line
 * (the same lists of errors CustomerService returned). The UI turns it back into a list with error_list().
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class CustomerMgmt
{
    private DataAccess $db;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess.
     */
    public function __construct(DataAccess $db)
    {
        $this->db = $db;
    }

    /**
     * SR-2.1, SR-2.3, SR-2.4, SR-2.5: reject empty name/phone/address (say which), a phone that isn't
     * exactly 10 digits, and a phone or handle that already belongs to another customer; otherwise INSERT
     * new Customer(...) and return it.
     */
    public function createCustomer(string $name, string $address, string $phone, ?string $socialMedia = null): Customer
    {
        $name = trim($name);
        $address = trim($address);
        $phone = $this->cleanPhone($phone);
        $socialMedia = $this->cleanHandle($socialMedia);

        $this->checkDetails($name, $address, $phone, $socialMedia, '');

        $customer = new Customer($name, $phone, $address, $socialMedia);
        $this->db->execute(
            'INSERT INTO customers (customer_id, name, phone, address, social_media_handle)
             VALUES (:id, :name, :phone, :address, :handle)',
            [
                'id' => $customer->getCustomerId(),
                'name' => $customer->getName(),
                'phone' => $customer->getPhone(),
                'address' => $customer->getAddress(),
                'handle' => $customer->getSocialMediaHandle(),
            ]
        );
        return $customer;
    }

    /**
     * SR-2.2 to SR-2.5: same checks as createCustomer (ignoring this customer's own phone/handle), then
     * the setters, then UPDATE.
     */
    public function editCustomer(Customer $customer, string $name, string $address, string $phone, ?string $socialMedia = null): void
    {
        $name = trim($name);
        $address = trim($address);
        $phone = $this->cleanPhone($phone);
        $socialMedia = $this->cleanHandle($socialMedia);

        // The duplicate check (SR-2.5) applies here too, otherwise editing would be a way around it.
        $this->checkDetails($name, $address, $phone, $socialMedia, $customer->getCustomerId());

        $customer->setName($name);
        $customer->setAddress($address);
        $customer->setPhone($phone);
        $customer->setSocialMediaHandle($socialMedia);

        $this->db->execute(
            'UPDATE customers
             SET name = :name, phone = :phone, address = :address, social_media_handle = :handle
             WHERE customer_id = :id',
            [
                'name' => $customer->getName(),
                'phone' => $customer->getPhone(),
                'address' => $customer->getAddress(),
                'handle' => $customer->getSocialMediaHandle(),
                'id' => $customer->getCustomerId(),
            ]
        );
    }

    /**
     * SR-2.6: SELECT ... WHERE name LIKE :search OR phone LIKE :search OR social_media_handle LIKE
     * :search, with ['search' => '%' . $searchText . '%'] (the same LIKE search as INFO2180 Lab 5).
     * An empty search matches every customer, so the page can list them all (like getAllCustomers() did).
     * @return Customer[]
     */
    public function searchCustomers(string $searchText): array
    {
        $search = '%' . trim($searchText) . '%';
        $rows = $this->db->select(
            'SELECT * FROM customers
             WHERE name LIKE :search1 OR phone LIKE :search2 OR social_media_handle LIKE :search3
             ORDER BY name',
            ['search1' => $search, 'search2' => $search, 'search3' => $search]
        );

        $customers = [];
        foreach ($rows as $row) {
            $customers[] = Customer::fromRow($row);   // each row becomes a Customer object
        }
        return $customers;
    }

    /**
     * SELECT the customer by customer_id and return Customer::fromRow().
     */
    public function getCustomerById(string $customerId): Customer
    {
        $rows = $this->db->select('SELECT * FROM customers WHERE customer_id = :id', ['id' => $customerId]);

        if (count($rows) == 0) {
            throw new Exception('Customer not found.');
        }
        return Customer::fromRow($rows[0]);
    }

    /**
     * SR-2.7: the customer's orders, newest first (OrderMgmt::getOrderById() shows how to rebuild a full
     * order). Each order comes with its lines; its deliveries are not loaded here.
     * Ported from OrderService::getAllOrders($customerId) (based-on-old-srs).
     * @return Order[]
     */
    public function getOrderHistory(Customer $customer): array
    {
        $rows = $this->db->select(
            'SELECT * FROM orders WHERE customer_id = :id ORDER BY entered_at DESC',
            ['id' => $customer->getCustomerId()]
        );

        $orders = [];
        foreach ($rows as $row) {
            // The order's lines, with each item's columns so Item::fromRow() can rebuild the item
            $lineRows = $this->db->select(
                'SELECT l.*, i.name, i.selling_price, i.stock_quantity, i.reserved_quantity
                 FROM order_lines l
                 JOIN items i ON i.item_id = l.item_id
                 WHERE l.order_id = :id
                 ORDER BY l.line_id',
                ['id' => $row['order_id']]
            );
            $lines = [];
            foreach ($lineRows as $lineRow) {
                $lines[] = OrderLine::fromRow($lineRow, Item::fromRow($lineRow));
            }
            $orders[] = Order::fromRow($row, $customer, $lines);
        }
        return $orders;
    }

    /**
     * SC-5: refuse while the customer has a Pending delivery; otherwise $customer->anonymize() and UPDATE.
     */
    public function anonymizeCustomer(Customer $customer): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::anonymizeCustomer() is not built yet.');
    }

    /**
     * The rules for customer details. $excludeId is the customer being edited ('' when adding),
     * so a customer doesn't count as a duplicate of themselves. Throws one Exception listing every problem.
     * Ported from CustomerService::checkDetails() (based-on-old-srs). Helper; not on the class diagram.
     */
    private function checkDetails(string $name, string $address, string $phone, ?string $socialMedia, string $excludeId): void
    {
        $errors = [];

        // Every field except the social media handle is required (SR-2.1, SR-2.3: say which one)
        if ($name == '') {
            $errors[] = 'Name is required.';
        }
        if ($address == '') {
            $errors[] = 'Delivery address is required.';
        }
        if ($phone == '') {
            $errors[] = 'Phone number is required.';
        }

        // Format check (SR-2.4): exactly 10 digits, for example 8765550101
        if ($phone != '' && preg_match('/^[0-9]{10}$/', $phone) !== 1) {
            $errors[] = 'Phone number must be exactly 10 digits, for example 876-555-0101.';
        }

        // No duplicates (SR-2.5). Only checked once the format is right.
        if (count($errors) == 0) {
            $samePhone = $this->db->select(
                'SELECT customer_id FROM customers WHERE phone = :phone AND customer_id <> :exclude',
                ['phone' => $phone, 'exclude' => $excludeId]
            );
            if (count($samePhone) > 0) {
                $errors[] = 'Another customer already has this phone number.';
            }

            if ($socialMedia != null) {
                $sameHandle = $this->db->select(
                    'SELECT customer_id FROM customers WHERE social_media_handle = :handle AND customer_id <> :exclude',
                    ['handle' => $socialMedia, 'exclude' => $excludeId]
                );
                if (count($sameHandle) > 0) {
                    $errors[] = 'Another customer already has this social media handle.';
                }
            }
        }

        if (count($errors) > 0) {
            throw new Exception(implode("\n", $errors));
        }
    }

    /**
     * Removes the spaces, dashes and brackets people type in phone numbers, so 876-555-0101 is saved as
     * 8765550101 (the database holds exactly 10 digits). Helper; not on the class diagram.
     */
    private function cleanPhone(string $phone): string
    {
        return str_replace([' ', '-', '(', ')'], '', trim($phone));
    }

    /**
     * An empty social media handle is saved as "none" (NULL). Helper; not on the class diagram.
     */
    private function cleanHandle(?string $handle): ?string
    {
        if ($handle == null || trim($handle) == '') {
            return null;
        }
        return trim($handle);
    }
}
