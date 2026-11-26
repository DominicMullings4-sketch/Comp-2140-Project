<?php

/**
 * InventoryMgmt: Application Logic layer.
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
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
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::createStockReceipt() is not built yet.');
    }

    /**
     * SR-1.5, SR-1.6, SR-1.7, SF-2: in ONE transaction (beginTransaction / commit, rollback on error):
     * INSERT the receipt, INSERT each line, and for each line call $item->increaseStockQuantity() then
     * UPDATE that item's stock_quantity.
     */
    public function saveStockReceipt(StockReceipt $receipt): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::saveStockReceipt() is not built yet.');
    }

    /**
     * SR-1.4: reject an empty name, a price below 0 or a name that already exists; otherwise INSERT new
     * Item($name, $sellingPrice).
     */
    public function addItem(string $name, float $sellingPrice): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::addItem() is not built yet.');
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
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::getItemById() is not built yet.');
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
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::getSuppliers() is not built yet.');
    }

    /**
     * SELECT the supplier by supplier_id and return Supplier::fromRow().
     */
    public function getSupplierById(string $supplierId): Supplier
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::getSupplierById() is not built yet.');
    }

    /**
     * SR-3.6: for each line of the order, $item->reserveQuantity(qty) and UPDATE the item's
     * reserved_quantity. Called by OrderMgmt inside its transaction.
     */
    public function reserveStock(Order $order): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::reserveStock() is not built yet.');
    }

    /**
     * SR-3.8: for each line of the order, $item->unreserveQuantity(qty) and UPDATE the item's
     * reserved_quantity.
     */
    public function releaseStock(Order $order): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryMgmt::releaseStock() is not built yet.');
    }

        // ---------------- Store Products (SRS Feature 5, SR-5.1 to SR-5.5) ----------------

    /** SR-5.2, AC-5.1, AC-5.3: validate, then INSERT. The timestamp comes from StockItem. */
    public function logStockItem($name, ?string $description, $quantity, $rate, string $loggedBy): StockItem
    {
        [$name, $quantity, $rate] = $this->validateStockItem($name, $quantity, $rate);
        $stockItem = new StockItem($name, $description, $quantity, $rate);
        $this->db->execute(
            'INSERT INTO stock_items (stock_item_id, item_name, description, quantity, rate, logged_at, logged_by)
             VALUES (:id, :name, :description, :quantity, :rate, :loggedAt, :loggedBy)',
            [
                'id' => $stockItem->getStockItemId(),
                'name' => $name,
                'description' => $description,
                'quantity' => $quantity,
                'rate' => $rate,
                'loggedAt' => $stockItem->getLoggedAt(),
                'loggedBy' => $loggedBy,
            ]
        );
        return $this->getStockItemById($stockItem->getStockItemId());
    }

    /** SR-5.2, AC-5.2: edit an item. The timestamp cannot be edited (AC-3.3). */
    public function updateStockItem(string $stockItemId, $name, ?string $description, $quantity, $rate): void
    {
        $existing = $this->getStockItemById($stockItemId);
        if ($existing->isFlagged()) {
            throw new Exception('A flagged item cannot be edited.');
        }
        [$name, $quantity, $rate] = $this->validateStockItem($name, $quantity, $rate);
        $this->db->execute(
            'UPDATE stock_items SET item_name = :name, description = :description, quantity = :quantity, rate = :rate
             WHERE stock_item_id = :id',
            ['name' => $name, 'description' => $description, 'quantity' => $quantity, 'rate' => $rate, 'id' => $stockItemId]
        );
    }

    /** SR-5.1, SR-5.4, AC-5.4: only the owner sees flagged items. @return StockItem[] */
    public function getStockItems(UserRole $viewerRole): array
    {
        $sql = 'SELECT * FROM stock_items';
        if ($viewerRole != UserRole::BusinessOwner) {
            $sql .= ' WHERE is_flagged = 0';
        }
        $rows = $this->db->select($sql . ' ORDER BY logged_at DESC, item_name', []);

        $items = [];
        foreach ($rows as $row) {
            $items[] = StockItem::fromRow($row);
        }
        return $items;
    }

    public function getStockItemById(string $stockItemId): StockItem
    {
        $rows = $this->db->select('SELECT * FROM stock_items WHERE stock_item_id = :id', ['id' => $stockItemId]);
        if (count($rows) == 0) {
            throw new Exception('Stock item not found.');
        }
        return StockItem::fromRow($rows[0]);
    }

    /** SR-5.4, SF-3: flag an item logged in error. The row stays in the database. */
    public function flagStockItem(string $stockItemId, string $flaggedBy): void
    {
        $this->getStockItemById($stockItemId);   // throws if it does not exist
        $this->db->execute(
            'UPDATE stock_items SET is_flagged = 1, flagged_at = :at, flagged_by = :by WHERE stock_item_id = :id',
            ['at' => now(), 'by' => $flaggedBy, 'id' => $stockItemId]
        );
    }

    /**
     * SR-5.5, AC-5.5: flagged items are never counted. With no dates, counts every item.
     * 'incoming' = unflagged items logged in the period and not yet taken for delivery.
     * @param string|null $from Y-m-d   @param string|null $to Y-m-d
     * @return array{total:int, incoming:int}
     */
    public function getStockSummary(?string $from = null, ?string $to = null): array
    {
        $where = 'is_flagged = 0';
        $params = [];
        if ($from !== null && $to !== null) {
            $where .= ' AND DATE(logged_at) BETWEEN :from AND :to';
            $params = ['from' => $from, 'to' => $to];
        }
        $rows = $this->db->select(
            'SELECT COUNT(*) AS total, COALESCE(SUM(taken_for_delivery = 0), 0) AS incoming
             FROM stock_items WHERE ' . $where,
            $params
        );
        return ['total' => (int) $rows[0]['total'], 'incoming' => (int) $rows[0]['incoming']];
    }

    /** AC-5.1, TS-5.1B: reject bad input before any SQL runs. */
    private function validateStockItem($name, $quantity, $rate): array
    {
        $name = trim((string) $name);
        if ($name === '' || mb_strlen($name) > 100) {
            throw new InvalidArgumentException('Item name is required (100 characters or fewer).');
        }
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 1) {
            throw new InvalidArgumentException('Quantity must be a whole number of 1 or more.');
        }
        if (!is_numeric($rate) || $rate < 0 || $rate > 99999999.99) {
            throw new InvalidArgumentException('Rate must be a number of 0 or more.');
        }
        return [$name, $quantity, round((float) $rate, 2)];
    }
}
