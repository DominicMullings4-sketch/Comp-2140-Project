<?php

/**
 * InventoryUI: User Interface layer.
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 * Ported from InventoryUI and StockReceiptUI on the based-on-old-srs branch.
 *
 * inventory.php                          -> showItems(): list of items + "Add item" form
 * inventory.php?action=item       (POST) -> submitNewItem()
 * inventory.php?action=edit&id=I-..      -> showItems() with the "Edit item" form; POST -> submitItemEdit()
 * inventory.php?action=receipt           -> showStockReceiptForm(); POST -> submitStockReceipt()
 * inventory.php?action=viewreceipt&id=.. -> showStockReceipt(): the breakdown of one receipt
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class InventoryUI
{
    private UserPermission $permission;
    private InventoryMgmt $inventory;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, InventoryMgmt.
     */
    public function __construct(UserPermission $permission, InventoryMgmt $inventory)
    {
        $this->permission = $permission;
        $this->inventory = $inventory;
    }

    /**
     * Worked example: every layer working together. Copy this pattern for the other pages.
     * Now also shows the add/edit item form from based-on-old-srs.
     * $errors and $form are only passed in when a save failed, so the form shows the messages AND what
     * the user typed. $editId is the item being edited (null = adding a new one).
     */
    public function showItems(array $errors = [], ?array $form = null, ?string $editId = null): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);   // SC-1: who may see this page
        $items = $this->inventory->getItems();   // ask the logic layer for the data

        // What the form boxes show: empty when adding, the item's details when editing.
        if ($form == null) {
            $form = ['name' => '', 'price' => ''];
            if ($editId != null) {
                try {
                    $item = $this->inventory->getItemById($editId);
                } catch (Exception $exception) {
                    set_flash('That item was not found.', 'error');
                    redirect('inventory.php');
                }
                $form = ['name' => $item->getName(), 'price' => number_format($item->getSellingPrice(), 2, '.', '')];
            }
        }

        // The form sends to action=edit when editing, so the page knows which item.
        if ($editId == null) {
            $formAction = 'inventory.php?action=item';
        } else {
            $formAction = 'inventory.php?action=edit&id=' . urlencode($editId);
        }

        render_header('Inventory', $user);
        ?>
        <h1>Inventory</h1>
        <p><a class="button" href="inventory.php?action=receipt">Record stock received</a></p>

        <section class="card">
            <h2><?= $editId == null ? 'Add item' : 'Edit item' ?></h2>
            <?php $this->showFieldErrors($errors); ?>

            <form method="post" action="<?= e($formAction) ?>">
                <label for="name">Item name</label>
                <input id="name" name="name" value="<?= e($form['name']) ?>" required>

                <label for="price">Selling price</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="<?= e($form['price']) ?>" required>

                <?php if ($editId == null): ?>
                    <p>New items start with 0 in stock. Stock is added by recording a stock receipt.</p>
                <?php endif; ?>

                <button type="submit"><?= $editId == null ? 'Add item' : 'Save changes' ?></button>
                <?php if ($editId != null): ?>
                    <a class="button secondary" href="inventory.php">Cancel</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="card">
            <h2>All items</h2>
            <table>
                <thead>
                    <tr><th>Item</th><th class="num">Selling price</th><th class="num">Stock</th><th class="num">Reserved</th><th class="num">Available</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item->getName()) ?></td>
                        <td class="num"><?= money($item->getSellingPrice()) ?></td>
                        <td class="num"><?= $item->getStockQuantity() ?></td>
                        <td class="num"><?= $item->getReservedQuantity() ?></td>
                        <td class="num"><?= $item->getAvailableQuantity() ?></td>
                        <td><a href="inventory.php?action=edit&amp;id=<?= e(urlencode($item->getItemId())) ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($items) == 0): ?>
                    <tr><td colspan="6">No items yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-1.1, SR-1.8: supplier dropdown (InventoryMgmt::getSuppliers()), date received, and rows of item /
     * quantity / unit cost. Below the form: the list of submitted receipts (from based-on-old-srs).
     * Name the row inputs as arrays so PHP receives a list: lines[0][item_id], lines[0][quantity], ...
     */
    public function showStockReceiptForm(array $errors = [], ?array $form = null): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        $suppliers = $this->inventory->getSuppliers();
        $items = $this->inventory->getItems();
        $receipts = $this->inventory->getStockReceipts();   // every receipt saved so far

        if ($form == null) {
            $form = ['supplier_id' => '', 'date_received' => today(), 'lines' => []];
        }
        $rowCount = 5;   // how many item rows the form shows

        render_header('Stock Receipts', $user);
        ?>
        <p><a href="inventory.php">&larr; Inventory</a></p>
        <h1>Stock Receipts</h1>

        <section class="card">
            <h2>Record stock received</h2>
            <?php $this->showFieldErrors($errors); ?>

            <?php if (count($items) == 0 || count($suppliers) == 0): ?>
                <p>To record stock you need at least one supplier and one item. Add items on the
                   <a href="inventory.php">Inventory</a> page first.</p>
            <?php else: ?>
                <form method="post" action="inventory.php?action=receipt">
                    <label for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">Choose a supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= e($supplier->getSupplierId()) ?>" <?= $supplier->getSupplierId() == ($form['supplier_id'] ?? '') ? 'selected' : '' ?>>
                                <?= e($supplier->getName()) ?> (<?= e($supplier->getCity()) ?>, <?= e($supplier->getCountry()) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="date_received">Date received</label>
                    <input id="date_received" name="date_received" type="date" value="<?= e($form['date_received'] ?? '') ?>" required>

                    <h3>Items received</h3>
                    <p>Fill in one row per item. Leave unused rows empty.</p>
                    <table>
                        <tr>
                            <th>Item</th>
                            <th class="num">Quantity</th>
                            <th class="num">Unit cost</th>
                        </tr>
                        <?php for ($i = 0; $i < $rowCount; $i++): ?>
                            <?php $chosen = $form['lines'][$i]['item_id'] ?? ''; ?>
                            <tr>
                                <td>
                                    <select name="lines[<?= $i ?>][item_id]" aria-label="Item, row <?= $i + 1 ?>">
                                        <option value="">Choose an item</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?= e($item->getItemId()) ?>" <?= $item->getItemId() == $chosen ? 'selected' : '' ?>>
                                                <?= e($item->getName()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="num">
                                    <input name="lines[<?= $i ?>][quantity]" type="number" min="1" step="1"
                                           value="<?= e($form['lines'][$i]['quantity'] ?? '') ?>" aria-label="Quantity, row <?= $i + 1 ?>">
                                </td>
                                <td class="num">
                                    <input name="lines[<?= $i ?>][unit_cost]" type="number" min="0" step="0.01"
                                           value="<?= e($form['lines'][$i]['unit_cost'] ?? '') ?>" aria-label="Unit cost, row <?= $i + 1 ?>">
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
            <?php if (count($receipts) == 0): ?>
                <p>No stock receipts yet.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date received</th>
                        <th>Supplier</th>
                    </tr>
                    <?php foreach ($receipts as $receipt): ?>
                        <tr>
                            <td><a href="inventory.php?action=viewreceipt&amp;id=<?= e(urlencode($receipt->getReceiptId())) ?>"><?= e($receipt->getReceiptId()) ?></a></td>
                            <td><?= e($receipt->getDateReceived()) ?></td>
                            <td><?= e($receipt->getSupplier()->getName()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-1.2: if a required field is empty, showFieldErrors(); otherwise createStockReceipt(), addLine()
     * per row, saveStockReceipt().
     * The row checks are the ones from StockReceiptService::recordReceipt() on based-on-old-srs.
     */
    public function submitStockReceipt(array $form): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        $supplierId = trim($form['supplier_id'] ?? '');
        $dateReceived = trim($form['date_received'] ?? '');
        // is_array() guards against a tampered form sending something that isn't a list.
        $lines = is_array($form['lines'] ?? null) ? $form['lines'] : [];
        $errors = [];

        // PART 1 - SR-1.2: supplier and date are required
        if ($supplierId == '') {
            $errors[] = 'Supplier is required.';
        }
        if ($dateReceived == '') {
            $errors[] = 'Date received is required.';
        } elseif (DateTime::createFromFormat('Y-m-d', $dateReceived) === false) {
            $errors[] = 'Date received is not a valid date.';
        }

        // PART 2 - make the receipt (nothing is saved yet)
        $receipt = null;
        if (count($errors) == 0) {
            try {
                $supplier = $this->inventory->getSupplierById($supplierId);
                $receipt = $this->inventory->createStockReceipt($supplier, $dateReceived, $user);
            } catch (Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        // PART 3 - check each item row and add it to the receipt. Rows left completely empty are skipped.
        $items = [];   // item ID => Item, so two rows for the same item share one Item object
        foreach ($lines as $i => $line) {
            $itemId = trim($line['item_id'] ?? '');
            $quantity = trim($line['quantity'] ?? '');
            $unitCost = trim($line['unit_cost'] ?? '');
            $rowNumber = $i + 1;   // people count rows from 1, PHP counts from 0

            if ($itemId == '' && $quantity == '' && $unitCost == '') {
                continue;   // nothing typed in this row: skip it
            }

            // SR-1.2: a row needs an item, a quantity and a unit cost
            if ($itemId == '' || $quantity == '' || $unitCost == '') {
                $errors[] = "Row $rowNumber: choose an item and enter both the quantity and the unit cost.";
                continue;
            }

            // SR-1.3: quantity must be a whole number (checked here, while it's still text)
            if (!ctype_digit($quantity)) {
                $errors[] = "Row $rowNumber: quantity must be a whole number, 1 or more.";
                continue;
            }

            // SR-1.10: unit cost must be a number (decimals allowed, e.g. 850.50)
            if (!is_numeric($unitCost)) {
                $errors[] = "Row $rowNumber: unit cost must be a number, 0 or more.";
                continue;
            }

            try {
                if (!isset($items[$itemId])) {
                    $items[$itemId] = $this->inventory->getItemById($itemId);   // the item must really exist
                }
                if ($receipt != null) {
                    $receipt->addLine($items[$itemId], (int) $quantity, (float) $unitCost);   // checks 1 or more / 0 or more
                }
            } catch (Exception $exception) {
                $errors[] = "Row $rowNumber: " . lcfirst($exception->getMessage());
            }
        }

        // PART 4 - the receipt needs at least one row
        if (count($errors) == 0 && count($receipt->getLines()) == 0) {
            $errors[] = 'Add at least one item received.';
        }

        // PART 5 - any problems? Show the form again with the errors and what they typed. Nothing is saved.
        if (count($errors) > 0) {
            $this->showStockReceiptForm($errors, $form);
            return;
        }

        // PART 6 - save the receipt, its lines and the new stock levels (one transaction, in InventoryMgmt)
        try {
            $this->inventory->saveStockReceipt($receipt);
        } catch (Exception $exception) {
            $this->showStockReceiptForm(error_list($exception), $form);
            return;
        }

        // Saved: show the receipt's unique ID (SR-1.6) and reload the page (so refresh can't save it twice).
        set_flash('Stock receipt ' . $receipt->getReceiptId() . ' saved. Stock levels have been updated.');
        redirect('inventory.php?action=receipt');
    }

    /**
     * The breakdown of one stock receipt: its details and every line
     * (item, quantity received, unit cost price, line cost), plus the total cost.
     * Ported from StockReceiptUI::showReceipt() (based-on-old-srs).
     * NOT ON THE CLASS DIAGRAM YET: add it to InventoryUI in the SDS.
     */
    public function showStockReceipt(string $receiptId): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $receipt = $this->inventory->getStockReceiptById($receiptId);
        } catch (Exception $exception) {
            set_flash('That stock receipt was not found.', 'error');
            redirect('inventory.php?action=receipt');
        }

        render_header('Stock Receipt ' . $receipt->getReceiptId(), $user);
        $total = 0.0;
        ?>
        <p><a href="inventory.php?action=receipt">&larr; Back to stock receipts</a></p>
        <h1>Stock Receipt <?= e($receipt->getReceiptId()) ?></h1>

        <section class="card">
            <table>
                <tr><th>Supplier</th><td><?= e($receipt->getSupplier()->getName()) ?></td></tr>
                <tr><th>Date received</th><td><?= e($receipt->getDateReceived()) ?></td></tr>
                <tr><th>Recorded at</th><td><?= e($receipt->getEnteredAt()) ?></td></tr>
                <tr><th>Recorded by</th><td><?= e($receipt->getEnteredBy()->getName()) ?></td></tr>
            </table>
        </section>

        <section class="card">
            <h2>Items received</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th class="num">Quantity</th>
                    <th class="num">Unit cost</th>
                    <th class="num">Line cost</th>
                </tr>
                <?php foreach ($receipt->getLines() as $line): ?>
                    <?php
                    $lineCost = $line->getQuantityReceived() * $line->getUnitCostPrice();
                    $total += $lineCost;
                    ?>
                    <tr>
                        <td><?= e($line->getItem()->getName()) ?></td>
                        <td class="num"><?= $line->getQuantityReceived() ?></td>
                        <td class="num"><?= money($line->getUnitCostPrice()) ?></td>
                        <td class="num"><?= money($lineCost) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">Total cost</th>
                    <th class="num"><?= money($total) ?></th>
                </tr>
            </table>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-1.4: InventoryMgmt::addItem() with the form's name and selling price; show any error.
     */
    public function submitNewItem(array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        // 1. Read what was typed.
        $form = [
            'name' => trim($form['name'] ?? ''),
            'price' => trim($form['price'] ?? ''),
        ];

        // 2. Check the boxes first. The price arrives as text, so check it's a number before handing it over as one.
        $errors = $this->checkItemFields($form);
        if (count($errors) == 0) {
            try {
                $this->inventory->addItem($form['name'], (float) $form['price']);
            } catch (Exception $exception) {
                $errors = error_list($exception);
            }
        }

        // 3. Saved? Show the message and reload the page.
        if (count($errors) == 0) {
            set_flash('Item added.');
            redirect('inventory.php');
        }

        // 4. Not saved: show the page again with the errors and what they typed.
        $this->showItems($errors, $form);
    }

    /**
     * Saves changes to an item's name and selling price.
     * Ported from the edit half of InventoryUI::submitItem() (based-on-old-srs).
     * NOT ON THE CLASS DIAGRAM YET: add it to InventoryUI in the SDS.
     */
    public function submitItemEdit(string $itemId, array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        $form = [
            'name' => trim($form['name'] ?? ''),
            'price' => trim($form['price'] ?? ''),
        ];

        $errors = $this->checkItemFields($form);
        if (count($errors) == 0) {
            try {
                $this->inventory->editItem($itemId, $form['name'], (float) $form['price']);
            } catch (Exception $exception) {
                $errors = error_list($exception);
            }
        }

        if (count($errors) == 0) {
            set_flash('Item updated.');
            redirect('inventory.php');
        }

        $this->showItems($errors, $form, $itemId);
    }

    /**
     * SR-1.9: InventoryMgmt::addSupplier() with the form's values; show any error.
     */
    public function submitNewSupplier(array $form): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryUI::submitNewSupplier() is not built yet.');
    }

    /**
     * SR-1.2: show which fields are missing, next to the form.
     * @param string[] $errors
     */
    private function showFieldErrors(array $errors): void
    {
        show_errors($errors);   // a red box listing each problem (src/helpers.php)
    }

    /**
     * SR-1.2: the item form's boxes must be filled in, and the price must be a number, before it is
     * handed to InventoryMgmt (which then checks the business rules). Helper; not on the class diagram.
     * @return string[] error messages (empty = fine)
     */
    private function checkItemFields(array $form): array
    {
        $errors = [];
        if ($form['name'] == '') {
            $errors[] = 'Name is required.';
        }
        if ($form['price'] == '') {
            $errors[] = 'Selling price is required.';
        } elseif (!is_numeric($form['price'])) {
            $errors[] = 'Selling price must be a number, 0 or more.';
        }
        return $errors;
    }
}
