<?php

/**
 * InventoryUI: User Interface layer. The screens for Feature 2 (Viewing and editing inventory).
 * Owner: Feature 2. Copy the pattern from CustomerUI.php: it's the same list + add/edit form layout.
 *
 * inventory.php          -> showItems(): list of items + "Add item" form
 * inventory.php?edit=3   -> showItems(): list of items + "Edit item" form for item 3
 * form submitted (POST)  -> submitItem()
 */
class InventoryUI
{
    private InventoryService $inventory;

    public function __construct()
    {
        $this->inventory = new InventoryService();
    }

    /**
     * 2.1: shows the list of items and the add/edit form (name, quantity, selling price).
     * Like CustomerUI::showCustomers(): ?edit=ID loads that item into the form with getProduct().
     */
    public function showItems(array $errors = [], ?array $form = null): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        $products = $this->inventory->getAllProducts();
        $editId = $this->editId();   // NEW: null = adding, a number = editing that item

        if ($form === null) {
            $form = ['name' => '', 'quantity' => '0', 'price' => ''];

            // NEW: editing? Load that item's current details into the form.
            if ($editId !== null) {
                $product = $this->inventory->getProduct($editId);
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
        }

        // NEW: the form sends to inventory.php?edit=3 when editing, so submitItem() knows which item.
        $formAction = ($editId === null) ? 'inventory.php' : 'inventory.php?edit=' . $editId;

        render_header('Inventory');
        ?>
        <h1>Inventory</h1>

        <section class="card">
            <!-- NEW: heading and button change when editing -->
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
            <?php if (count($products) == 0): ?>
                <p>No items added.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Item</th>
                        <th class="number">Quantity</th>
                        <th class="number">Selling price</th>
                        <th></th> <!-- NEW: column for the Edit links -->
                    </tr>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= h($p->name) ?></td>
                            <td class="number"><?= h($p->quantity) ?></td>
                            <td class="number"><?= h(money($p->sellingPrice)) ?></td>
                            <!-- NEW: each row links to inventory.php?edit=its id -->
                            <td><a href="inventory.php?edit=<?= h($p->id) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * 2.2, 2.3: the add/edit form was submitted. Like CustomerUI::submitCustomer():
     * addProduct() or updateProduct(); no errors -> set_flash + redirect; errors -> showItems($errors, $form).
     */
        public function submitItem(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);
        $editId = $this->editId();   // NEW: null = adding, a number = editing that item

        // 1. Read what was typed.
        $form = [
            'name' => trim($post['name'] ?? ''),
            'quantity' => trim($post['quantity'] ?? ''),
            'price' => trim($post['price'] ?? ''),
        ];

        // 2. NEW: adding or editing? Call the matching service method.
        if ($editId === null) {
            $errors = $this->inventory->addProduct($form['name'], $form['quantity'], $form['price']);
            $message = 'Item added.';
        } else {
            $errors = $this->inventory->updateProduct($editId, $form['name'], $form['quantity'], $form['price']);
            $message = 'Item updated.';
        }

        // 3. Saved? Show the right message and reload the page.
        if (count($errors) === 0) {
            set_flash($message);
            redirect('inventory.php');
        }

        // 4. Not saved: show the page again with the errors and what they typed.
        $this->showItems($errors, $form);
    }

        /** Which item is being edited? (inventory.php?edit=3) Null means "adding a new one". */
    private function editId(): ?int
    {
        return isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    }

}
