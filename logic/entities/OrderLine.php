<?php

/**
 * OrderLine: entity class. One product and quantity inside an order (Feature 4).
 */
class OrderLine
{
    public ?int $id = null;
    public ?int $orderId = null;
    public int $productId;
    public int $quantity;
    public float $unitPrice;              // copied from the product when the order is saved
    public ?string $productName = null;   // for display only (filled when read with a JOIN)

    /** This line's total: quantity x unit price. */
    public function lineTotal(): float
    {
        return $this->quantity * $this->unitPrice;
    }

    /** Builds an OrderLine from a row of the order_lines table. */
    public static function fromRow(array $row): OrderLine
    {
        $line = new OrderLine();
        $line->id = (int) $row['id'];
        $line->orderId = (int) $row['order_id'];
        $line->productId = (int) $row['product_id'];
        $line->quantity = (int) $row['quantity'];
        $line->unitPrice = (float) $row['unit_price'];
        $line->productName = $row['product_name'] ?? null;
        return $line;
    }
}
