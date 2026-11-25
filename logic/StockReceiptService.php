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
        $errors = [];
        $supplierName = trim($supplierName);
        $dateReceived = trim($dateReceived);

        // PART 1 - 1.2: supplier and date are required
        if ($supplierName === '') {
            $errors[] = 'Supplier name is required.';
        }
        if ($dateReceived === '') {
            $errors[] = 'Date received is required.';
        } elseif (DateTime::createFromFormat('Y-m-d', $dateReceived) === false) {
            $errors[] = 'Date received is not a valid date.';
        }

        // PART 2 - check each product row. Rows left completely empty are skipped.
        $cleanLines = [];
        foreach ($lines as $i => $line) {
            $productId = trim($line['product_id'] ?? '');
            $quantity = trim($line['quantity'] ?? '');
            $unitCost = trim($line['unit_cost'] ?? '');   // NEW
            $rowNumber = $i + 1;   // people count rows from 1, PHP counts from 0

            if ($productId === '' && $quantity === '' && $unitCost === '') {   // NEW: cost too
                continue;   // nothing typed in this row: skip it
            }

            // 1.2: a row needs a product, a quantity and a unit cost
            if ($productId === '' || $quantity === '' || $unitCost === '') {   // NEW: cost too
                $errors[] = "Row $rowNumber: choose a product and enter both the quantity and the unit cost.";
                continue;
            }

            // 1.3: quantity must be a whole number, at least 1
            if (!ctype_digit($quantity) || (int) $quantity < 1) {
                $errors[] = "Row $rowNumber: quantity must be a whole number, 1 or more.";
                continue;
            }

            // NEW: unit cost must be a number, 0 or more (decimals allowed, e.g. 850.50)
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
                'unit_cost' => (float) $unitCost,   // NEW
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

        // Job 2 (next step) replaces this line: save everything in one transaction.
                // JOB 2 - save the receipt, its rows and the new stock levels in ONE transaction:
        // either everything is saved, or (if anything fails) nothing is.
        $this->db->beginTransaction();
        try {
            // Step 1: the receipt itself (1.1)
            $this->db->execute(
                'INSERT INTO stock_receipts (supplier_name, date_received) VALUES (:supplier, :date)',
                ['supplier' => $supplierName, 'date' => $dateReceived]
            );

            // Step 2: the id MySQL just gave it. This is the unique identifier (1.5).
            $receiptId = $this->db->lastInsertId();

            foreach ($cleanLines as $line) {
                // Step 3: one row per product received
                $this->db->execute(
                    'INSERT INTO stock_receipt_lines (receipt_id, product_id, quantity_received, unit_cost)
                     VALUES (:receipt, :product, :quantity, :cost)',
                    [
                        'receipt' => $receiptId,
                        'product' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'cost' => $line['unit_cost'],
                    ]
                );

                // Step 4: increase that product's stock (1.4)
                $this->db->execute(
                    'UPDATE products SET quantity = quantity + :quantity WHERE id = :product',
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
}
