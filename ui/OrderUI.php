<?php

/**
 * OrderUI: User Interface layer. The screens for Feature 4 (Record Orders). Staff's home page.
 * Owner: Feature 4. Copy the pattern from CustomerUI.php.
 *
 * orders.php                      -> showOrderForm()
 * form submitted (POST)           -> submitOrder()
 * orders.php?action=invoice&id=12 -> showInvoice(12)
 */
class OrderUI
{
    private OrderService $orders;
    private CustomerService $customers;
    private InventoryService $inventory;

    public function __construct()
    {
        $this->orders = new OrderService();
        $this->customers = new CustomerService();
        $this->inventory = new InventoryService();
    }

    /**
     * 4.1, 4.3: the order form: customer dropdown ($this->customers->getAllCustomers()),
     * rows of product dropdown ($this->inventory->getAllProducts()) + quantity, and a live grand total.
     * Put each product's price on its option so the JS can read it:
     *   <option value="3" data-price="1500.00">Phone case - Black</option>
     * Name the row inputs as arrays: lines[0][product_id], lines[0][quantity], ...
     * Load the script before render_footer():  <script src="ui/js/order_total.js"></script>
     */
    public function showOrderForm(array $errors = [], ?array $form = null): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 4): replace this placeholder with the form.
        render_header('Orders');
        ?>
        <h1>Orders</h1>
        <section class="card todo">
            <p><strong>Not built yet.</strong> This page will let staff:</p>
            <ul>
                <li>4.1 record an order for a customer</li>
                <li>4.2 be stopped when a line's quantity is less than 1 or more than the stock available</li>
                <li>4.3 see the grand total while entering the order</li>
                <li>4.4 get an invoice of the order details when it's submitted</li>
                <li>4.5 have inventory quantities reduced by the line quantities</li>
                <li>4.6 have the saved order linked to the customer</li>
            </ul>
        </section>
        <?php
        render_footer();
    }

    /**
     * 4.1, 4.2, 4.5, 4.6: the order form was submitted.
     *   $result = $this->orders->createOrder((int) ($post['customer_id'] ?? 0), $post['lines'] ?? []);
     *   no errors -> redirect('orders.php?action=invoice&id=' . $result['id']);   (4.4)
     *   errors    -> $this->showOrderForm($result['errors'], $post);
     */
    public function submitOrder(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 4)
        throw new Exception('OrderUI::submitOrder() is not built yet (F4).');
    }

    /**
     * 4.4: the invoice for one order: customer details, a table of lines (product, quantity,
     * unit price, line total) and the grand total. Use money(): money($line->lineTotal()), money($order->total())
     * Not found -> set_flash('Order not found.', 'error'); redirect('orders.php');
     */
    public function showInvoice(int $orderId): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        // TODO (Feature 4)
        throw new Exception('OrderUI::showInvoice() is not built yet (F4: 4.4).');
    }
}
