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
        // TODO (Feature 4)
        throw new Exception('OrderService::createOrder() is not built yet (F4: 4.1, 4.2, 4.5, 4.6).');
    }

    /** 4.4: one order with its lines (OrderLine objects) and customer details, for the invoice page. */
    public function getOrder(int $id): ?Order
    {
        // TODO (Feature 4)
        throw new Exception('OrderService::getOrder() is not built yet (F4: 4.4).');
    }
}
