<?php
// Feature 2: Viewing and editing inventory (2.1, 2.2, 2.3).
// Same pattern as customers.php:
//   inventory.php          -> list of items + "Add item" form
//   inventory.php?edit=3   -> list of items + "Edit item" form for item 3

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

$service = new InventoryService();
$errors = [];

// Which item is being edited? No ?edit= in the address means we're adding a new one.
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;

// What the form boxes show: empty when adding (quantity starts at 0), the item's details when editing.
$form = ['name' => '', 'quantity' => '0', 'price' => ''];
if ($editId !== null) {
    $product = $service->getProduct($editId);
    if ($product === null) {
        set_flash('That item was not found.', 'error');
        redirect('inventory.php');
    }
    $form = [
        'name' => $product->name,
        'quantity' => (string) $product->quantity,
        'price' => number_format($product->sellingPrice, 2, '.', ''),
    ];
}

// The form was submitted: hand what was typed to the service, which checks the rules and saves.
if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'quantity' => trim($_POST['quantity'] ?? ''),
        'price' => trim($_POST['price'] ?? ''),
    ];

    if ($editId === null) {
        $errors = $service->addProduct($form['name'], $form['quantity'], $form['price']);
        $message = 'Item added.';
    } else {
        $errors = $service->updateProduct($editId, $form['name'], $form['quantity'], $form['price']);
        $message = 'Item updated.';
    }

    // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't save it twice.
    if (count($errors) === 0) {
        set_flash($message);
        redirect('inventory.php');
    }
}

$products = $service->getAllProducts();
$pageTitle = 'Inventory';
$formAction = ($editId === null) ? 'inventory.php' : 'inventory.php?edit=' . $editId;
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Inventory</h1>

<section class="card">
    <h2><?= $editId === null ? 'Add item' : 'Edit item' ?></h2>

    <?php if (count($errors) > 0): ?>
        <ul class="flash error">
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="<?= h($formAction) ?>">
        <label for="name">Item name</label>
        <input id="name" name="name" value="<?= h($form['name']) ?>" required>

        <label for="quantity">Quantity in stock</label>
        <input id="quantity" name="quantity" type="number" min="0" step="1" value="<?= h($form['quantity']) ?>" required>

        <label for="price">Selling price</label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="<?= h($form['price']) ?>" required>

        <button type="submit"><?= $editId === null ? 'Add item' : 'Save changes' ?></button>
        <?php if ($editId !== null): ?>
            <a class="button secondary" href="inventory.php">Cancel</a>
        <?php endif; ?>
    </form>
</section>

<section class="card">
    <h2>All items</h2>
    <?php if (count($products) === 0): ?>
        <p>No items yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Item</th>
                <th class="number">Quantity</th>
                <th class="number">Selling price</th>
                <th></th>
            </tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= h($p->name) ?></td>
                    <td class="number"><?= h($p->quantity) ?></td>
                    <td class="number"><?= h(money($p->sellingPrice)) ?></td>
                    <td><a href="inventory.php?edit=<?= h($p->id) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
