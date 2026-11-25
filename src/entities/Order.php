<?php

/**
 * Order: Entity class (shared by all layers).
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Order
{
    private string $orderId;
    private string $enteredAt;   // date and time as Y-m-d H:i:s
    private OrderStatus $orderStatus;
    private ?OrderSource $orderSource;
    private Customer $customer;
    private array $lines;   // OrderLine[]
    private array $deliveries;   // Delivery[]

    /**
     * SR-3.5: a new order starts as Pending Confirmation, with no deliveries (so its delivery status is
     * Not Scheduled).
     */
    public function __construct(Customer $customer, ?OrderSource $orderSource = null)
    {
        $this->orderId = new_id('ORD');
        $this->customer = $customer;
        $this->orderSource = $orderSource;
        $this->enteredAt = now();
        $this->orderStatus = OrderStatus::PendingConfirmation;
        $this->lines = [];
        $this->deliveries = [];
    }

    /**
     * SR-3.1, SR-3.3: quantity must be a whole number above 0 and no more than
     * $item->getAvailableQuantity(); otherwise throw new Exception('...') with a message for the user,
     * then add new OrderLine($item, $quantity) to $this->lines.
     */
    public function addLine(Item $item, int $quantity): void
    {
        if ($quantity <= 0) {
        throw new Exception('Quantity must be greater than zero.');
        }

        if ($quantity > $item->getAvailableQuantity()) {
            throw new Exception('Quantity exceeds the available stock.');
        }

        $this->lines[] = new OrderLine($item, $quantity);
    }

    /**
     * @return OrderLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * SR-3.4: the sum of every line's total.
     */
    public function getOrderTotal(): float
    {
        $total = 0.0;
        foreach ($this->lines as $line) {
            $total += $line->getLineTotal();
        }
        return round($total, 2);
    }

    /**
     * SR-4.6: only from Pending Confirmation to Order Confirmed. OrderMgmt checks the order has an invoice
     * before calling this.
     */
    public function confirmOrder(): void
    {
        if ($this->orderStatus !== OrderStatus::PendingConfirmation) {
        throw new Exception('Only pending orders can be confirmed.');
        }

        $this->orderStatus = OrderStatus::OrderConfirmed;
    }

    /**
     * SR-3.7: allowed only while the order status is Pending Confirmation or Order Confirmed AND
     * getDeliveryStatus() is Not Scheduled or Failed. Set orderStatus to Cancelled; throw new
     * Exception('...') if not allowed.
     */
    public function cancelOrder(): void
    {
        $statusAllowsCancellation = in_array(
            $this->orderStatus,
            [OrderStatus::PendingConfirmation, OrderStatus::OrderConfirmed],
            true
        );

        $deliveryAllowsCancellation = in_array(
            $this->getDeliveryStatus(),
            [DeliveryStatus::NotScheduled, DeliveryStatus::Failed],
            true
        );

        if (!$statusAllowsCancellation || !$deliveryAllowsCancellation) {
            throw new Exception('This order cannot be cancelled.');
        }

        $this->orderStatus = OrderStatus::Cancelled;
    }

    /**
     * @return Delivery[]
     */
    public function getDeliveries(): array
    {
        return $this->deliveries;
    }

    /**
     * Not Scheduled when the order has no deliveries yet, otherwise the status of its latest delivery.
     */
    public function getDeliveryStatus(): DeliveryStatus
    {
        if (count($this->deliveries) == 0) {
            return DeliveryStatus::NotScheduled;
        }
        $latest = $this->deliveries[count($this->deliveries) - 1];
        return $latest->getStatus();
    }

    /**
     * SR-6.5: how many of this order's deliveries failed.
     */
    public function getUnsuccessfulDeliveryAttempts(): int
    {
        $failed = 0;
        foreach ($this->deliveries as $delivery) {
            if ($delivery->getStatus() === DeliveryStatus::Failed) {
                $failed++;
            }
        }
        return $failed;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string  date and time as Y-m-d H:i:s
     */
    public function getEnteredAt(): string
    {
        return $this->enteredAt;
    }

    public function getOrderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getOrderSource(): ?OrderSource
    {
        return $this->orderSource;
    }

    /**
     * SR-5.2, SR-5.6, SR-5.7: allowed only while the order is Order Confirmed and getDeliveryStatus() is
     * Not Scheduled or Failed. Then return new Delivery($this, $worker, $deliveryDate); the Delivery adds
     * itself to this order, so earlier failed deliveries stay on record.
     * @param string $deliveryDate  date as Y-m-d
     */
    public function assignNewDelivery(User $worker, string $deliveryDate): Delivery
    {
        if ($this->orderStatus !== OrderStatus::OrderConfirmed) {
        throw new Exception('Only confirmed orders can be assigned for delivery.');
        }

        if (!in_array(
            $this->getDeliveryStatus(),
            [DeliveryStatus::NotScheduled, DeliveryStatus::Failed],
            true
        )) {
            throw new Exception('This order cannot be assigned for delivery.');
        }

        return new Delivery($this, $worker, $deliveryDate);
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     * @param OrderLine[] $lines
     */
    public static function fromRow(array $row, Customer $customer, array $lines)
    {
        $source = null;
        if ($row['order_source'] !== null) {
            $source = OrderSource::from($row['order_source']);
        }
        $order = new Order($customer, $source);
        $order->orderId = $row['order_id'];
        $order->enteredAt = $row['entered_at'];
        $order->orderStatus = OrderStatus::from($row['order_status']);
        $order->lines = $lines;
        // Its deliveries are added one by one: each Delivery::fromRow() adds itself to this order.
        return $order;
    }

    /**
     * Adds a delivery to this order's list of deliveries.
     * Delivery's constructor calls this, so every delivery is linked to its order.
     * Helper; not on the class diagram.
     */
    public function addDelivery(Delivery $delivery)
    {
        $this->deliveries[] = $delivery;
    }
}
