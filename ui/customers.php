<?php
// Feature 3: Storing Customer Information (3.1, 3.2, 3.3).
// WORKED EXAMPLE: fully built. Copy this page's pattern for your own feature:
//   TOP half    = PHP only: check the role, handle the form, load the data into variables.
//   BOTTOM half = HTML only: print the variables with h(), repeat rows with foreach.
//
//   customers.php          -> list of customers + "Add customer" form
//   customers.php?edit=5   -> list of customers + "Edit customer" form for customer 5

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);   // the gate: staff only

$service = new CustomerService();
$errors = [];

// Which customer is being edited? No ?edit= in the address means we're adding a new one.
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;

// What the form boxes show: empty when adding, the customer's details when editing.
$form = ['name' => '', 'address' => '', 'phone' => '', 'email' => ''];
if ($editId !== null) {
    $customer = $service->getCustomer($editId);
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

// The form was submitted: hand what was typed to the service, which checks the rules and saves.
if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
    ];

    if ($editId === null) {
        $errors = $service->addCustomer($form['name'], $form['address'], $form['phone'], $form['email']);
        $message = 'Customer added.';
    } else {
        $errors = $service->updateCustomer($editId, $form['name'], $form['address'], $form['phone'], $form['email']);
        $message = 'Customer updated.';
    }

    // Saved? Redirect (Post -> Redirect -> Get), so pressing refresh can't save it twice.
    // Not saved? Fall through and show the form again with the errors and what they typed.
    if (count($errors) === 0) {
        set_flash($message);
        redirect('customers.php');
    }
}

$customers = $service->getAllCustomers();
$pageTitle = 'Customers';
$formAction = ($editId === null) ? 'customers.php' : 'customers.php?edit=' . $editId;
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

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

<?php include __DIR__ . '/partials/footer.php'; ?>
