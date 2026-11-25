<?php
// Feature 1: Add Stock Receipts (1.1 - 1.5).  Owner: ______
// Copy the pattern from customers.php.
//
// Plan:
//   TOP:    $inventory = new InventoryService();   $products = $inventory->getAllProducts();   (dropdown)
//           on POST: $result = (new StockReceiptService())->recordReceipt($supplier, $date, $lines);
//                    no errors -> set_flash('Stock receipt #' . $result['id'] . ' saved.'); redirect(...)
//   BOTTOM: form with supplier name, date received, and one or more lines
//           (product dropdown + quantity). Name the line inputs as arrays so PHP gets a list:
//           <select name="lines[0][product_id]"> ... <input name="lines[0][quantity]" type="number" min="1">

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);

$pageTitle = 'Stock Receipts';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Stock Receipts</h1>
<section class="card todo">
    <p><strong>Not built yet.</strong> This page will let staff:</p>
    <ul>
        <li>1.1 record a stock receipt: supplier name, one or more products (from a dropdown) with quantities, and date received</li>
        <li>1.2 be stopped from saving when a field is missing</li>
        <li>1.3 be stopped from saving a quantity less than 1</li>
        <li>1.4 have the products' inventory quantities increase automatically when saved</li>
        <li>1.5 see the unique identifier generated for the receipt</li>
    </ul>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
