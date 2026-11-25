<?php

/**
 * InventoryService: Application Logic layer. Business rules AND SQL for Feature 2 (Viewing and editing inventory).
 * Owner: Feature 2. Copy the style of CustomerService.php.
 */
class InventoryService
{
    private DataAccess $db;

    public function __construct()
    {
        $this->db = new DataAccess();
    }

    /**
     * 2.1: every product, ordered by name. Features 1 and 4 also use this for their product dropdowns.
     * SQL: SELECT * FROM products ORDER BY name   -> turn each row into a Product with Product::fromRow()
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        // TODO (Feature 2)
        throw new Exception('InventoryService::getAllProducts() is not built yet (F2: 2.1).');
    }

    /** One product, or null if not found. Features 1 and 4 use this to check a chosen product exists. */
    public function getProduct(int $id): ?Product
    {
        // TODO (Feature 2)
        throw new Exception('InventoryService::getProduct() is not built yet (F2).');
    }

    /**
     * 2.3: adds a new product. Check: name required and not already used,
     * quantity a whole number >= 0, selling price a number >= 0.
     * The values arrive as text from the form, so check them before converting.
     * SQL: INSERT INTO products (name, quantity, selling_price) VALUES (:name, :quantity, :price)
     * @return string[] error messages (empty = saved)
     */
    public function addProduct(string $name, string $quantity, string $sellingPrice): array
    {
        // TODO (Feature 2)
        throw new Exception('InventoryService::addProduct() is not built yet (F2: 2.3).');
    }

    /**
     * 2.2: changes an existing product's name, quantity and selling price (same checks as addProduct).
     * SQL: UPDATE products SET name = :name, quantity = :quantity, selling_price = :price WHERE id = :id
     * @return string[] error messages (empty = saved)
     */
    public function updateProduct(int $id, string $name, string $quantity, string $sellingPrice): array
    {
        // TODO (Feature 2)
        throw new Exception('InventoryService::updateProduct() is not built yet (F2: 2.2).');
    }
}
