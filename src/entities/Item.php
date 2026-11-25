<?php

/**
 * Item: Entity class (shared by all layers).
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Item
{
    private string $itemId;
    private string $name;
    private float $sellingPrice;
    private int $stockQuantity;
    private int $reservedQuantity;

    public function __construct(string $name, float $sellingPrice)
    {
        $this->itemId = new_id('I');
        $this->name = $name;
        $this->sellingPrice = $sellingPrice;
        $this->stockQuantity = 0;
        $this->reservedQuantity = 0;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSellingPrice(): float
    {
        return $this->sellingPrice;
    }

    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    public function getReservedQuantity(): int
    {
        return $this->reservedQuantity;
    }

    /**
     * SR-3.6: increase reservedQuantity by $quantity. Reject (throw new Exception('...') with a message)
     * if $quantity is more than getAvailableQuantity() (SR-3.3).
     */
    public function reserveQuantity(int $quantity): void
    {
        if ($quantity > $this->getAvailableQuantity()) {
            throw new Exception('Only ' . $this->getAvailableQuantity() . ' of "' . $this->name . '" available.');
        }
        $this->reservedQuantity += $quantity;
    }

    /**
     * SR-3.8: decrease reservedQuantity by $quantity (used when an order is cancelled).
     */
    public function unreserveQuantity(int $quantity): void
    {
        $this->reservedQuantity -= $quantity;
    }

    /**
     * SR-1.5: increase stockQuantity by $quantity when a stock receipt is saved.
     */
    public function increaseStockQuantity(int $quantity): void
    {
        $this->stockQuantity += $quantity;
    }

    /**
     * SR-6.4: decrease stockQuantity by $quantity. Private: only completeDelivery() calls it.
     */
    private function decreaseStockQuantity(int $quantity): void
    {
        $this->stockQuantity -= $quantity;
    }

    /**
     * SR-1.4: the selling price must be 0 or more; otherwise throw new Exception('...') with a message for
     * the user.
     */
    public function setSellingPrice(float $price): void
    {
        if ($price < 0) {
            throw new Exception('Selling price must be a number, 0 or more.');
        }
        $this->sellingPrice = $price;
    }

    /**
     * Available Quantity = Stock Quantity - Reserved Quantity (SRS definition, SR-3.2).
     */
    public function getAvailableQuantity(): int
    {
        return $this->stockQuantity - $this->reservedQuantity;
    }

    /**
     * SR-6.4: a completed delivery takes the items out of stock: call decreaseStockQuantity($quantity) and
     * unreserveQuantity($quantity).
     */
    public function completeDelivery(int $quantity): void
    {
        $this->decreaseStockQuantity($quantity);
        $this->unreserveQuantity($quantity);
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row)
    {
        $item = new Item($row['name'], (float) $row['selling_price']);
        $item->itemId = $row['item_id'];
        $item->stockQuantity = (int) $row['stock_quantity'];
        $item->reservedQuantity = (int) $row['reserved_quantity'];
        return $item;
    }
}
