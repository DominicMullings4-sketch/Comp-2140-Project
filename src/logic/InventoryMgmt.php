<?php

/**
 * InventoryMgmt: Application Logic layer.
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 * Ported from InventoryService and StockReceiptService on the based-on-old-srs branch.
 *
 * When a rule fails, the method throws ONE Exception holding every problem, one per line
 * (the same lists of errors the old services returned). The UI turns it back into a list with error_list().
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class InventoryMgmt
{
    private DataAccess $db;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess.
     */
    public function __construct(DataAccess $db)
    {
        $this->db = $db;
    }

    /**
     * SR-1.1: return new StockReceipt($supplier, $dateReceived, $enteredBy). Nothing is saved until
     * saveStockReceipt().
     * @param string $dateReceived  date as Y-m-d
     */
    public function createStockReceipt(Supplier $supplier, string $dateReceived, User $enteredBy): StockReceipt
    {
        return new StockReceipt($supplier, $dateReceived, $enteredBy);
    }

    /**
     * SR-1.5, SR-1.6, SR-1.7, SF-2: in ONE transaction (beginTransaction / commit, rollback on error):
     * INSERT the receipt, INSERT each line, and for each line call $item->increaseStockQuantity() then
     * UPDATE that item's stock_quantity.
     */
    public function saveStockReceipt(StockReceipt $receipt): void
    {
        if (count($receipt->getLines()) == 0) {
            throw new Exception('Add at least one item received.');
        }

        // Either everything is saved, or (if anything fails) nothing is.
        $this->db->beginTransaction();
        try {
            // Step 1: the receipt itself (SR-1.1). Its ID (SR-1.6) was made by the StockReceipt constructor.
            $this->db->execute(
                'INSERT INTO stock_receipts (receipt_id, supplier_id, date_received, entered_at, entered_by)
                 VALUES (:id, :supplier, :date, :enteredAt, :enteredBy)',
                [
                    'id' => $receipt->getReceiptId(),
                    'supplier' => $receipt->getSupplier()->getSupplierId(),
                    'date' => $receipt->getDateReceived(),
                    'enteredAt' => $receipt->getEnteredAt(),
                    'enteredBy' => $receipt->getEnteredBy()->getId(),
                ]
            );

            foreach ($receipt->getLines() as $line) {
                $item = $line->getItem();
                $quantity = $line->getQuantityReceived();

                // Step 2: one row per item received
                $this->db->execute(
                    'INSERT INTO stock_receipt_lines (receipt_id, item_id, quantity_received, unit_cost_price)
                     VALUES (:receipt, :item, :quantity, :cost)',
                    [
                        'receipt' => $receipt->getReceiptId(),
                        'item' => $item->getItemId(),
                        'quantity' => $quantity,
                        'cost' => $line->getUnitCostPrice(),
                    ]
                );

                // Step 3: increase that item's stock (SR-1.5). The object is updated, and the SQL adds to
                // the number already in the database, so two rows for the same item both count.
                $item->increaseStockQuantity($quantity);
                $this->db->execute(
                    'UPDATE items SET stock_quantity = stock_quantity + :quantity WHERE item_id = :item',
                    ['quantity' => $quantity, 'item' => $item->getItemId()]
                );
            }

            $this->db->commit();       // everything worked: keep it all
        } catch (Exception $error) {
            $this->db->rollback();     // something failed: undo it all
            throw $error;
        }
    }

    /**
     * SR-1.4: reject an empty name, a price below 0 or a name that already exists; otherwise INSERT new
     * Item($name, $sellingPrice).
     */
    public function addItem(string $name, float $sellingPrice): void
    {
        $name = trim($name);
        $this->checkItemDetails($name, $sellingPrice, '');

        $item = new Item($name, $sellingPrice);   // a new item starts with 0 in stock (stock comes from receipts)
        $this->db->execute(
            'INSERT INTO items (item_id, name, selling_price, stock_quantity, reserved_quantity)
             VALUES (:id, :name, :price, :stock, :reserved)',
            [
                'id' => $item->getItemId(),
                'name' => $item->getName(),
                'price' => $item->getSellingPrice(),
                'stock' => $item->getStockQuantity(),
                'reserved' => $item->getReservedQuantity(),
            ]
        );
    }

    /**
     * Changes an existing item's name and selling price (same checks as addItem).
     * Ported from InventoryService::updateItem() (based-on-old-srs). The stock quantity is no longer edited
     * here: on this structure it only changes through stock receipts (SR-1.5) and completed deliveries (SR-6.4).
     * NOT ON THE CLASS DIAGRAM YET: add it to InventoryMgmt in the SDS.
     */
    public function editItem(string $itemId, string $name, float $sellingPrice): void
    {
        $name = trim($name);
        $item = $this->getItemById($itemId);   // throws 'Item not found.' if it doesn't exist
        $this->checkItemDetails($name, $sellingPrice, $itemId);

        $item->setSellingPrice($sellingPrice);
        $this->db->execute(
            'UPDATE items SET name = :name, selling_price = :price WHERE item_id = :id',
            ['name' => $name, 'price' => $item->getSellingPrice(), 'id' => $item->getItemId()]
        );
    }

    /**
     * Worked example: every layer working together. Copy this pattern for the other features.
     * @return Item[]
     */
    public function getItems(): array
    {
        $rows = $this->db->select('SELECT * FROM items ORDER BY name', []);

        // Turn each database row into an Item object.
        $items = [];
        foreach ($rows as $row) {
            $items[] = Item::fromRow($row);
        }
        return $items;
    }

    /**
     * SELECT the item WHERE item_id = :id and return Item::fromRow(). If no row comes back, throw new
     * Exception('Item not found.').
     */
    public function getItemById(string $itemId): Item
    {
        $rows = $this->db->select('SELECT * FROM items WHERE item_id = :id', ['id' => $itemId]);

        if (count($rows) == 0) {
            throw new Exception('Item not found.');
        }
        return Item::fromRow($rows[0]);
    }

    /**
     * SR-1.9: name, city and country are required; contact name and phone are optional. INSERT new
     * Supplier(...) and return it.
     */
    public function addSupplier(string $name, string $city, string $country, ?string $contactName = null, ?string $contactPhone = null): Supplier
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::addSupplier() is not built yet.');
    }

    /**
     * SR-1.8: every supplier, sorted by name, for the stock receipt's supplier list.
     * @return Supplier[]
     */
    public function getSuppliers(): array
    {
        $rows = $this->db->select('SELECT * FROM suppliers ORDER BY name', []);

        $suppliers = [];
        foreach ($rows as $row) {
            $suppliers[] = Supplier::fromRow($row);
        }
        return $suppliers;
    }

    /**
     * SELECT the supplier by supplier_id and return Supplier::fromRow().
     */
    public function getSupplierById(string $supplierId): Supplier
    {
        $rows = $this->db->select('SELECT * FROM suppliers WHERE supplier_id = :id', ['id' => $supplierId]);

        if (count($rows) == 0) {
            throw new Exception('Supplier not found.');
        }
        return Supplier::fromRow($rows[0]);
    }

    /**
     * Every stock receipt that has been saved, newest first, for the "Submitted receipts" list.
     * Ported from StockReceiptService::getAllReceipts() (based-on-old-srs).
     * NOT ON THE CLASS DIAGRAM YET: add it to InventoryMgmt in the SDS.
     * @return StockReceipt[]
     */
    public function getStockReceipts(): array
    {
        $rows = $this->db->select('SELECT receipt_id FROM stock_receipts ORDER BY entered_at DESC', []);

        $receipts = [];
        foreach ($rows as $row) {
            $receipts[] = $this->getStockReceiptById($row['receipt_id']);
        }
        return $receipts;
    }

    /**
     * One stock receipt WITH its supplier, the user who entered it, and its lines, for the breakdown page.
     * Ported from StockReceiptService::getReceipt() (based-on-old-srs).
     * NOT ON THE CLASS DIAGRAM YET: add it to InventoryMgmt in the SDS.
     */
    public function getStockReceiptById(string $receiptId): StockReceipt
    {
        // 1. The receipt itself
        $rows = $this->db->select('SELECT * FROM stock_receipts WHERE receipt_id = :id', ['id' => $receiptId]);
        if (count($rows) == 0) {
            throw new Exception('Stock receipt not found.');
        }
        $row = $rows[0];

        // 2. Its supplier and the user who entered it
        $supplier = $this->getSupplierById($row['supplier_id']);
        $userRows = $this->db->select('SELECT * FROM users WHERE user_id = :id', ['id' => $row['entered_by']]);
        $enteredBy = User::fromRow($userRows[0]);

        // 3. Its lines. The JOIN adds each item's columns, so Item::fromRow() can rebuild the item.
        $lineRows = $this->db->select(
            'SELECT l.*, i.name, i.selling_price, i.stock_quantity, i.reserved_quantity
             FROM stock_receipt_lines l
             JOIN items i ON i.item_id = l.item_id
             WHERE l.receipt_id = :id
             ORDER BY l.line_id',
            ['id' => $receiptId]
        );
        $lines = [];
        foreach ($lineRows as $lineRow) {
            $lines[] = StockReceiptLine::fromRow($lineRow, Item::fromRow($lineRow));
        }

        return StockReceipt::fromRow($row, $supplier, $enteredBy, $lines);
    }

    /**
     * SR-3.6: for each line of the order, $item->reserveQuantity(qty) and UPDATE the item's
     * reserved_quantity. Called by OrderMgmt inside its transaction.
     */
    public function reserveStock(Order $order): void
    {
        foreach ($order->getLines() as $line) {
            $item = $line->getItem();
            $quantity = $line->getQuantityOrdered();

            $item->reserveQuantity($quantity);   // throws if more than the available quantity
            $this->db->execute(
                'UPDATE items SET reserved_quantity = reserved_quantity + :quantity WHERE item_id = :item',
                ['quantity' => $quantity, 'item' => $item->getItemId()]
            );
        }
    }

    /**
     * SR-3.8: for each line of the order, $item->unreserveQuantity(qty) and UPDATE the item's
     * reserved_quantity.
     */
    public function releaseStock(Order $order): void
    {
        foreach ($order->getLines() as $line) {
            $item = $line->getItem();
            $quantity = $line->getQuantityOrdered();

            $item->unreserveQuantity($quantity);
            $this->db->execute(
                'UPDATE items SET reserved_quantity = reserved_quantity - :quantity WHERE item_id = :item',
                ['quantity' => $quantity, 'item' => $item->getItemId()]
            );
        }
    }

    /**
     * The rules for an item's details (SR-1.4). $excludeId is the item being edited ('' when adding),
     * so an item doesn't count as a duplicate of itself. Throws one Exception listing every problem.
     * Ported from InventoryService::checkDetails() (based-on-old-srs). Helper; not on the class diagram.
     */
    private function checkItemDetails(string $name, float $sellingPrice, string $excludeId): void
    {
        $errors = [];

        if ($name == '') {
            $errors[] = 'Name is required.';
        }
        if ($sellingPrice < 0) {
            $errors[] = 'Selling price must be a number, 0 or more.';
        }

        // Check if the item already exists (only once the other checks pass).
        if (count($errors) == 0) {
            $results = $this->db->select(
                'SELECT item_id FROM items WHERE name = :name AND item_id <> :exclude',
                ['name' => $name, 'exclude' => $excludeId]
            );
            if (count($results) > 0) {
                $errors[] = 'Item already exists!';
            }
        }

        if (count($errors) > 0) {
            throw new Exception(implode("\n", $errors));
        }
    }
}
