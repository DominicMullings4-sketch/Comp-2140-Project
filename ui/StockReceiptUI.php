<?php

/**
 * StockReceiptUI: User Interface layer. The screens for Feature 1 (Add Stock Receipts).
 * Owner: Feature 1. Copy the pattern from CustomerUI.php.
 *
 * stock_receipts.php     -> showReceiptForm()
 * form submitted (POST)  -> submitReceipt()
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
     * 1.1: the stock receipt form: supplier name, date received, and several rows of
     * product (dropdown from $this->inventory->getAllProducts()) + quantity.
     * Name the row inputs as arrays so PHP receives a list:
     *   <select name="lines[0][product_id]"> ...   <input name="lines[0][quantity]" type="number" min="1">
     */
    public function showReceiptForm(array $errors = [], ?array $form = null): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 1): replace this placeholder with the form.
        render_header('Stock Receipts');
        ?>
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
        <?php
        render_footer();
    }

    /**
     * 1.1 - 1.5: the form was submitted.
     *   $result = $this->receipts->recordReceipt($supplier, $date, $post['lines'] ?? []);
     *   no errors -> set_flash('Stock receipt #' . $result['id'] . ' saved.'); redirect('stock_receipts.php');
     *   errors    -> $this->showReceiptForm($result['errors'], $post);
     */
    public function submitReceipt(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 1)
        throw new Exception('StockReceiptUI::submitReceipt() is not built yet (F1).');
    }
}
