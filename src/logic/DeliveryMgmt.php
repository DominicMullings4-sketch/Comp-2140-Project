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
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('DeliveryMgmt::getDeliveryWorkers() is not built yet.');
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
}
