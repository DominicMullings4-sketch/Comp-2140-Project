<?php

/**
 * StockReceiptLine: Entity class (shared by all layers).
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class StockReceiptLine
{
    private int $quantityReceived;
    private float $unitCostPrice;
    private Item $item;

    public function __construct(Item $item, int $quantity, float $unitCost)
    {
        $this->item = $item;
        $this->quantityReceived = $quantity;
        $this->unitCostPrice = $unitCost;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getQuantityReceived(): int
    {
        return $this->quantityReceived;
    }

    public function getUnitCostPrice(): float
    {
        return $this->unitCostPrice;
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row, Item $item)
    {
        return new StockReceiptLine($item, (int) $row['quantity_received'], (float) $row['unit_cost_price']);
    }
}
