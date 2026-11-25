<?php

/**
 * OrderMgmt: Application Logic layer.
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
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
       $order->addLine($item, $quantity);
    }

    /**
     * SR-3.5, SR-3.6, SF-2: in ONE transaction: INSERT the order, INSERT each line, then
     * $this->inventory->reserveStock($order). Roll back if anything fails.
     */
    public function saveOrder(Order $order): void
    {
        $this->db->beginTransaction();
        try {
            // INSERT into orders using the order's getters.
            foreach ($order->getLines() as $line) {
                // INSERT into order_lines; save item ID, quantity, and unit price.
            }
            $this->inventory->reserveStock($order);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    /**
     * Every order, newest first, rebuilt with Order::fromRow() (load its customer and lines too).
     * @return Order[]
     */
    public function getOrders(): array
    {
        $rows = $this->db->select(
            'SELECT order_id FROM orders ORDER BY entered_at DESC',
            []
        );

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
        $orderRows = $this->db->select(
        'SELECT * FROM orders WHERE order_id = :order_id',
        ['order_id' => $orderId]
    );

    if (count($orderRows) === 0) {
        throw new Exception('Order not found.');
    }

    $orderRow = $orderRows[0];

    $customerRows = $this->db->select(
        'SELECT * FROM customers WHERE customer_id = :customer_id',
        ['customer_id' => $orderRow['customer_id']]
    );

    if (count($customerRows) === 0) {
        throw new Exception('Customer not found for this order.');
    }

    $customer = Customer::fromRow($customerRows[0]);

    $lineRows = $this->db->select(
        'SELECT ol.quantity_ordered, ol.unit_price,
                i.item_id, i.name, i.selling_price,
                i.stock_quantity, i.reserved_quantity
         FROM order_lines ol
         JOIN items i ON i.item_id = ol.item_id
         WHERE ol.order_id = :order_id
         ORDER BY ol.line_id',
        ['order_id' => $orderId]
    );

    $lines = [];
    foreach ($lineRows as $lineRow) {
        $item = Item::fromRow($lineRow);
        $lines[] = OrderLine::fromRow($lineRow, $item);
    }

    $order = Order::fromRow($orderRow, $customer, $lines);

    $deliveryRows = $this->db->select(
        'SELECT * FROM deliveries
         WHERE order_id = :order_id
         ORDER BY assigned_at',
        ['order_id' => $orderId]
    );

    foreach ($deliveryRows as $deliveryRow) {
        $workerRows = $this->db->select(
            'SELECT * FROM users WHERE user_id = :user_id',
            ['user_id' => $deliveryRow['worker_id']]
        );

        if (count($workerRows) === 0) {
            throw new Exception('Delivery worker not found.');
        }

        $recordedBy = null;
        if ($deliveryRow['outcome_recorded_by'] !== null) {
            $recordedByRows = $this->db->select(
                'SELECT * FROM users WHERE user_id = :user_id',
                ['user_id' => $deliveryRow['outcome_recorded_by']]
            );

            if (count($recordedByRows) > 0) {
                $recordedBy = User::fromRow($recordedByRows[0]);
            }
        }

        Delivery::fromRow(
            $deliveryRow,
            $order,
            User::fromRow($workerRows[0]),
            $recordedBy
        );
    }

    return $order;
    }

    /**
     * SR-3.7, SR-3.8, SR-4.7, SF-2: in one transaction: $order->cancelOrder(), UPDATE it,
     * $this->inventory->releaseStock($order), and mark its invoice Void if it has one.
     */
    public function cancelOrder(Order $order): void
    {
        $this->db->beginTransaction();
        try {
            $order->cancelOrder();
            // UPDATE orders.order_status.
            $this->inventory->releaseStock($order);
            // If an invoice exists, mark it void and UPDATE invoices.status.
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
    }

    /**
     * SR-4.1, SR-4.2: only for a Pending Confirmation order with no invoice yet. INSERT new
     * Invoice($order) and return it.
     */
    public function generateInvoice(Order $order): Invoice
    {
        $invoice = new Invoice($order);
        return $invoice;
    }

    /**
     * SR-4.6: only when the order has an invoice. $order->confirmOrder(), then UPDATE its order_status.
     */
    public function confirmOrder(Order $order): void
    {
        $order->confirmOrder();
    }

    /**
     * SR-4.3, SR-4.4: build the invoice PDF (a PDF library will be added to the project) and return the
     * file's path.
     * @return string  path of the generated file
     */
    public function downloadInvoicePdf(Invoice $invoice): string
    {
        $pdfPath = /* generated file path */;
        return $pdfPath;
    }
}
