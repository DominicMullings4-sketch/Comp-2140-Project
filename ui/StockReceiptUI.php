<?php

/**
 * StockReceiptUI: User Interface layer. The screens for Feature 3 (Add Stock Receipts).
 *
 * stock_receipts.php                  -> showReceiptForm(): the form + the list of submitted receipts
 * form submitted (POST)               -> submitReceipt()
 * stock_receipts.php?action=view&id=3 -> showReceipt(3): the breakdown of receipt 3 with its lines
 */
class StockReceiptUI
{
    private StockReceiptService $receipts;
    private InventoryService $inventory;

    public function __construct()
    {
        $this->receipts = new StockReceiptService();
        $this->inventory = new InventoryService();
    }

    /**
     * 3.1: the stock receipt form: supplier name, date received, and several rows of
     * product.
     */
    public function showReceiptForm(array $errors = [], ?array $form = null): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);
        
        $products = $this->inventory->getAllProducts();

        // Every receipt saved so far, for the "Submitted receipts" list below the form
        $receipts = $this->receipts->getAllReceipts();

        if ($form === null) {
            $form = ['supplier_name' => '', 'date_received' => date('Y-m-d'), 'lines' => []];
        }
        $rowCount = 5;

        render_header('Stock Receipts');
        ?>
        <h1>Stock Receipts</h1>

        <section class="card">
            <h2>Record stock received</h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($products) === 0): ?>
                <p>There are no items yet. Add them on the <a href="inventory.php">Inventory</a> page first.</p>
            <?php else: ?>
                <form method="post" action="stock_receipts.php">
                    <label for="supplier_name">Supplier name</label>
                    <input id="supplier_name" name="supplier_name" value="<?= h($form['supplier_name'] ?? '') ?>" required>

                    <label for="date_received">Date received</label>
                    <input id="date_received" name="date_received" type="date" value="<?= h($form['date_received'] ?? '') ?>" required>

                    <h3>Products received</h3>
                    <p>Fill in one row per product. Leave unused rows empty.</p>
                    <table>
                        <tr>
                            <th>Product</th>
                            <th class="number">Quantity</th>
                            <th class="number">Unit cost</th>
                        </tr>
                        <?php for ($i = 0; $i < $rowCount; $i++): ?>
                            <?php $chosen = $form['lines'][$i]['product_id'] ?? ''; ?>
                            <tr>
                                <td>
                                    <select name="lines[<?= $i ?>][product_id]" aria-label="Product, row <?= $i + 1 ?>">
                                        <option value="">Choose a product</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= h($p->id) ?>" <?= (string) $p->id === (string) $chosen ? 'selected' : '' ?>>
                                                <?= h($p->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="number">
                                    <input name="lines[<?= $i ?>][quantity]" type="number" min="1" step="1"
                                           value="<?= h($form['lines'][$i]['quantity'] ?? '') ?>" aria-label="Quantity, row <?= $i + 1 ?>">
                                </td>
                                <td class="number">
                                    <input name="lines[<?= $i ?>][unit_cost]" type="number" min="0" step="0.01"
                                           value="<?= h($form['lines'][$i]['unit_cost'] ?? '') ?>" aria-label="Unit cost, row <?= $i + 1 ?>">
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </table>

                    <button type="submit">Save stock receipt</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Submitted receipts</h2>
            <?php if (count($receipts) === 0): ?>
                <p>No stock receipts yet.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date received</th>
                        <th>Supplier</th>
                        <th>Entered By</th>
                    </tr>
                    <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td><a href="stock_receipts.php?action=view&amp;id=<?= h($r->id) ?>">#ORD-<?= h($r->id) ?></a></td>
                            <td><?= h($r->dateReceived) ?></td>
                            <td><?= h($r->supplierName) ?></td>
                            <td><?= h('EMP-'.$r->enteredBy) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * The breakdown of one stock receipt: its details and every line
     * (product, quantity received, unit cost, line cost), plus the total cost.
     */
    public function showReceipt(int $receiptId): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        $receipt = $this->receipts->getReceipt($receiptId);
        if ($receipt === null) {
            set_flash('That stock receipt was not found.', 'error');
            redirect('stock_receipts.php');
        }

        render_header('Stock Receipt #' . $receipt->id);
        ?>
        <p><a href="stock_receipts.php">&larr; Back to stock receipts</a></p>
        <h1>Stock Receipt #<?= h($receipt->id) ?></h1>

        <section class="card">
            <table>
                <tr><th>Supplier</th><td><?= h($receipt->supplierName) ?></td></tr>
                <tr><th>Date received</th><td><?= h($receipt->dateReceived) ?></td></tr>
                <tr><th>Recorded at</th><td><?= h($receipt->createdAt) ?></td></tr>
                <tr><th>Entered by</th><td><?= h("EMP-".$receipt->enteredBy) ?></td></tr>
            </table>
        </section>

        <section class="card">
            <h2>Products received</h2>
            <table>
                <tr>
                    <th>Product</th>
                    <th class="number">Quantity</th>
                    <th class="number">Unit cost</th>
                    <th class="number">Line cost</th>
                </tr>
                <?php foreach ($receipt->lines as $line): ?>
                    <tr>
                        <td><?= h($line->productName) ?></td>
                        <td class="number"><?= h($line->quantityReceived) ?></td>
                        <td class="number"><?= h(money($line->unitCost)) ?></td>
                        <td class="number"><?= h(money($line->lineTotal())) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">Total cost</th>
                    <th class="number"><?= h(money($receipt->total())) ?></th>
                </tr>
            </table>
        </section>
        <?php
        render_footer();
    }

    /**
     * 1.1 - 1.5: the form was submitted.
     */
    public function submitReceipt(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // 1. Hand what was typed to the logic layer, which checks it and saves it.
        //    The signed-in user's id is saved as the person who entered the receipt (stock_receipts.entered_by).
        $lines = is_array($post['lines'] ?? null) ? $post['lines'] : [];
        $result = $this->receipts->recordReceipt(
            $post['supplier_name'] ?? '',
            $post['date_received'] ?? '',
            $lines,
            AccessControl::currentUserId()
        );

        if (count($result['errors']) === 0) {
            set_flash('Stock receipt #' . $result['id'] . ' saved. Stock levels have been updated.');
            redirect('stock_receipts.php');
        }

        // 3. Not saved: show the form again with the errors and what they typed.
        $this->showReceiptForm($result['errors'], $post);
    }
}
