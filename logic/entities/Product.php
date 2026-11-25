<?php

/**
 * Product: entity class. One inventory item (Feature 2).
 */
class Product
{
    public ?int $id = null;        // null until the product is saved
    public string $name;
    public int $quantity;
    public float $sellingPrice;

    /** Builds a Product from a row of the products table. */
    public static function fromRow(array $row): Product
    {
        $product = new Product();
        $product->id = (int) $row['id'];
        $product->name = $row['name'];
        $product->quantity = (int) $row['quantity'];
        $product->sellingPrice = (float) $row['selling_price'];
        return $product;
    }
}
