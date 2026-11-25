<?php

/**
 * OrderMgmt: Application Logic layer.
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 * createOrder, addOrderLine, saveOrder, getOrders and getOrderById are ported from OrderService
 * on the based-on-old-srs branch.
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class OrderMgmt
{
    private DataAccess $db;
    private InventoryMgmt $inventory;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess, InventoryMgmt.
     */
    public function __construct(DataAccess $db, InventoryMgmt $inventory)
    {
        $this->db = $db;
        $this->inventory = $inventory;
    }

    /**
     * SR-3.1: return new Order($customer, $orderSource). Nothing is saved until saveOrder().
     */
    public function createOrder(Customer $customer, ?OrderSource $orderSource = null): Order
    {
        return new Order($customer, $orderSource);
    }

    /**
     * SR-3.3: $order->addLine($item, $quantity); the Order checks the quantity.
     */
    public function addOrderLine(Order $order, Item $item, int $quantity): void
    {
        $order->addLine($item, $quantity);   // the Order checks the quantity and throws if it's wrong
    }

    /**
     * SR-3.5, SR-3.6, SF-2: in ONE transaction: INSERT the order, INSERT each line, then
     * $this->inventory->reserveStock($order). Roll back if anything fails.
     */
    public function saveOrder(Order $order): void
    {
        if (count($order->getLines()) == 0) {
            throw new Exception('Add at least one item to the order.');
        }

        // Save the order, its lines and the reserved stock in ONE transaction:
        // either everything is saved, or (if anything fails) nothing is.
        $this->db->beginTransaction();
        try {
            // Step 1: the order itself, linked to the customer
            $source = null;
            if ($order->getOrderSource() != null) {
                $source = $order->getOrderSource()->value;
            }
            $this->db->execute(
                'INSERT INTO orders (order_id, customer_id, order_source, entered_at, order_status)
                 VALUES (:id, :customer, :source, :enteredAt, :status)',
                [
                    'id' => $order->getOrderId(),
                    'customer' => $order->getCustomer()->getCustomerId(),
                    'source' => $source,
                    'enteredAt' => $order->getEnteredAt(),
                    'status' => $order->getOrderStatus()->value,
                ]
            );

            // Step 2: one row per item ordered, with the price at the time of the order (SR-4.5)
            foreach ($order->getLines() as $line) {
                $this->db->execute(
                    'INSERT INTO order_lines (order_id, item_id, quantity_ordered, unit_price)
                     VALUES (:order, :item, :quantity, :price)',
                    [
                        'order' => $order->getOrderId(),
                        'item' => $line->getItem()->getItemId(),
                        'quantity' => $line->getQuantityOrdered(),
                        'price' => $line->getUnitPrice(),
                    ]
                );
            }

            // Step 3: reserve the stock (SR-3.6). On based-on-old-srs the stock went down straight away;
            // here it is reserved, and only leaves stock when the delivery is completed (SR-6.4).
            $this->inventory->reserveStock($order);

            $this->db->commit();       // everything worked: keep it all
        } catch (Exception $error) {
            $this->db->rollback();     // something failed: undo it all
            throw $error;
        }
    }

    /**
     * Every order, newest first, rebuilt with Order::fromRow() (load its customer and lines too).
     * @return Order[]
     */
    public function getOrders(): array
    {
        $rows = $this->db->select('SELECT order_id FROM orders ORDER BY entered_at DESC', []);

        $orders = [];
        foreach ($rows as $row) {
            $orders[] = $this->getOrderById($row['order_id']);
        }
        return $orders;
    }

    /**
     * Rebuild the full order: Customer::fromRow(), each OrderLine::fromRow() with its Item,
     * Order::fromRow(), then each Delivery::fromRow() (oldest first, ORDER BY assigned_at), which links
     * itself to the order.
     */
    public function getOrderById(string $orderId): Order
    {
        // 1. The order itself
        $rows = $this->db->select('SELECT * FROM orders WHERE order_id = :id', ['id' => $orderId]);
        if (count($rows) == 0) {
            throw new Exception('Order not found.');
        }
        $row = $rows[0];

        // 2. Its customer
        $customerRows = $this->db->select('SELECT * FROM customers WHERE customer_id = :id', ['id' => $row['customer_id']]);
        $customer = Customer::fromRow($customerRows[0]);

        // 3. Its lines. The JOIN adds each item's columns, so Item::fromRow() can rebuild the item.
        $lineRows = $this->db->select(
            'SELECT l.*, i.name, i.selling_price, i.stock_quantity, i.reserved_quantity
             FROM order_lines l
             JOIN items i ON i.item_id = l.item_id
             WHERE l.order_id = :id
             ORDER BY l.line_id',
            ['id' => $orderId]
        );
        $lines = [];
        foreach ($lineRows as $lineRow) {
            $lines[] = OrderLine::fromRow($lineRow, Item::fromRow($lineRow));
        }

        $order = Order::fromRow($row, $customer, $lines);

        // 4. Its deliveries, oldest first. Each Delivery::fromRow() adds itself to the order.
        $deliveryRows = $this->db->select(
            'SELECT * FROM deliveries WHERE order_id = :id ORDER BY assigned_at',
            ['id' => $orderId]
        );
        foreach ($deliveryRows as $deliveryRow) {
            $worker = $this->getUser($deliveryRow['worker_id']);
            $recordedBy = null;
            if ($deliveryRow['outcome_recorded_by'] != null) {
                $recordedBy = $this->getUser($deliveryRow['outcome_recorded_by']);
            }
            Delivery::fromRow($deliveryRow, $order, $worker, $recordedBy);
        }

        return $order;
    }

    /**
     * SR-3.7, SR-3.8, SR-4.7, SF-2: in one transaction: $order->cancelOrder(), UPDATE it,
     * $this->inventory->releaseStock($order), and mark its invoice Void if it has one.
     */
    public function cancelOrder(Order $order): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::cancelOrder() is not built yet.');
    }

    /**
     * SR-4.1, SR-4.2: only for a Pending Confirmation order with no invoice yet. INSERT new
     * Invoice($order) and return it.
     */
    public function generateInvoice(Order $order): Invoice
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::generateInvoice() is not built yet.');
    }

    /**
     * SR-4.6: only when the order has an invoice. $order->confirmOrder(), then UPDATE its order_status.
     */
    public function confirmOrder(Order $order): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::confirmOrder() is not built yet.');
    }

    /**
     * SR-4.3, SR-4.4: build the invoice PDF (a PDF library will be added to the project) and return the
     * file's path.
     * @return string  path of the generated file
     */
    public function downloadInvoicePdf(Invoice $invoice): string
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::downloadInvoicePdf() is not built yet.');
    }

    /**
     * Loads one user (a delivery's worker or recorder). Helper; not on the class diagram.
     */
    private function getUser(string $userId): User
    {
        $rows = $this->db->select('SELECT * FROM users WHERE user_id = :id', ['id' => $userId]);
        return User::fromRow($rows[0]);
    }
}
