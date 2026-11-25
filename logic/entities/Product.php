<?php

/**
 * Product: entity class. One inventory item (Feature 2). Stored in the items table.
 */
class Product
{
    public ?int $id = null;        // null until the product is saved
    public string $name;
    public int $quantity;          // items.stock_quantity
    public int $reservedQuantity = 0;   // feature rolled back
    public float $sellingPrice;

    /** Builds a Product from a row of the items table. */
    public static function fromRow(array $row): Product
    {
        $product = new Product();
        $product->id = (int) $row['item_id'];
        $product->name = $row['name'];
        $product->quantity = (int) $row['stock_quantity'];
        $product->reservedQuantity = (int) $row['reserved_quantity'];
        $product->sellingPrice = (float) $row['selling_price'];
        return $product;
    }
}
