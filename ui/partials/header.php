<?php
// The top of every page: the page title, the menu for the signed-in user's role,
// and any one-time message (set_flash). UI classes call render_header('Title') to print it.
$role = AccessControl::currentRole();
$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | Track-It</title>
    <link rel="stylesheet" href="ui/css/style.css">
</head>
<body>
<header class="topbar">
    <span class="brand">Track-It</span>

    <?php if ($role === User::ROLE_STAFF): ?>
        <nav>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
            <a href="inventory.php">Inventory</a>
            <a href="stock_receipts.php">Stock Receipts</a>
            <a href="deliveries.php">Deliveries</a>
            <a href="delivery_feedback.php">Delivery Feedback</a>
            <a href="login.php?action=logout">Log out (<?= h(AccessControl::currentUsername()) ?>)</a>
        </nav>
    <?php elseif ($role === User::ROLE_BEARER): ?>
        <nav>
            <a href="my_deliveries.php">My Deliveries</a>
            <a href="login.php?action=logout">Log out (<?= h(AccessControl::currentUsername()) ?>)</a>
        </nav>
    <?php endif; ?>
</header>

<main>
<?php if ($flash !== null): ?>
    <p class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></p>
<?php endif; ?>
