<?php

/**
 * StockReceiptService: Application Logic layer. Business rules AND SQL for Feature 3 (Add Stock Receipts).
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
     * 3.1 - 3.5: records a stock receipt with one or more lines.
     */
    public function recordReceipt(string $supplierName, string $dateReceived, array $lines, int $enteredBy): array
    {
        $errors = [];
        $supplierName = trim($supplierName);
        $dateReceived = trim($dateReceived);

        // PART 1 - 1.2: supplier and date are required
        if ($supplierName === '') {
            $errors[] = 'Supplier name is required.';
        }
        if ($dateReceived === '') {
            $errors[] = 'Date received is required.';
        } elseif (($parsedDate = DateTime::createFromFormat('!Y-m-d', $dateReceived)) === false
            || $parsedDate->format('Y-m-d') !== $dateReceived) {
            $errors[] = 'Date received is not a valid date.';
        }

        // PART 2 - check each product row. Rows left completely empty are skipped.
        $cleanLines = [];
        foreach ($lines as $i => $line) {
            $productId = trim($line['product_id'] ?? '');
            $quantity = trim($line['quantity'] ?? '');
            $unitCost = trim($line['unit_cost'] ?? '');  
            $rowNumber = $i + 1;   

            if ($productId === '' && $quantity === '' && $unitCost === '') { 
                continue;   // if nothing is typed in row: skip it
            }

            // 1.2: a row needs a product, a quantity and a unit cost
            if ($productId === '' || $quantity === '' || $unitCost === '') {  
                $errors[] = "Row $rowNumber: choose a product and enter both the quantity and the unit cost.";
                continue;
            }

            // 1.3: quantity must be a whole number, at least 1
            if (!ctype_digit($quantity) || (int) $quantity < 1) {
                $errors[] = "Row $rowNumber: quantity must be a whole number, 1 or more.";
                continue;
            }

            // unit cost must be a number, 0 or more (decimals allowed)
            if (!is_numeric($unitCost) || (float) $unitCost < 0) {
                $errors[] = "Row $rowNumber: unit cost must be a number, 0 or more.";
                continue;
            }

            // The product must really exist (your InventoryService::getProduct)
            if ($this->inventory->getProduct((int) $productId) === null) {
                $errors[] = "Row $rowNumber: that product no longer exists.";
                continue;
            }

            // This row passed every check: keep it, as real numbers
            $cleanLines[] = [
                'product_id' => (int) $productId,
                'quantity' => (int) $quantity,
                'unit_cost' => (float) $unitCost,  
            ];
        }
        
        // PART 3 - the receipt needs at least one row
        if (count($cleanLines) === 0 && count($errors) === 0) {
            $errors[] = 'Add at least one product received.';
        }

        // PART 4 - any problems? Stop here and save nothing.
        if (count($errors) > 0) {
            return ['errors' => $errors, 'id' => null];
        }

        // COmplete all saves with an ACID transaction. Either all succeed, are all are rolled back.
        $this->db->beginTransaction();
        try {
            // Step 1: the receipt itself (1.1)
            $this->db->execute(
                'INSERT INTO stock_receipts (supplier_name, date_received, entered_by) VALUES (:supplier, :date, :user)',
                ['supplier' => $supplierName, 'date' => $dateReceived, 'user' => $enteredBy]
            );

            // Step 2: the id MySQL just gave it. This is the unique identifier (3.5).
            $receiptId = $this->db->lastInsertId();

            foreach ($cleanLines as $line) {
                // Step 3: one row per product received
                $this->db->execute(
                    'INSERT INTO stock_receipt_lines (receipt_id, item_id, quantity_received, unit_cost_price)
                     VALUES (:receipt, :product, :quantity, :cost)',
                    [
                        'receipt' => $receiptId,
                        'product' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'cost' => $line['unit_cost'],
                    ]
                );

                // Step 4: increase that product's stock (3.4)
                $this->db->execute(
                    'UPDATE items SET stock_quantity = stock_quantity + :quantity WHERE item_id = :product',
                    ['quantity' => $line['quantity'], 'product' => $line['product_id']]
                );
            }

            $this->db->commit();       // everything worked: keep it all
        } catch (Exception $error) {
            $this->db->rollback();     // something failed: undo it all
            throw $error;
        }

        return ['errors' => [], 'id' => $receiptId];
    }

    /**
     * Every stock receipt that has been saved, newest first, for the "Submitted receipts" list.
     * Displays: id, supplier, date. The lines are loaded by getReceipt().
     */
    public function getAllReceipts(): array
    {
        $rows = $this->db->select('SELECT * FROM stock_receipts ORDER BY receipt_id DESC', []);

        $receipts = [];
        foreach ($rows as $row) {
            $receipts[] = StockReceipt::fromRow($row);
        }
        return $receipts;
    }

    /**
     * One stock receipt WITH its lines (and each line's product name), for the breakdown page.
     * Returns null if there is no receipt with that id.
     */
    public function getReceipt(int $id): ?StockReceipt
    {
        // 1. The receipt itself
        $rows = $this->db->select('SELECT * FROM stock_receipts WHERE receipt_id = :id', ['id' => $id]);
        if (count($rows) === 0) {
            return null;   // no receipt with that id
        }
        $receipt = StockReceipt::fromRow($rows[0]);   // the first (and only) row

        // 2. Its lines. The JOIN adds each item's name, renamed to product_name
        //    so StockReceiptLine::fromRow() picks it up.
        $lineRows = $this->db->select(
            'SELECT l.*, p.name AS product_name
             FROM stock_receipt_lines l
             JOIN items p ON p.item_id = l.item_id
             WHERE l.receipt_id = :id
             ORDER BY l.line_id',
            ['id' => $id]
        );
        foreach ($lineRows as $lineRow) {
            $receipt->lines[] = StockReceiptLine::fromRow($lineRow);
        }

        return $receipt;
    }
}
