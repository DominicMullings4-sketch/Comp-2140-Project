<?php

/**
 * DeliveryService: Application Logic layer for Feature 4 (Confirm Delivery).
 *
 *   SR-4.1  dispatch date for each confirmed order, using the 10:00 a.m. cutoff
 *   SR-4.2  delivery list for a chosen dispatch date, with incomplete orders flagged
 *   SR-4.3  assign a bearer, then record the handover to that bearer
 *
 * All SQL goes through DataAccess (Data Management layer). The pages never run SQL themselves.
 */
class DeliveryService
{
    // The daily dispatch cutoff. An order confirmed at or before this time goes out the same day.
    public const DISPATCH_CUTOFF = '10:00:00';

    private DataAccess $db;
    private UserService $users;

    // True once this page load has checked the deliveries table has the handover columns.
    private static bool $handoverColumnsChecked = false;

    public function __construct()
    {
        $this->db = new DataAccess();
        $this->users = new UserService();
        $this->addHandoverColumnsIfMissing();
    }

    /**
     * AC-4.3 stores the handover in two columns: handed_over_at and handed_over_by.
     * A database imported before Feature 4 was finished doesn't have them, so they are added
     * here the first time it's needed. Nobody has to re-import the database.
     * Once the columns exist, this only runs two quick SHOW COLUMNS checks per page load.
     */
    private function addHandoverColumnsIfMissing(): void
    {
        if (self::$handoverColumnsChecked) {
            return;
        }

        if (count($this->db->select("SHOW COLUMNS FROM deliveries LIKE 'handed_over_at'", [])) === 0) {
            $this->db->execute('ALTER TABLE deliveries ADD COLUMN handed_over_at DATETIME NULL', []);
        }
        if (count($this->db->select("SHOW COLUMNS FROM deliveries LIKE 'handed_over_by'", [])) === 0) {
            $this->db->execute('ALTER TABLE deliveries ADD COLUMN handed_over_by INT NULL', []);
        }

        self::$handoverColumnsChecked = true;
    }

    /**
     * SR-4.1 / AC-4.1: the dispatch date for an order confirmed at $confirmedAt.
     * At or before 10:00:00 -> the confirmation date. After 10:00:00 -> the next calendar day.
     * OrderService calls this when an order is saved, and the result is stored in deliveries.delivery_date.
     */
    public function calculateDeliveryDate(DateTime $confirmedAt): string
    {
        if ($confirmedAt->format('H:i:s') <= self::DISPATCH_CUTOFF) {
            return $confirmedAt->format('Y-m-d');
        }

        $nextDay = clone $confirmedAt;   // clone so the caller's DateTime is not changed
        $nextDay->modify('+1 day');
        return $nextDay->format('Y-m-d');
    }

    /**
     * SR-4.2 / AC-4.2: every confirmed order scheduled for $date, and only those.
     * Each Order comes with its customer's name, phone and address, its bearer,
     * its handover time and its lines (products, quantities, prices) so the total can be shown.
     * @return Order[]
     */
    public function getDeliveryList(string $date): array
    {
        $rows = $this->db->select(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                    c.delivery_address AS customer_address,
                    d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment,
                    d.handed_over_at, u.name AS bearer_name, u.username AS bearer_username
             FROM deliveries d
             JOIN orders o    ON o.order_id = d.order_id
             JOIN customers c ON c.customer_id = o.customer_id
             LEFT JOIN users u ON u.user_id = d.worker_id
             WHERE d.delivery_date = :date
             ORDER BY o.order_id',
            ['date' => $date]
        );

        // Index the orders by id so each line can be attached to its order below.
        $orders = [];
        foreach ($rows as $row) {
            $order = Order::fromRow($row);
            $orders[$order->id] = $order;
        }
        if (count($orders) === 0) {
            return [];
        }

        // One query for every line on this list, instead of one query per order.
        $lineRows = $this->db->select(
            'SELECT l.*, p.name AS product_name
             FROM order_lines l
             JOIN items p ON p.item_id = l.item_id
             JOIN deliveries d ON d.order_id = l.order_id
             WHERE d.delivery_date = :date
             ORDER BY l.order_id, l.line_id',
            ['date' => $date]
        );
        foreach ($lineRows as $lineRow) {
            $line = OrderLine::fromRow($lineRow);
            $orders[$line->orderId]->lines[] = $line;
        }

        return array_values($orders);
    }

    // Address text that means "no address was given" (compared in lower case, without dots).
    private const PLACEHOLDER_ADDRESSES = [
        'na', 'n/a', 'none', 'nil', 'null', 'unknown', 'tbd', 'tba', 'pending',
        'no address', 'not given', 'not provided', 'same', 'test', 'xxx', 'x',
    ];

    /**
     * AC-4.2: what is wrong with this order's delivery information.
     * An empty list means it is complete. Otherwise the order is flagged as incomplete.
     * @return string[] for example ['no contact number', 'delivery address looks incomplete']
     */
    public function getMissingDetails(Order $order): array
    {
        $problems = [];
        $phoneProblem = $this->phoneProblem($order->customerPhone);
        if ($phoneProblem !== null) {
            $problems[] = $phoneProblem;
        }
        $addressProblem = $this->addressProblem($order->customerAddress);
        if ($addressProblem !== null) {
            $problems[] = $addressProblem;
        }
        return $problems;
    }

    /**
     * Null if the contact number can be used to reach the customer.
     * The customer form only checks the characters, so a number like '0000000' or '-------' can
     * still be saved. A usable number needs 7 to 15 digits that are not all the same digit.
     */
    public function phoneProblem(?string $phone): ?string
    {
        if (trim((string) $phone) === '') {
            return 'no contact number';
        }
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (strlen($digits) < 7 || strlen($digits) > 15 || preg_match('/^(\d)\1*$/', $digits) === 1) {
            return 'contact number is not valid';
        }
        return null;
    }

    /**
     * Null if the delivery address can be used. Blank, a placeholder such as 'N/A' or 'none',
     * or text without any letters (e.g. '-', '12') is not an address a bearer can deliver to.
     */
    public function addressProblem(?string $address): ?string
    {
        $text = trim((string) $address);
        if ($text === '') {
            return 'no delivery address';
        }
        $normalised = strtolower(trim(str_replace('.', '', $text)));
        if (in_array($normalised, self::PLACEHOLDER_ADDRESSES, true)
            || preg_match('/[a-z]/i', $text) !== 1
            || strlen($text) < 5) {
            return 'delivery address looks incomplete';
        }
        return null;
    }

    /** Every bearer, for the "assign a bearer" dropdown (SR-4.3). @return User[] */
    public function getBearers(): array
    {
        return $this->users->getBearers();
    }

    /**
     * SR-4.3: assigns (or changes) the bearer for an order that has not been handed over yet.
     * Assigning does NOT change the status. The order stays 'Pending' until the handover is recorded.
     * @return string[] error messages (empty = saved)
     */
    public function assignBearer(int $orderId, int $bearerId): array
    {
        $bearer = $this->users->getUser($bearerId);
        if ($bearer === null || $bearer->role !== User::ROLE_BEARER) {
            return ['Please choose a valid delivery bearer.'];
        }

        $delivery = $this->findDelivery($orderId);
        if ($delivery === null) {
            return ['That order was not found.'];
        }
        if ($delivery['status'] !== Order::STATUS_PENDING) {
            return ['Order #' . $orderId . ' has already been handed over, so its bearer cannot be changed.'];
        }

        $this->db->execute(
            'UPDATE deliveries SET worker_id = :bearer, assigned_at = NOW()
             WHERE order_id = :order AND status = :pending',
            ['bearer' => $bearerId, 'order' => $orderId, 'pending' => Order::STATUS_PENDING]
        );
        return [];
    }

    /**
     * SR-4.3 / AC-4.3: the business owner records that the order was handed to its assigned bearer.
     * Only then does the order become 'Out for delivery'. The handover record is the bearer
     * (worker_id), the date and time (handed_over_at) and who recorded it (handed_over_by).
     * With no bearer assigned, nothing is changed and no handover is recorded.
     * @return string[] error messages (empty = saved)
     */
    public function recordHandover(int $orderId, int $recordedBy): array
    {
        $recorder = $this->users->getUser($recordedBy);
        if ($recorder === null || $recorder->role !== User::ROLE_STAFF) {
            return ['Only office staff can record a delivery handover.'];
        }

        $delivery = $this->findDelivery($orderId);
        if ($delivery === null) {
            return ['That order was not found.'];
        }
        if ($delivery['worker_id'] === null) {
            return ['Assign a bearer to order #' . $orderId . ' before recording the handover.'];
        }
        if ($delivery['status'] !== Order::STATUS_PENDING) {
            return ['Order #' . $orderId . ' has already been handed over.'];
        }

        // The WHERE repeats the checks, so the order can't be handed over twice
        // even if two people press the button at the same moment.
        $changed = $this->db->execute(
            'UPDATE deliveries
             SET status = :out, handed_over_at = NOW(), handed_over_by = :recordedBy
             WHERE order_id = :order AND worker_id IS NOT NULL AND status = :pending',
            [
                'out' => Order::STATUS_OUT_FOR_DELIVERY,
                'recordedBy' => $recordedBy,
                'order' => $orderId,
                'pending' => Order::STATUS_PENDING,
            ]
        );
        if ($changed === 0) {
            return ['The handover for order #' . $orderId . ' could not be recorded. Please try again.'];
        }
        return [];
    }

    /** The deliveries row for one order, or null if there is none. */
    private function findDelivery(int $orderId): ?array
    {
        $rows = $this->db->select(
            'SELECT worker_id, status FROM deliveries WHERE order_id = :order',
            ['order' => $orderId]
        );
        return $rows[0] ?? null;
    }
}
