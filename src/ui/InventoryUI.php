<?php

/**
 * InventoryUI: User Interface layer.
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
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
     */
    public function showItems(): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);   // SC-1: who may see this page
        $items = $this->inventory->getItems();   // ask the logic layer for the data

        render_header('Inventory', $user);
        ?>
        <h1>Inventory</h1>
        <p><a class="button" href="inventory.php?action=receipt">Record stock received</a></p>
        <table>
            <thead>
                <tr><th>Item</th><th class="num">Selling price</th><th class="num">Stock</th><th class="num">Reserved</th><th class="num">Available</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item->getName()) ?></td>
                    <td class="num"><?= money($item->getSellingPrice()) ?></td>
                    <td class="num"><?= $item->getStockQuantity() ?></td>
                    <td class="num"><?= $item->getReservedQuantity() ?></td>
                    <td class="num"><?= $item->getAvailableQuantity() ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($items) == 0): ?>
                <tr><td colspan="5">No items yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        render_footer();
    }

    /**
     * SR-1.1, SR-1.8: supplier dropdown (InventoryMgmt::getSuppliers()), date received, and rows of item /
     * quantity / unit cost.
     */
    public function showStockReceiptForm(): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryUI::showStockReceiptForm() is not built yet.');
    }

    /**
     * SR-1.2: if a required field is empty, showFieldErrors(); otherwise createStockReceipt(), addLine()
     * per row, saveStockReceipt().
     */
    public function submitStockReceipt(array $form): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryUI::submitStockReceipt() is not built yet.');
    }

    /**
     * SR-1.4: InventoryMgmt::addItem() with the form's name and selling price; show any error.
     */
    public function submitNewItem(array $form): void
    {
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryUI::submitNewItem() is not built yet.');
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
        // TODO (P2): build this method. The note above says what it must do.
        throw new Exception('InventoryUI::showFieldErrors() is not built yet.');
    }
}
