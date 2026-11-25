<?php

/**
 * StockReceiptService: Application Logic layer. Business rules AND SQL for Feature 1 (Add Stock Receipts).
 * Owner: Feature 1. Copy the style of CustomerService.php.
 */
class StockReceiptService
{
    private DataAccess $db;
    private InventoryService $inventory;

    public function __construct()
    {
        $this->db = new DataAccess();
        $this->inventory = new InventoryService();
    }

    /**
     * 1.1 - 1.5: records a stock receipt with one or more lines.
     *
     * $lines comes from the form as a list like:
     *   [ ['product_id' => '3', 'quantity' => '10'], ['product_id' => '1', 'quantity' => '5'] ]
     *
     * Rules to check before saving:
     *   1.2  supplier name, date received and every line's product and quantity are filled in
     *   1.3  every quantity is a whole number of at least 1
     *        each product is a real product ($this->inventory->getProduct())
     *
     * If all pass, save in ONE transaction (see DataAccess.php):
     *   1. INSERT INTO stock_receipts (supplier_name, date_received) VALUES (...)
     *   2. $receiptId = $this->db->lastInsertId();                                  (1.5 the unique identifier)
     *   3. for each line: INSERT INTO stock_receipt_lines (receipt_id, product_id, quantity_received) ...
     *   4. for each line: UPDATE products SET quantity = quantity + :qty WHERE id = :product_id   (1.4)
     *
     * @return array ['errors' => string[], 'id' => int|null]  (id is the new receipt id when saved)
     */
    public function recordReceipt(string $supplierName, string $dateReceived, array $lines): array
    {
        // TODO (Feature 1)
        throw new Exception('StockReceiptService::recordReceipt() is not built yet (F1: 1.1 - 1.5).');
    }
}
