<?php

/**
 * OrderUI: User Interface layer. The screens for Feature 4 (Record Orders). Staff's home page.
 * Owner: Feature 4. Copy the pattern from CustomerUI.php.
 *
 * orders.php                       -> showOrderForm(): the new order form + the list of previous orders
 * form submitted (POST)            -> submitOrder()
 * orders.php?action=invoice&id=12  -> showInvoice(12)
 * orders.php?action=customer&id=3  -> showCustomerOrders(3): every order for customer 3
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

        // The dropdowns: customers (Feature 3) and products (your Feature 2)
        $customers = $this->customers->getAllCustomers();
        $products = $this->inventory->getAllProducts();

        // Every order saved so far, for the "Previous orders" list below the form
        $orders = $this->orders->getAllOrders();

        // What the form starts with. If a save failed, submitOrder() passes in what was typed.
        if ($form === null) {
            $form = ['customer_id' => '', 'lines' => []];
        }
        $rowCount = 5;   // how many product rows the form shows

        render_header('Orders');
        ?>
        <h1>Orders</h1>

        <section class="card">
            <h2>New order</h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (count($customers) === 0 || count($products) === 0): ?>
                <p>To record an order you need at least one <a href="customers.php">customer</a>
                   and one <a href="inventory.php">inventory item</a>.</p>
            <?php else: ?>
                <form id="order-form" method="post" action="orders.php">
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Choose a customer</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= h($c->id) ?>" <?= (string) $c->id === (string) ($form['customer_id'] ?? '') ? 'selected' : '' ?>>
                                <?= h($c->name) ?> (<?= h($c->phone) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <h3>Products</h3>
                    <p>Fill in one row per product. Leave unused rows empty.</p>
                    <table>
                        <tr>
                            <th>Product</th>
                            <th class="number">Quantity</th>
                            <th class="number">Line total</th>
                        </tr>
                        <?php for ($i = 0; $i < $rowCount; $i++): ?>
                            <?php $chosen = $form['lines'][$i]['product_id'] ?? ''; ?>
                            <tr class="order-line">
                                <td>
                                    <select name="lines[<?= $i ?>][product_id]" aria-label="Product, row <?= $i + 1 ?>">
                                        <option value="">Choose a product</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= h($p->id) ?>" data-price="<?= h($p->sellingPrice) ?>"
                                                <?= (string) $p->id === (string) $chosen ? 'selected' : '' ?>>
                                                <?= h($p->name) ?> (<?= h(money($p->sellingPrice)) ?>, <?= h($p->quantity) ?> in stock)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="number">
                                    <input name="lines[<?= $i ?>][quantity]" type="number" min="1" step="1"
                                           value="<?= h($form['lines'][$i]['quantity'] ?? '') ?>" aria-label="Quantity, row <?= $i + 1 ?>">
                                </td>
                                <td class="number line-total"></td>
                            </tr>
                        <?php endfor; ?>
                        <tr>
                            <th colspan="2">Grand total</th>
                            <th class="number" id="grand-total">$0.00</th>
                        </tr>
                    </table>

                    <button type="submit">Save order</button>
                </form>
                <script src="ui/js/order_total.js"></script>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Previous orders</h2>
            <?php $this->showOrderTable($orders); ?>
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

        // 1. Hand what was typed to the logic layer, which checks it and saves it.
        //    is_array() guards against a tampered form sending something that isn't a list.
        $lines = is_array($post['lines'] ?? null) ? $post['lines'] : [];
        $result = $this->orders->createOrder((int) ($post['customer_id'] ?? 0), $lines);

        // 2. Saved? Go straight to the order's invoice (4.4).
        if (count($result['errors']) === 0) {
            set_flash('Order #' . $result['id'] . ' saved.');
            redirect('orders.php?action=invoice&id=' . $result['id']);
        }

        // 3. Not saved: show the form again with the errors and what they typed.
        $this->showOrderForm($result['errors'], $post);
    }

    /**
     * 4.4: the invoice for one order: customer details, a table of lines (product, quantity,
     * unit price, line total) and the grand total. Use money(): money($line->lineTotal()), money($order->total())
     * Not found -> set_flash('Order not found.', 'error'); redirect('orders.php');
     */
    public function showInvoice(int $orderId): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        $order = $this->orders->getOrder($orderId);
        if ($order === null) {
            set_flash('That order was not found.', 'error');
            redirect('orders.php');
        }

        render_header('Invoice #' . $order->id);
        ?>
        <p><a href="orders.php">&larr; New order</a></p>
        <h1>Invoice: Order #<?= h($order->id) ?></h1>

        <section class="card">
            <table>
                <tr><th>Order date</th><td><?= h($order->createdAt) ?></td></tr>
                <tr><th>Delivery date</th><td><?= h($order->deliveryDate) ?></td></tr>
                <tr><th>Customer</th><td><?= h($order->customerName) ?></td></tr>
                <tr><th>Delivery address</th><td><?= h($order->customerAddress) ?></td></tr>
                <tr><th>Phone</th><td><?= h($order->customerPhone) ?></td></tr>
                <tr><th>Email</th><td><?= h($order->customerEmail) ?></td></tr>
            </table>
        </section>

        <section class="card">
            <h2>Items</h2>
            <table>
                <tr>
                    <th>Product</th>
                    <th class="number">Quantity</th>
                    <th class="number">Unit price</th>
                    <th class="number">Line total</th>
                </tr>
                <?php foreach ($order->lines as $line): ?>
                    <tr>
                        <td><?= h($line->productName) ?></td>
                        <td class="number"><?= h($line->quantity) ?></td>
                        <td class="number"><?= h(money($line->unitPrice)) ?></td>
                        <td class="number"><?= h(money($line->lineTotal())) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">Grand total</th>
                    <th class="number"><?= h(money($order->total())) ?></th>
                </tr>
            </table>
            <button type="button" onclick="window.print()">Print invoice</button>
        </section>
        <?php
        render_footer();
    }

    /**
     * Every order for one customer: the customer's details, then their orders
     * (each Order ID opens the invoice).
     */
    public function showCustomerOrders(int $customerId): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);

        $customer = $this->customers->getCustomer($customerId);
        if ($customer === null) {
            set_flash('That customer was not found.', 'error');
            redirect('orders.php');
        }
        $orders = $this->orders->getAllOrders($customerId);

        render_header('Orders for ' . $customer->name);
        ?>
        <p><a href="orders.php">&larr; All orders</a></p>
        <h1>Orders for <?= h($customer->name) ?></h1>

        <section class="card">
            <table>
                <tr><th>Phone</th><td><?= h($customer->phone) ?></td></tr>
                <tr><th>Email</th><td><?= h($customer->email) ?></td></tr>
                <tr><th>Delivery address</th><td><?= h($customer->deliveryAddress) ?></td></tr>
            </table>
        </section>

        <section class="card">
            <h2><?= count($orders) ?> order(s)</h2>
            <?php $this->showOrderTable($orders); ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * Prints a table of orders: Order ID (opens the invoice) | Created at | Status | Customer (opens their orders).
     * Shared by showOrderForm() and showCustomerOrders(), so the table is written once.
     * @param Order[] $orders
     */
    private function showOrderTable(array $orders): void
    {
        if (count($orders) === 0) {
            echo '<p>No orders yet.</p>';
            return;
        }
        ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Created at</th>
                <th>Status</th>
                <th>Customer</th>
            </tr>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="orders.php?action=invoice&amp;id=<?= h($o->id) ?>">#<?= h($o->id) ?></a></td>
                    <td><?= h($o->createdAt) ?></td>
                    <td><?= h($o->status) ?></td>
                    <td>
                        <a href="orders.php?action=customer&amp;id=<?= h($o->customerId) ?>">
                            <?= h($o->customerName) ?> (<?= h($o->customerPhone) ?>)
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
}
