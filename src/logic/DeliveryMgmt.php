<?php

/**
 * DeliveryMgmt: Application Logic layer.
 * Feature owner: P5 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class DeliveryMgmt
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
     * SR-5.1: orders that are Order Confirmed and whose delivery status is Not Scheduled or Failed.
     * @return Order[]
     */
    public function getOrdersAwaitingAssignment(): array
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::getOrdersAwaitingAssignment() is not built yet.');
    }

    /**
     * SR-5.3: active users with the DeliveryWorker role.
     * @return User[]
     */
    public function getDeliveryWorkers(): array
    {
        $rows = $this->db->select("SELECT * FROM users WHERE role = 'DeliveryWorker'
            AND status = 'Active' ORDER BY name, user_id", []);
        return array_map([User::class, 'fromRow'], $rows);
    }

    /**
     * SR-5.5: true when $deliveryDate is today and the time now is 10:00 AM or later.
     * @param string $deliveryDate  date as Y-m-d
     */
    public function needsSameDayWarning(string $deliveryDate): bool
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::needsSameDayWarning() is not built yet.');
    }

    /**
     * SR-5.2, SR-5.4, SR-5.6: reject a date before today; $order->assignNewDelivery($worker,
     * $deliveryDate); INSERT the delivery; return it.
     * @param string $deliveryDate  date as Y-m-d
     */
    public function createDelivery(Order $order, User $worker, string $deliveryDate): Delivery
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::createDelivery() is not built yet.');
    }

    /**
     * Load the delivery's order with OrderMgmt-style loading, then rebuild it with Delivery::fromRow().
     */
    public function getDeliveryById(string $deliveryId): Delivery
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::getDeliveryById() is not built yet.');
    }

    /**
     * SR-6.1: deliveries for $worker with today's date, each with its order and customer.
     * @return Delivery[]
     */
    public function getTodaysDeliveries(User $worker): array
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::getTodaysDeliveries() is not built yet.');
    }

    /**
     * SR-6.2 to SR-6.4, SR-6.6, SF-2: a delivery worker may only change their own deliveries. In one
     * transaction: $delivery->markDelivered($changedBy), UPDATE the delivery, and UPDATE every item's
     * stock_quantity and reserved_quantity.
     */
    public function completeDelivery(Delivery $delivery, User $changedBy): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::completeDelivery() is not built yet.');
    }

    /**
     * SR-6.5, SR-6.6: same ownership rule; $delivery->markFailed($changedBy), then UPDATE the delivery.
     */
    public function failDelivery(Delivery $delivery, User $changedBy): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::failDelivery() is not built yet.');
    }

    public function dispatchList(string $date): array
    {
        $rows = $this->db->select("SELECT o.order_id, o.bearer_id, c.name, c.phone, c.address, u.name AS bearer,
                i.name AS product, l.quantity_ordered, l.quantity_ordered * l.unit_price AS line_total
            FROM orders o JOIN customers c ON c.customer_id = o.customer_id
            LEFT JOIN users u ON u.user_id = o.bearer_id
            LEFT JOIN order_lines l ON l.order_id = o.order_id
            LEFT JOIN items i ON i.item_id = l.item_id
            WHERE o.dispatch_date = :date AND o.order_status = 'OrderConfirmed'
            ORDER BY o.order_id, l.line_id", ['date' => $date]);
        $orders = [];
        foreach ($rows as $row) {
            $id = $row['order_id'];
            if (!isset($orders[$id])) {
                $orders[$id] = ['order_id' => $id, 'name' => $row['name'], 'phone' => $row['phone'],
                    'address' => $row['address'], 'bearer' => $row['bearer'], 'bearer_id' => $row['bearer_id'], 'lines' => [], 'total' => 0];
            }
            if ($row['product'] !== null) {
                $orders[$id]['lines'][] = $row['quantity_ordered'] . ' × ' . $row['product'];
                $orders[$id]['total'] += $row['line_total'];
            }
        }
        return $orders;
    }

    public function handovers(string $date): array
    {
        return $this->db->select("SELECT o.order_id, u.name, o.handed_over_at
            FROM orders o JOIN users u ON u.user_id = o.bearer_id
            WHERE o.dispatch_date = :date AND o.handed_over_at IS NOT NULL
            ORDER BY o.handed_over_at, o.order_id", ['date' => $date]);
    }

    public function assignBearer(string $order, string $worker, string $date): void
    {
        // Lock the scheduled order first so a concurrent handover sees one bearer.
        $this->db->beginTransaction();
        try {
            $rows = $this->db->select("SELECT order_id FROM orders WHERE order_id = :id
                AND dispatch_date = :date AND order_status = 'OrderConfirmed' FOR UPDATE",
                ['id' => $order, 'date' => $date]);
            $workers = $this->db->select("SELECT user_id FROM users WHERE user_id = :id
                AND role = 'DeliveryWorker' AND status = 'Active' FOR UPDATE", ['id' => $worker]);
            if (!$rows || !$workers) {
                throw new DomainException('Select a scheduled order and an active bearer.');
            }
            $this->db->execute('UPDATE orders SET bearer_id = :worker WHERE order_id = :id',
                ['worker' => $worker, 'id' => $order]);
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }

    public function recordHandover(string $order, string $date, string $expectedBearer): void
    {
        $this->db->beginTransaction();
        try {
            $changed = $this->db->execute("UPDATE orders o JOIN users u ON u.user_id = o.bearer_id
                SET o.order_status = 'OutForDelivery', o.handed_over_at = UTC_TIMESTAMP() - INTERVAL 5 HOUR
                WHERE o.order_id = :id AND o.dispatch_date = :date AND o.order_status = 'OrderConfirmed'
                AND o.bearer_id = :bearer AND u.role = 'DeliveryWorker' AND u.status = 'Active'",
                ['id' => $order, 'date' => $date, 'bearer' => $expectedBearer]);
            if ($changed !== 1) {
                throw new DomainException('Handover was not recorded. Check the order and its assigned bearer.');
            }
            $this->db->execute("INSERT INTO deliveries (delivery_id, order_id, worker_id, delivery_date, status)
                VALUES (:delivery, :id, :worker, :date, 'Pending')",
                ['delivery' => new_id('D'), 'id' => $order, 'worker' => $expectedBearer, 'date' => $date]);
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }
}
