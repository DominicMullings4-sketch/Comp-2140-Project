<?php

/**
 * StockReceiptLine: entity class. One product and quantity on a stock receipt (Feature 1).
 */
class StockReceiptLine
{
    public ?int $id = null;
    public ?int $receiptId = null;
    public int $productId;
    public int $quantityReceived;
    public float $unitCost;
    public ?string $productName = null;   // for display only (filled when read with a JOIN)

    // This line's cost: quantity received x unit cost.
    public function lineTotal(): float
    {
        return $this->quantityReceived * $this->unitCost;
    }

    /** Builds a StockReceiptLine from a row of the stock_receipt_lines table. */
    public static function fromRow(array $row): StockReceiptLine
    {
        $line = new StockReceiptLine();
        $line->id = (int) $row['line_id'];
        $line->receiptId = (int) $row['receipt_id'];
        $line->productId = (int) $row['item_id'];
        $line->quantityReceived = (int) $row['quantity_received'];
        $line->unitCost = (float) $row['unit_cost_price'];
        $line->productName = $row['product_name'] ?? null;
        return $line;
    }
}
