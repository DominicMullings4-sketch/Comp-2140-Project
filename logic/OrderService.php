<?php

/**
 * OrderService: Application Logic layer. Business rules AND SQL for Feature 4 (Record Orders).
 * Owner: Feature 4. Copy the style of CustomerService.php.
 *
 * Tip for reading an order with the customer's details: use a JOIN and name the extra columns so
 * Order::fromRow() picks them up:
 *   SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
 *          c.delivery_address AS customer_address, c.email AS customer_email
 *   FROM orders o JOIN customers c ON c.id = o.customer_id
 *   WHERE o.id = :id
 * And for the lines with product names:
 *   SELECT l.*, p.name AS product_name FROM order_lines l JOIN products p ON p.id = l.product_id
 *   WHERE l.order_id = :id
 */
class OrderService
{
    private DataAccess $db;
    private InventoryService $inventory;
    private DeliveryService $delivery;

    public function __construct()
    {
        $this->db = new DataAccess();
        $this->inventory = new InventoryService();
        $this->delivery = new DeliveryService();
    }

    /**
     * 4.1, 4.2, 4.5, 4.6 (and 5.1): records an order for a customer.
     *
     * $lines comes from the form as a list like:
     *   [ ['product_id' => '3', 'quantity' => '2'], ['product_id' => '1', 'quantity' => '1'] ]
     *
     * Rules to check before saving:
     *   4.1  the customer exists and there is at least one line
     *   4.2  every quantity is a whole number of at least 1 and not more than the product's stock
     *
     * If all pass:
     *   - 5.1: $deliveryDate = $this->delivery->calculateDeliveryDate(new DateTime());
     *   - save in ONE transaction (see DataAccess.php):
     *       1. INSERT INTO orders (customer_id, delivery_date) VALUES (...)       (status starts as 'Pending')
     *       2. $orderId = $this->db->lastInsertId();
     *       3. for each line: INSERT INTO order_lines (order_id, product_id, quantity, unit_price) ...
     *          (unit_price = the product's selling price right now)
     *       4. for each line: UPDATE products SET quantity = quantity - :qty WHERE id = :product_id   (4.5)
     *
     * @return array ['errors' => string[], 'id' => int|null]  (id is the new order id when saved)
     */
    public function createOrder(int $customerId, array $lines): array
    {
        $errors = [];

        // PART 1 - 4.1: the customer must exist
        $customer = $this->db->select('SELECT id FROM customers WHERE id = :id', ['id' => $customerId]);
        if (count($customer) === 0) {
            $errors[] = 'Choose a customer.';
        }

        // PART 2 - check each product row. Rows left completely empty are skipped.
        $cleanLines = [];
        $wanted = [];   // how many of each product this order asks for so far (product id => quantity)
        foreach ($lines as $i => $line) {
            $productId = trim($line['product_id'] ?? '');
            $quantity = trim($line['quantity'] ?? '');
            $rowNumber = $i + 1;   // people count rows from 1, PHP counts from 0

            if ($productId === '' && $quantity === '') {
                continue;   // nothing typed in this row: skip it
            }

            // a row needs both a product and a quantity
            if ($productId === '' || $quantity === '') {
                $errors[] = "Row $rowNumber: choose a product and enter a quantity.";
                continue;
            }

            // 4.2: quantity must be a whole number, at least 1
            if (!ctype_digit($quantity) || (int) $quantity < 1) {
                $errors[] = "Row $rowNumber: quantity must be a whole number, 1 or more.";
                continue;
            }

            // The product must really exist (your InventoryService::getProduct)
            $product = $this->inventory->getProduct((int) $productId);
            if ($product === null) {
                $errors[] = "Row $rowNumber: that product no longer exists.";
                continue;
            }

            // 4.2: not more than the stock available. Adds up the same product across rows,
            // so 2 rows of 3 can't get past a stock of 5.
            $wanted[$product->id] = ($wanted[$product->id] ?? 0) + (int) $quantity;
            if ($wanted[$product->id] > $product->quantity) {
                $errors[] = "Row $rowNumber: only {$product->quantity} of \"{$product->name}\" in stock.";
                continue;
            }

            // This row passed every check. Copy the selling price NOW, so the invoice
            // never changes if the price is edited later.
            $cleanLines[] = [
                'product_id' => $product->id,
                'quantity' => (int) $quantity,
                'unit_price' => $product->sellingPrice,
            ];
        }

        // PART 3 - 4.1: the order needs at least one product
        if (count($cleanLines) === 0 && count($errors) === 0) {
            $errors[] = 'Add at least one product to the order.';
        }

        // PART 4 - any problems? Stop here and save nothing.
        if (count($errors) > 0) {
            return ['errors' => $errors, 'id' => null];
        }

        // PART 5 - save the order, its lines and the reduced stock in ONE transaction.
        $now = new DateTime();
        $deliveryDate = $this->delivery->calculateDeliveryDate($now);   // 5.1, 5.3, 5.4 (your Step 1)

        $this->db->beginTransaction();
        try {
            // Step 1: the order itself, linked to the customer (4.6). Status starts as 'Pending' (5.2).
            $this->db->execute(
                'INSERT INTO orders (customer_id, created_at, delivery_date) VALUES (:customer, :created, :delivery)',
                ['customer' => $customerId, 'created' => $now->format('Y-m-d H:i:s'), 'delivery' => $deliveryDate]
            );

            // Step 2: the id MySQL just gave the order
            $orderId = $this->db->lastInsertId();

            foreach ($cleanLines as $line) {
                // Step 3: one row per product ordered, with the price at the time of the order
                $this->db->execute(
                    'INSERT INTO order_lines (order_id, product_id, quantity, unit_price)
                     VALUES (:order, :product, :quantity, :price)',
                    [
                        'order' => $orderId,
                        'product' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'price' => $line['unit_price'],
                    ]
                );

                // Step 4: reduce that product's stock (4.5)
                $this->db->execute(
                    'UPDATE products SET quantity = quantity - :quantity WHERE id = :product',
                    ['quantity' => $line['quantity'], 'product' => $line['product_id']]
                );
            }

            $this->db->commit();       // everything worked: keep it all
        } catch (Exception $error) {
            $this->db->rollback();     // something failed: undo it all
            throw $error;
        }

        return ['errors' => [], 'id' => $orderId];
    }

    /** 4.4: one order with its lines (OrderLine objects) and customer details, for the invoice page. */
    public function getOrder(int $id): ?Order
    {
        // 1. The order, with its customer's details (the JOIN adds them, renamed so Order::fromRow() picks them up)
        $row = $this->db->selectOne(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                    c.delivery_address AS customer_address, c.email AS customer_email
             FROM orders o
             JOIN customers c ON c.id = o.customer_id
             WHERE o.id = :id',
            ['id' => $id]
        );
        if ($row === null) {
            return null;
        }
        $order = Order::fromRow($row);

        // 2. Its lines, with each product's name
        $lineRows = $this->db->select(
            'SELECT l.*, p.name AS product_name
             FROM order_lines l
             JOIN products p ON p.id = l.product_id
             WHERE l.order_id = :id
             ORDER BY l.id',
            ['id' => $id]
        );
        foreach ($lineRows as $lineRow) {
            $order->lines[] = OrderLine::fromRow($lineRow);
        }

        return $order;    }

    /**
     * Saved orders, newest first, with each customer's name and phone, for the order lists.
     * Every order when $customerId is null; only that customer's orders when an id is given.
     * (The lines are loaded by getOrder() when one is opened.)
     * @return Order[]
     */
    public function getAllOrders(?int $customerId = null): array
    {
        $sql = 'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone
                FROM orders o
                JOIN customers c ON c.id = o.customer_id';
        $params = [];

        // Only one customer's orders? Add a WHERE, with the id as a placeholder.
        if ($customerId !== null) {
            $sql .= ' WHERE o.customer_id = :customer';
            $params['customer'] = $customerId;
        }

        $sql .= ' ORDER BY o.id DESC';
        $rows = $this->db->select($sql, $params);

        $orders = [];
        foreach ($rows as $row) {
            $orders[] = Order::fromRow($row);
        }
        return $orders;
    }
}
