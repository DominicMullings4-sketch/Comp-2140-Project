<?php

/**
 * Order: entity class. A customer's order (Feature 4), including its delivery details
 * (Features 5 and 6). The order is stored in the orders table; its delivery details are
 * stored in the deliveries table (one delivery per order) and are read with a JOIN.
 * It is made up of OrderLine objects (composition on the class diagram).
 */
class Order
{
    // The four delivery statuses (5.2). Use these instead of typing the words.
    public const STATUS_PENDING = 'Pending';
    public const STATUS_OUT_FOR_DELIVERY = 'Out for delivery';
    public const STATUS_DELIVERED = 'Delivered';
    public const STATUS_FAILED = 'Failed';
    public const ALL_STATUSES = ['Pending', 'Out for delivery', 'Delivered', 'Failed'];

    public ?int $id = null;               // null until saved
    public int $customerId;
    public ?string $createdAt = null;     // 'YYYY-MM-DD HH:MM:SS'
    // From the deliveries table (null if the order has no delivery row)
    public ?int $deliveryId = null;       // deliveries.delivery_id
    public ?string $deliveryDate = null;  // 'YYYY-MM-DD'
    public string $status = self::STATUS_PENDING;
    public ?int $bearerId = null;         // deliveries.worker_id
    public ?string $deliveryComment = null;   // deliveries.comment
    public ?string $handedOverAt = null;      // deliveries.handed_over_at (Feature 4, AC-4.3)

    /** OrderLine[] */
    public array $lines = [];

    // For display 
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public ?string $customerAddress = null;
    public ?string $customerEmail = null;
    public ?string $bearerUsername = null;
    public ?string $bearerName = null;

    // Grand total (4.3): the sum of every line's total.
    public function total(): float
    {
        $total = 0.0;
        foreach ($this->lines as $line) {
            $total += $line->lineTotal();
        }
        return $total;
    }

    /**
     * Builds an Order (without its lines) from a row of the orders table, plus any joined columns.
     * The delivery columns (delivery_id, delivery_date, status, worker_id, comment) come from
     * a JOIN on deliveries; ?? null means they are simply left empty if the query didn't include them.
     */
    public static function fromRow(array $row): Order
    {
        $order = new Order();
        $order->id = (int) $row['order_id'];
        $order->customerId = (int) $row['customer_id'];
        $order->createdAt = $row['created_at'];

        $order->deliveryId = isset($row['delivery_id']) ? (int) $row['delivery_id'] : null;
        $order->deliveryDate = $row['delivery_date'] ?? null;
        $order->status = $row['status'] ?? self::STATUS_PENDING;
        $order->bearerId = isset($row['worker_id']) ? (int) $row['worker_id'] : null;
        $order->deliveryComment = $row['comment'] ?? null;
        $order->handedOverAt = $row['handed_over_at'] ?? null;

        $order->customerName = $row['customer_name'] ?? null;
        $order->customerPhone = $row['customer_phone'] ?? null;
        $order->customerAddress = $row['customer_address'] ?? null;
        $order->customerEmail = $row['customer_email'] ?? null;
        $order->bearerUsername = $row['bearer_username'] ?? null;
        $order->bearerName = $row['bearer_name'] ?? null;
        return $order;
    }
}
