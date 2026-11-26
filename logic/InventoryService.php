<?php

/**
 * InventoryService: Application Logic layer. Business rules AND SQL for Feature 5 (Store Products).
 */
class InventoryService
{
    private DataAccess $db;

    public function __construct()
    {
        $this->db = new DataAccess();
    }

    /**
     * 5.1: every product, ordered by name. Features 2 and 4 also use this for their product dropdowns.
     */
    public function getAllProducts(): array
    {
        $rows = $this->db->select('SELECT * FROM items ORDER BY name ASC', []);

        $products = [];
        foreach ($rows as $row) {
            $products[] = Product::fromRow($row);
        }

        return $products;
    }

    /** One product, or null if not found. Features 1 and 3 use this to check a chosen product exists. */
    public function getProduct(int $id): ?Product
    {
        $rows = $this->db->select('SELECT * FROM items WHERE item_id = :id', ['id'=>$id]);
        
        if (count($rows) == 0){
            return null;
        } else {
            return Product::fromRow($rows[0]);
        }
    }

    public function getProductIdByName(string $name): ?int
    {
         $rows = $this->db->select('SELECT item_id FROM items WHERE name = :name', ['name'=>$name]);
        
        if (count($rows) == 0){
            return null;
        } else {
            return (int) $rows[0]['item_id'];
        }
    }

    /**
     * 5.3: adds a new product. Check: name required and not already used,
     * quantity a whole number >= 0, selling price a number >= 0.
     * The values arrive as text from the form, so check them before converting.
     * SQL: INSERT INTO items (name, stock_quantity, selling_price) VALUES (:name, :quantity, :price)
     * @return string[] error messages (empty = saved)
     */
    public function addProduct(string $name, string $quantity, string $sellingPrice): array
    {
        // TODO (Feature 2)
        $error = $this->checkDetails($name, $quantity, $sellingPrice, 0);

        if (count($error)){
            return $error;
        }
        
        $this->db->execute('INSERT INTO items (name, stock_quantity, selling_price)
        VALUES (:name, :quantity, :sellingPrice)', 
        ['name'=> $name, 'quantity'=>(int) $quantity, 'sellingPrice'=>(float) $sellingPrice]);

        return [];
    }

    /**
     * 2.2: changes an existing product's name, quantity and selling price (same checks as addProduct).
     * SQL: UPDATE items SET name = :name, stock_quantity = :quantity, selling_price = :price WHERE item_id = :id
     * @return string[] error messages (empty = saved)
     */
    public function updateProduct(int $id, string $name, string $quantity, string $sellingPrice): array
    {
        // TODO (Feature 2)
        $error = $this->checkDetails($name, $quantity, $sellingPrice, $id);

        if (count($error)){
            return $error;
        }
        
        $this->db->execute('UPDATE items SET name = :name, stock_quantity = :quantity, 
        selling_price = :sellingPrice WHERE item_id = :id', 
        ['name'=> $name, 'quantity'=>(int) $quantity, 'sellingPrice'=>(float) $sellingPrice, 'id'=> $id]
        );

        return [];
    }
    /**
     *  
     * 
     */
    private function checkDetails(string $name, string $quantity, string $sellingPrice, int $excludeId): array
{
    $errors = [];

    // your checks go here

    if ($name == "") {
        $errors[] = 'Name is required.';
    }
    if ($quantity === '') {
        $errors[] = 'Quantity is required.';
    }
    if ($sellingPrice === '') {
        $errors[] = 'Selling Price is required.';
    }

    // Number checks
    if ($quantity < 0 || !ctype_digit($quantity) ){
        $errors[] = "Quantity entered should be a whole number, 0 or more.";
    }
    if ($sellingPrice < 0 || !is_numeric($sellingPrice) ){
        $errors[] = "Selling price must be a number, 0 or more.";
    } 

    // Check if product previously exists.

    if (count($errors) == 0) {
        $results = $this->db->select('SELECT item_id FROM items WHERE name = :name and item_id <> :exclude', 
        ['name' => $name, 'exclude'=> $excludeId]);

        if (count($results) > 0){
            $errors[] = "Item already exists!";
        }
    }

    return $errors;
}
}
