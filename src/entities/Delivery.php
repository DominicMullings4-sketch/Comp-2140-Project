<?php

/**
 * Delivery: Entity class (shared by all layers).
 * Feature owner: P5 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Delivery
{
    private string $deliveryId;
    private string $deliveryDate;   // date as Y-m-d
    private DeliveryStatus $status;
    private ?string $outcomeRecordedAt;   // date and time as Y-m-d H:i:s
    private ?User $outcomeRecordedBy;
    private Order $order;
    private User $worker;

    /**
     * SR-5.6: a new delivery is Pending, and it adds itself to its order's list of deliveries.
     * @param string $deliveryDate  date as Y-m-d
     */
    public function __construct(Order $order, User $worker, string $deliveryDate)
    {
        $this->deliveryId = new_id('D');
        $this->order = $order;
        $this->worker = $worker;
        $this->deliveryDate = $deliveryDate;
        $this->status = DeliveryStatus::Pending;
        $this->outcomeRecordedAt = null;
        $this->outcomeRecordedBy = null;
        $order->addDelivery($this);
    }

    public function getDeliveryId(): string
    {
        return $this->deliveryId;
    }

    /**
     * SR-6.2, SR-6.3, SR-6.4, SR-6.6: only from Pending. Set status to Completed, call
     * completeDelivery(quantity) on the item of every line of $this->order, and record $changedBy and the
     * current date/time (now()).
     */
    public function markDelivered(User $changedBy): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('Delivery::markDelivered() is not built yet.');
    }

    /**
     * SR-6.5, SR-6.6: only from Pending. Set status to Failed and record $changedBy and the current
     * date/time (now()).
     */
    public function markFailed(User $changedBy): void
    {
        // TODO (P5): build this method. The note above says what it must do.
        throw new Exception('Delivery::markFailed() is not built yet.');
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getWorker(): User
    {
        return $this->worker;
    }

    /**
     * @return string  date as Y-m-d
     */
    public function getDeliveryDate(): string
    {
        return $this->deliveryDate;
    }

    public function getStatus(): DeliveryStatus
    {
        return $this->status;
    }

    /**
     * @return string  date and time as Y-m-d H:i:s
     */
    public function getRecordedAt(): ?string
    {
        return $this->outcomeRecordedAt;
    }

    public function getRecordedBy(): ?User
    {
        return $this->outcomeRecordedBy;
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row, Order $order, User $worker, ?User $recordedBy)
    {
        $delivery = new Delivery($order, $worker, $row['delivery_date']);   // the constructor adds it to the order
        $delivery->deliveryId = $row['delivery_id'];
        $delivery->status = DeliveryStatus::from($row['status']);
        $delivery->outcomeRecordedAt = $row['outcome_recorded_at'];
        $delivery->outcomeRecordedBy = $recordedBy;
        return $delivery;
    }
}
