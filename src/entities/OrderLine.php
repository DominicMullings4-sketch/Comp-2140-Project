<?php

/**
 * OrderLine: Entity class (shared by all layers).
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class OrderLine
{
    private int $quantityOrdered;
    private float $unitPrice;
    private Item $item;

    /**
     * SR-4.5: the unit price is copied from the item now, so later price changes don't alter this order or
     * its invoice.
     */
    public function __construct(Item $item, int $quantity)
    {
        $this->item = $item;
        $this->quantityOrdered = $quantity;
        $this->unitPrice = $item->getSellingPrice();
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getQuantityOrdered(): int
    {
        return $this->quantityOrdered;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getLineTotal(): float
    {
        return round($this->quantityOrdered * $this->unitPrice, 2);
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row, Item $item)
    {
        $line = new OrderLine($item, (int) $row['quantity_ordered']);
        $line->unitPrice = (float) $row['unit_price'];   // the price saved when the order was made (SR-4.5)
        return $line;
    }
}
