<?php

/**
 * Order: entity class. A customer's order (Feature 4), including its delivery details
 * (Features 5 and 6). It is made up of OrderLine objects (composition on the class diagram).
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
    public string $deliveryDate;          // 'YYYY-MM-DD'
    public string $status = self::STATUS_PENDING;
    public ?int $bearerId = null;
    public ?string $deliveryComment = null;

    /** @var OrderLine[] */
    public array $lines = [];

    // For display only (filled when read with a JOIN on customers / users)
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public ?string $customerAddress = null;
    public ?string $customerEmail = null;
    public ?string $bearerUsername = null;

    /** Grand total (4.3): the sum of every line's total. */
    public function total(): float
    {
        $total = 0.0;
        foreach ($this->lines as $line) {
            $total += $line->lineTotal();
        }
        return $total;
    }

    /** Builds an Order (without its lines) from a row of the orders table, plus any joined columns. */
    public static function fromRow(array $row): Order
    {
        $order = new Order();
        $order->id = (int) $row['id'];
        $order->customerId = (int) $row['customer_id'];
        $order->createdAt = $row['created_at'];
        $order->deliveryDate = $row['delivery_date'];
        $order->status = $row['status'];
        $order->bearerId = $row['bearer_id'] === null ? null : (int) $row['bearer_id'];
        $order->deliveryComment = $row['delivery_comment'];
        $order->customerName = $row['customer_name'] ?? null;
        $order->customerPhone = $row['customer_phone'] ?? null;
        $order->customerAddress = $row['customer_address'] ?? null;
        $order->customerEmail = $row['customer_email'] ?? null;
        $order->bearerUsername = $row['bearer_username'] ?? null;
        return $order;
    }
}
