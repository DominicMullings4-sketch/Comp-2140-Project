<?php

/**
 * OrderService: Application Logic layer. Business rules AND SQL for Feature 2 (Recieve Products).
 *
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
     * 2.1, 2.2, 2.5, 2.6: records an order for a customer.
     */
    public function createOrder(int $customerId, array $lines): array
    {
        $errors = [];

        // PART 1 - 4.1: checks if the customer must exist
        $customer = $this->db->select('SELECT customer_id FROM customers WHERE customer_id = :id', ['id' => $customerId]);
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

        // PART 5 - save the order, its delivery, its lines and the reduced stock in ONE transaction.
        $now = new DateTime();
        $deliveryDate = $this->delivery->calculateDeliveryDate($now);   // 5.1, 5.3, 5.4 (your Step 1)

        $this->db->beginTransaction();      // Very important as multiple tables are to be updated to complete the transaction.
        try {
            // Step 1: the order itself, linked to the customer
            $this->db->execute(
                'INSERT INTO orders (customer_id, created_at) VALUES (:customer, :created)',
                ['customer' => $customerId, 'created' => $now->format('Y-m-d H:i:s')]
            );

            // Step 2: the id MySQL just gave the order
            $orderId = $this->db->lastInsertId();

            // Step 2b: the order's delivery (2.3), in the deliveries table.
            // Status starts as 'Pending' and no worker is assigned yet (the table's defaults).
            $this->db->execute(
                'INSERT INTO deliveries (order_id, delivery_date) VALUES (:order, :delivery)',
                ['order' => $orderId, 'delivery' => $deliveryDate]
            );

            foreach ($cleanLines as $line) {
                // Step 3: one row per product ordered, with the price at the time of the order
                $this->db->execute(
                    'INSERT INTO order_lines (order_id, item_id, quantity_ordered, unit_price)
                     VALUES (:order, :product, :quantity, :price)',
                    [
                        'order' => $orderId,
                        'product' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'price' => $line['unit_price'],
                    ]
                );

                // Step 4: reduce that product's stock
                $this->db->execute(
                    'UPDATE items SET stock_quantity = stock_quantity - :quantity WHERE item_id = :product',
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
        //    and its delivery details (LEFT JOIN: the order still shows even if it has no delivery row)
        $rows = $this->db->select(
            'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                    c.delivery_address AS customer_address, c.email AS customer_email,
                    d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
             FROM orders o
             JOIN customers c ON c.customer_id = o.customer_id
             LEFT JOIN deliveries d ON d.order_id = o.order_id
             WHERE o.order_id = :id',
            ['id' => $id]
        );
        if (count($rows) === 0) {
            return null;   // no order with that id
        }
        $order = Order::fromRow($rows[0]);   // the first (and only) row

        // 2. Its lines, with each item's name
        $lineRows = $this->db->select(
            'SELECT l.*, p.name AS product_name
             FROM order_lines l
             JOIN items p ON p.item_id = l.item_id
             WHERE l.order_id = :id
             ORDER BY l.line_id',
            ['id' => $id]
        );
        foreach ($lineRows as $lineRow) {
            $order->lines[] = OrderLine::fromRow($lineRow);
        }

        return $order;
    }

    /**
     * Saved orders, newest first, with each customer's name and phone and the delivery status, for the order lists.
     * Every order when $customerId is null; only that customer's orders when an id is given.
     */
    public function getAllOrders(?int $customerId = null): array
    {
        $sql = 'SELECT o.*, c.name AS customer_name, c.phone AS customer_phone,
                       d.delivery_id, d.delivery_date, d.status, d.worker_id, d.comment
                FROM orders o
                JOIN customers c ON c.customer_id = o.customer_id
                LEFT JOIN deliveries d ON d.order_id = o.order_id';
        $params = [];

        // Only one customer's orders? Add a WHERE, with the id as a placeholder.
        if ($customerId !== null) {
            $sql .= ' WHERE o.customer_id = :customer';
            $params['customer'] = $customerId;
        }

        $sql .= ' ORDER BY o.order_id DESC';
        $rows = $this->db->select($sql, $params);

        $orders = [];
        foreach ($rows as $row) {
            $orders[] = Order::fromRow($row);
        }
        return $orders;
    }
}
