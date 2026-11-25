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
    public ?string $createdAt = null;

    /** @var StockReceiptLine[] */
    public array $lines = [];

    /** Builds a StockReceipt (without its lines) from a row of the stock_receipts table. */
    public static function fromRow(array $row): StockReceipt
    {
        $receipt = new StockReceipt();
        $receipt->id = (int) $row['id'];
        $receipt->supplierName = $row['supplier_name'];
        $receipt->dateReceived = $row['date_received'];
        $receipt->createdAt = $row['created_at'];
        return $receipt;
    }
}
