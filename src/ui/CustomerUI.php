<?php

/**
 * CustomerUI: User Interface layer.
 * Feature owner: P3 (see the Track-It demo build plan whiteboard).
 * Ported from CustomerUI (and the per-customer orders page in OrderUI) on the based-on-old-srs branch.
 *
 * customers.php                          -> showSearch(): search box, matching customers, "Add customer" form
 * customers.php?action=new       (POST)  -> submitNewCustomer()
 * customers.php?action=view&id=C-..      -> showCustomer(): edit form + the customer's orders
 * customers.php?action=edit&id=C-.. (POST) -> submitCustomerEdit()
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class CustomerUI
{
    private UserPermission $permission;
    private CustomerMgmt $customers;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, CustomerMgmt.
     */
    public function __construct(UserPermission $permission, CustomerMgmt $customers)
    {
        $this->permission = $permission;
        $this->customers = $customers;
    }

    /**
     * SR-2.6: search box plus the matching customers (CustomerMgmt::searchCustomers()).
     * An empty search lists every customer. The "Add customer" form is on this page too.
     * $errors and $form are only passed in by submitNewCustomer() when a save failed.
     */
    public function showSearch(string $searchText, array $errors = [], ?array $form = null): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);   // SC-1
        $customers = $this->customers->searchCustomers($searchText);   // ask the logic layer for the data

        if ($form == null) {
            $form = ['name' => '', 'address' => '', 'phone' => '', 'social' => ''];
        }

        render_header('Customers', $user);
        ?>
        <h1>Customers</h1>

        <section class="card">
            <h2>Add customer</h2>
            <?php $this->showFieldErrors($errors); ?>
            <?php $this->showCustomerForm('customers.php?action=new', $form, 'Add customer'); ?>
        </section>

        <section class="card">
            <h2>Find a customer</h2>
            <form method="get" action="customers.php">
                <label for="q">Name, phone or social media handle</label>
                <input id="q" name="q" value="<?= e($searchText) ?>">
                <button type="submit">Search</button>
                <?php if ($searchText != ''): ?>
                    <a class="button secondary" href="customers.php">Show all</a>
                <?php endif; ?>
            </form>

            <?php if (count($customers) == 0): ?>
                <p>No customers found.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Delivery address</th>
                        <th>Phone</th>
                        <th>Social media</th>
                        <th></th>
                    </tr>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= e($customer->getName()) ?></td>
                            <td><?= e($customer->getAddress()) ?></td>
                            <td><?= e($customer->getPhone()) ?></td>
                            <td><?= e($customer->getSocialMediaHandle()) ?></td>
                            <td><a href="customers.php?action=view&amp;id=<?= e(urlencode($customer->getCustomerId())) ?>">View / edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * SR-2.7: the customer's details plus their past orders.
     * The details are in an edit form (SR-2.2). Each Order ID opens the order.
     */
    public function showCustomer(string $customerId, array $errors = [], ?array $form = null): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $customer = $this->customers->getCustomerById($customerId);
        } catch (Exception $exception) {
            set_flash('That customer was not found.', 'error');
            redirect('customers.php');
        }
        $orders = $this->customers->getOrderHistory($customer);

        // What the form boxes show: the saved details, or what was typed if a save failed.
        if ($form == null) {
            $form = [
                'name' => $customer->getName(),
                'address' => $customer->getAddress(),
                'phone' => $customer->getPhone(),
                'social' => $customer->getSocialMediaHandle() ?? '',
            ];
        }
        $formAction = 'customers.php?action=edit&id=' . urlencode($customer->getCustomerId());

        render_header('Customer: ' . $customer->getName(), $user);
        ?>
        <p><a href="customers.php">&larr; All customers</a></p>
        <h1><?= e($customer->getName()) ?></h1>

        <section class="card">
            <h2>Edit customer</h2>
            <?php $this->showFieldErrors($errors); ?>
            <?php $this->showCustomerForm($formAction, $form, 'Save changes'); ?>
        </section>

        <section class="card">
            <h2><?= count($orders) ?> order(s)</h2>
            <p><a class="button" href="orders.php?action=new&amp;customer=<?= e(urlencode($customer->getCustomerId())) ?>">New order for this customer</a></p>
            <?php if (count($orders) > 0): ?>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Entered at</th>
                        <th>Status</th>
                        <th class="num">Total</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><a href="orders.php?action=view&amp;order=<?= e(urlencode($order->getOrderId())) ?>"><?= e($order->getOrderId()) ?></a></td>
                            <td><?= e($order->getEnteredAt()) ?></td>
                            <td><?= e($order->getOrderStatus()->value) ?></td>
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
     * SR-2.1 to SR-2.5: CustomerMgmt::createCustomer(); on a duplicate, show the existing record.
     * (A duplicate shows an error naming the phone or handle that's already used.)
     */
    public function submitNewCustomer(array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);
        $form = $this->readForm($form);

        try {
            $this->customers->createCustomer($form['name'], $form['address'], $form['phone'], $form['social']);
        } catch (Exception $exception) {
            // Not saved: show the form again with the errors and what they typed.
            $this->showSearch('', error_list($exception), $form);
            return;
        }

        // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't save it twice.
        set_flash('Customer added.');
        redirect('customers.php');
    }

    /**
     * SR-2.2: CustomerMgmt::editCustomer() with the form's values.
     */
    public function submitCustomerEdit(string $customerId, array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);
        $form = $this->readForm($form);

        try {
            $customer = $this->customers->getCustomerById($customerId);
            $this->customers->editCustomer($customer, $form['name'], $form['address'], $form['phone'], $form['social']);
        } catch (Exception $exception) {
            $this->showCustomer($customerId, error_list($exception), $form);
            return;
        }

        set_flash('Customer updated.');
        redirect('customers.php?action=view&id=' . urlencode($customerId));
    }

    /**
     * SC-5: CustomerMgmt::anonymizeCustomer() after the user confirms.
     */
    public function submitAnonymize(string $customerId): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::submitAnonymize() is not built yet.');
    }

    /**
     * SR-2.3: show each empty field by name.
     * @param string[] $errors
     */
    private function showFieldErrors(array $errors): void
    {
        show_errors($errors);   // a red box listing each problem (src/helpers.php)
    }

    /**
     * The add / edit customer form (the same boxes on both pages). Helper; not on the class diagram.
     */
    private function showCustomerForm(string $formAction, array $form, string $buttonText): void
    {
        ?>
        <form method="post" action="<?= e($formAction) ?>">
            <label for="name">Name</label>
            <input id="name" name="name" value="<?= e($form['name']) ?>" required>

            <label for="address">Delivery address</label>
            <input id="address" name="address" value="<?= e($form['address']) ?>" required>

            <label for="phone">Phone number (10 digits)</label>
            <input id="phone" name="phone" type="tel" value="<?= e($form['phone']) ?>" required>

            <label for="social">Social media handle (optional)</label>
            <input id="social" name="social" value="<?= e($form['social']) ?>">

            <button type="submit"><?= e($buttonText) ?></button>
        </form>
        <?php
    }

    /**
     * Reads what was typed into the customer form. Helper; not on the class diagram.
     */
    private function readForm(array $form): array
    {
        return [
            'name' => trim($form['name'] ?? ''),
            'address' => trim($form['address'] ?? ''),
            'phone' => trim($form['phone'] ?? ''),
            'social' => trim($form['social'] ?? ''),
        ];
    }
}
