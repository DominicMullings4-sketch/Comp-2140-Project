<?php

/**
 * OrderUI: User Interface layer.
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 * showOrders, showNewOrderForm, submitOrder and showOrder are ported from OrderUI on the based-on-old-srs branch.
 *
 * orders.php                              -> showOrders(): every order
 * orders.php?action=new&customer=C-..     -> showNewOrderForm(); POST -> submitOrder()
 * orders.php?action=view&order=ORD-..     -> showOrder(): one order with its lines and total
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class OrderUI
{
    private UserPermission $permission;
    private OrderMgmt $orders;
    private CustomerMgmt $customers;
    private InventoryMgmt $inventory;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, OrderMgmt, CustomerMgmt, InventoryMgmt.
     */
    public function __construct(UserPermission $permission, OrderMgmt $orders, CustomerMgmt $customers, InventoryMgmt $inventory)
    {
        $this->permission = $permission;
        $this->orders = $orders;
        $this->customers = $customers;
        $this->inventory = $inventory;
    }

    /**
     * Orders list with order status, delivery status and the actions allowed for each.
     * (The cancel / invoice / confirm buttons are still to be added with those methods.)
     */
    public function showOrders(): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);
        $orders = $this->orders->getOrders();

        render_header('Orders', $user);
        ?>
        <h1>Orders</h1>
        <p><a class="button" href="orders.php?action=new">New order</a></p>

        <section class="card">
            <h2>All orders</h2>
            <?php if (count($orders) == 0): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Entered at</th>
                        <th>Customer</th>
                        <th>Order status</th>
                        <th>Delivery status</th>
                        <th class="num">Total</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><a href="orders.php?action=view&amp;order=<?= e(urlencode($order->getOrderId())) ?>"><?= e($order->getOrderId()) ?></a></td>
                            <td><?= e($order->getEnteredAt()) ?></td>
                            <td>
                                <a href="customers.php?action=view&amp;id=<?= e(urlencode($order->getCustomer()->getCustomerId())) ?>">
                                    <?= e($order->getCustomer()->getName()) ?> (<?= e($order->getCustomer()->getPhone()) ?>)
                                </a>
                            </td>
                            <td><?= e($order->getOrderStatus()->value) ?></td>
                            <td><?= e($order->getDeliveryStatus()->value) ?></td>
                            <td class="num"><?= money($order->getOrderTotal()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-3.1, SR-3.2: items with their available quantity, quantity boxes and an optional order source.
     * The customer dropdown starts on $customerId (when the page is opened from a customer's page),
     * and a live total is worked out by assets/js/order_total.js (SR-3.4).
     * $errors and $form are only passed in by submitOrder() when a save failed.
     */
    public function showNewOrderForm(string $customerId, array $errors = [], ?array $form = null): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        // The dropdowns: customers (Feature 2) and items (Feature 1)
        $customers = $this->customers->searchCustomers('');   // an empty search gives every customer
        $items = $this->inventory->getItems();

        // What the form starts with. If a save failed, submitOrder() passes in what was typed.
        if ($form == null) {
            $form = ['customer_id' => $customerId, 'order_source' => '', 'lines' => []];
        }
        $rowCount = 5;   // how many item rows the form shows

        render_header('New order', $user);
        ?>
        <p><a href="orders.php">&larr; All orders</a></p>
        <h1>New order</h1>

        <section class="card">
            <?php show_errors($errors); ?>

            <?php if (count($customers) == 0 || count($items) == 0): ?>
                <p>To record an order you need at least one <a href="customers.php">customer</a>
                   and one <a href="inventory.php">inventory item</a>.</p>
            <?php else: ?>
                <form id="order-form" method="post" action="orders.php?action=new">
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Choose a customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= e($customer->getCustomerId()) ?>" <?= $customer->getCustomerId() == ($form['customer_id'] ?? '') ? 'selected' : '' ?>>
                                <?= e($customer->getName()) ?> (<?= e($customer->getPhone()) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="order_source">Order source (optional)</label>
                    <select id="order_source" name="order_source">
                        <option value="">Not specified</option>
                        <?php foreach (OrderSource::cases() as $source): ?>
                            <option value="<?= e($source->value) ?>" <?= $source->value == ($form['order_source'] ?? '') ? 'selected' : '' ?>>
                                <?= e($source->value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <h3>Items</h3>
                    <p>Fill in one row per item. Leave unused rows empty.</p>
                    <table>
                        <tr>
                            <th>Item</th>
                            <th class="num">Quantity</th>
                            <th class="num">Line total</th>
                        </tr>
                        <?php for ($i = 0; $i < $rowCount; $i++): ?>
                            <?php $chosen = $form['lines'][$i]['item_id'] ?? ''; ?>
                            <tr class="order-line">
                                <td>
                                    <select name="lines[<?= $i ?>][item_id]" aria-label="Item, row <?= $i + 1 ?>">
                                        <option value="">Choose an item</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?= e($item->getItemId()) ?>" data-price="<?= e($item->getSellingPrice()) ?>"
                                                <?= $item->getItemId() == $chosen ? 'selected' : '' ?>>
                                                <?= e($item->getName()) ?> (<?= money($item->getSellingPrice()) ?>, <?= $item->getAvailableQuantity() ?> available)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="num">
                                    <input name="lines[<?= $i ?>][quantity]" type="number" min="1" step="1"
                                           value="<?= e($form['lines'][$i]['quantity'] ?? '') ?>" aria-label="Quantity, row <?= $i + 1 ?>">
                                </td>
                                <td class="num line-total"></td>
                            </tr>
                        <?php endfor; ?>
                        <tr>
                            <th colspan="2">Order total</th>
                            <th class="num" id="grand-total">$0.00</th>
                        </tr>
                    </table>

                    <button type="submit">Save order</button>
                </form>
                <script src="assets/js/order_total.js"></script>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-3.1 to SR-3.6: createOrder(), addOrderLine() per line, saveOrder(); show errors such as quantity
     * above the available quantity.
     * The row checks are the ones from OrderService::createOrder() on based-on-old-srs.
     */
    public function submitOrder(array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        $customerId = trim($form['customer_id'] ?? '');
        // is_array() guards against a tampered form sending something that isn't a list.
        $lines = is_array($form['lines'] ?? null) ? $form['lines'] : [];
        $errors = [];

        // PART 1 - the customer must exist; then make the order (nothing is saved yet)
        $order = null;
        try {
            if ($customerId == '') {
                throw new Exception('Choose a customer.');
            }
            $customer = $this->customers->getCustomerById($customerId);
            $source = OrderSource::tryFrom($form['order_source'] ?? '');   // null when "Not specified"
            $order = $this->orders->createOrder($customer, $source);
        } catch (Exception $exception) {
            $errors[] = $exception->getMessage();
        }

        // PART 2 - check each item row and add it to the order. Rows left completely empty are skipped.
        $items = [];   // item ID => Item, so two rows for the same item share one Item object
        foreach ($lines as $i => $line) {
            $itemId = trim($line['item_id'] ?? '');
            $quantity = trim($line['quantity'] ?? '');
            $rowNumber = $i + 1;   // people count rows from 1, PHP counts from 0

            if ($itemId == '' && $quantity == '') {
                continue;   // nothing typed in this row: skip it
            }

            // a row needs both an item and a quantity
            if ($itemId == '' || $quantity == '') {
                $errors[] = "Row $rowNumber: choose an item and enter a quantity.";
                continue;
            }

            // SR-3.3: quantity must be a whole number (checked here, while it's still text)
            if (!ctype_digit($quantity)) {
                $errors[] = "Row $rowNumber: quantity must be a whole number, 1 or more.";
                continue;
            }

            try {
                if (!isset($items[$itemId])) {
                    $items[$itemId] = $this->inventory->getItemById($itemId);   // the item must really exist
                }
                if ($order != null) {
                    // SR-3.3: the Order checks 1 or more, and not more than the available quantity
                    $this->orders->addOrderLine($order, $items[$itemId], (int) $quantity);
                }
            } catch (Exception $exception) {
                $errors[] = "Row $rowNumber: " . lcfirst($exception->getMessage());
            }
        }

        // PART 3 - the order needs at least one item
        if (count($errors) == 0 && count($order->getLines()) == 0) {
            $errors[] = 'Add at least one item to the order.';
        }

        // PART 4 - any problems? Show the form again with the errors and what they typed. Nothing is saved.
        if (count($errors) > 0) {
            $this->showNewOrderForm($customerId, $errors, $form);
            return;
        }

        // PART 5 - save the order, its lines and the reserved stock (one transaction, in OrderMgmt)
        try {
            $this->orders->saveOrder($order);
        } catch (Exception $exception) {
            $this->showNewOrderForm($customerId, error_list($exception), $form);
            return;
        }

        // Saved: go straight to the order's page.
        set_flash('Order ' . $order->getOrderId() . ' saved.');
        redirect('orders.php?action=view&order=' . urlencode($order->getOrderId()));
    }

    /**
     * One order: customer details, a table of lines (item, quantity, unit price, line total) and the total.
     * This was the "invoice" page (showInvoice) on based-on-old-srs. On this structure the real invoice is
     * the Invoice class (generateInvoice / downloadInvoice), so this page is called showOrder.
     * NOT ON THE CLASS DIAGRAM YET: add it to OrderUI in the SDS.
     */
    public function showOrder(string $orderId): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $order = $this->orders->getOrderById($orderId);
        } catch (Exception $exception) {
            set_flash('That order was not found.', 'error');
            redirect('orders.php');
        }
        $customer = $order->getCustomer();
        $source = 'Not specified';
        if ($order->getOrderSource() != null) {
            $source = $order->getOrderSource()->value;
        }

        render_header('Order ' . $order->getOrderId(), $user);
        ?>
        <p><a href="orders.php">&larr; All orders</a></p>
        <h1>Order <?= e($order->getOrderId()) ?></h1>

        <section class="card">
            <table>
                <tr><th>Entered at</th><td><?= e($order->getEnteredAt()) ?></td></tr>
                <tr><th>Order status</th><td><?= e($order->getOrderStatus()->value) ?></td></tr>
                <tr><th>Delivery status</th><td><?= e($order->getDeliveryStatus()->value) ?></td></tr>
                <tr><th>Order source</th><td><?= e($source) ?></td></tr>
                <tr><th>Customer</th><td><?= e($customer->getName()) ?></td></tr>
                <tr><th>Delivery address</th><td><?= e($customer->getAddress()) ?></td></tr>
                <tr><th>Phone</th><td><?= e($customer->getPhone()) ?></td></tr>
                <tr><th>Social media</th><td><?= e($customer->getSocialMediaHandle()) ?></td></tr>
            </table>
        </section>

        <section class="card">
            <h2>Items</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th class="num">Quantity</th>
                    <th class="num">Unit price</th>
                    <th class="num">Line total</th>
                </tr>
                <?php foreach ($order->getLines() as $line): ?>
                    <tr>
                        <td><?= e($line->getItem()->getName()) ?></td>
                        <td class="num"><?= $line->getQuantityOrdered() ?></td>
                        <td class="num"><?= money($line->getUnitPrice()) ?></td>
                        <td class="num"><?= money($line->getLineTotal()) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="3">Order total</th>
                    <th class="num"><?= money($order->getOrderTotal()) ?></th>
                </tr>
            </table>
            <button type="button" onclick="window.print()">Print</button>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-3.7: OrderMgmt::cancelOrder().
     */
    public function submitCancel(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitCancel() is not built yet.');
    }

    /**
     * SR-4.1: OrderMgmt::generateInvoice().
     */
    public function submitGenerateInvoice(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitGenerateInvoice() is not built yet.');
    }

    /**
     * SR-4.6: OrderMgmt::confirmOrder().
     */
    public function submitConfirm(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitConfirm() is not built yet.');
    }

    /**
     * SR-4.4: OrderMgmt::downloadInvoicePdf(), then send the file to the browser with a PDF content type.
     */
    public function downloadInvoice(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::downloadInvoice() is not built yet.');
    }
}
