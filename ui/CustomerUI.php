<?php

/**
 * CustomerUI: User Interface layer. The screens for Feature 3 (Storing Customer Information).
 * WORKED EXAMPLE: fully built. Copy this class's pattern for your own UI class.
 *
 * customers.php          -> showCustomers(): list of customers + "Add customer" form
 * customers.php?edit=5   -> showCustomers(): list of customers + "Edit customer" form for customer 5
 * form submitted (POST)  -> submitCustomer()
 */
class CustomerUI
{
    private CustomerService $customers;

    public function __construct()
    {
        $this->customers = new CustomerService();
    }

    /**
     * 3.3: shows the list of customers and the add/edit form.
     * $errors and $form are only passed in by submitCustomer() when a save failed,
     * so the form shows the messages AND what the user typed.
     */
    public function showCustomers(array $errors = [], ?array $form = null): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only
        $editId = $this->editId();

        // What the form boxes show: empty when adding, the customer's details when editing.
        if ($form === null) {
            $form = ['name' => '', 'address' => '', 'phone' => '', 'email' => ''];
            if ($editId !== null) {
                $customer = $this->customers->getCustomer($editId);
                if ($customer === null) {
                    set_flash('That customer was not found.', 'error');
                    redirect('customers.php');
                }
                $form = [
                    'name' => $customer->name,
                    'address' => $customer->deliveryAddress,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ];
            }
        }

        $customers = $this->customers->getAllCustomers();   // ask the logic layer for the data
        $formAction = ($editId === null) ? 'customers.php' : 'customers.php?edit=' . $editId;

        render_header('Customers');
        ?>
        <h1>Customers</h1>

        <section class="card">
            <h2><?= $editId === null ? 'Add customer' : 'Edit customer' ?></h2>

            <?php if (count($errors) > 0): ?>
                <ul class="flash error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="<?= h($formAction) ?>">
                <label for="name">Name</label>
                <input id="name" name="name" value="<?= h($form['name']) ?>" required>

                <label for="address">Delivery address</label>
                <input id="address" name="address" value="<?= h($form['address']) ?>" required>

                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="<?= h($form['phone']) ?>" required>

                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="<?= h($form['email']) ?>" required>

                <button type="submit"><?= $editId === null ? 'Add customer' : 'Save changes' ?></button>
                <?php if ($editId !== null): ?>
                    <a class="button secondary" href="customers.php">Cancel</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="card">
            <h2>All customers</h2>
            <?php if (count($customers) === 0): ?>
                <p>No customers yet.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Delivery address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= h($c->name) ?></td>
                            <td><?= h($c->deliveryAddress) ?></td>
                            <td><?= h($c->phone) ?></td>
                            <td><?= h($c->email) ?></td>
                            <td><a href="customers.php?edit=<?= h($c->id) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
        <?php
        render_footer();
    }

    /**
     * 3.1, 3.2, 3.3: the add/edit form was submitted. Hands what was typed to the logic layer,
     * which checks the rules and saves.
     */
    public function submitCustomer(array $post): void
    {
        AccessControl::requireRole(User::ROLE_STAFF);
        $editId = $this->editId();

        $form = [
            'name' => trim($post['name'] ?? ''),
            'address' => trim($post['address'] ?? ''),
            'phone' => trim($post['phone'] ?? ''),
            'email' => trim($post['email'] ?? ''),
        ];

        if ($editId === null) {
            $errors = $this->customers->addCustomer($form['name'], $form['address'], $form['phone'], $form['email']);
            $message = 'Customer added.';
        } else {
            $errors = $this->customers->updateCustomer($editId, $form['name'], $form['address'], $form['phone'], $form['email']);
            $message = 'Customer updated.';
        }

        // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't save it twice.
        if (count($errors) === 0) {
            set_flash($message);
            redirect('customers.php');
        }

        // Not saved: show the form again with the errors and what they typed.
        $this->showCustomers($errors, $form);
    }

    /** Which customer is being edited? (customers.php?edit=5) Null means "adding a new one". */
    private function editId(): ?int
    {
        return isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    }
}
