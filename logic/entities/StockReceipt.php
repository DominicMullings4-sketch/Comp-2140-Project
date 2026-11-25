<?php

/**
 * StockReceipt: entity class. One delivery of stock from a supplier (Feature 1).
 * It is made up of StockReceiptLine objects (composition on the class diagram).
 */
class StockReceipt
{
    public ?int $id = null;        // the unique identifier (1.5); null until saved
    public string $supplierName;
    public string $dateReceived;   // 'YYYY-MM-DD'
    public ?string $createdAt = null;   // stock_receipts.entered_at
    public ?int $enteredBy = null;      // the user who recorded it (stock_receipts.entered_by)

    /** @var StockReceiptLine[] */
    public array $lines = [];

    /** The receipt's total cost: the sum of every line's cost. */
    public function total(): float
    {
        $total = 0.0;
        foreach ($this->lines as $line) {
            $total += $line->lineTotal();
        }
        return $total;
    }

    /** Builds a StockReceipt (without its lines) from a row of the stock_receipts table. */
    public static function fromRow(array $row): StockReceipt
    {
        $receipt = new StockReceipt();
        $receipt->id = (int) $row['receipt_id'];
        $receipt->supplierName = $row['supplier_name'];
        $receipt->dateReceived = $row['date_received'];
        $receipt->createdAt = $row['entered_at'];
        $receipt->enteredBy = (int) $row['entered_by'];
        return $receipt;
    }
}
