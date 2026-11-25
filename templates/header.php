<?php
// The top of every page. render_header() gives it $title and $user.
// The menu only shows the pages the user's role may open (SC-1).
$showStaffMenu = false;
$showOwnerMenu = false;
if ($user != null) {
    $role = $user->getRole();
    if ($role == UserRole::BusinessOwner || $role == UserRole::OfficeStaff) {
        $showStaffMenu = true;
    }
    if ($role == UserRole::BusinessOwner) {
        $showOwnerMenu = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Track-It</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($title === 'Delivery dispatch'): ?>
        <link rel="stylesheet" href="assets/css/dispatch.css">
    <?php endif; ?>
</head>
<?php $isDispatch = $title === 'Delivery dispatch'; ?>
<body<?= $isDispatch ? ' class="dispatch-page"' : '' ?>>
<header class="topbar">
    <a class="brand" href="index.php">Track-It</a>
    <?php if ($user != null): ?>
        <nav>
            <?php if ($showStaffMenu): ?>
                <a href="inventory.php">Inventory</a>
                <a href="customers.php">Customers</a>
                <a href="orders.php">Orders</a>
                <?php if ($showOwnerMenu): ?>
                    <a href="deliveries.php"<?= $isDispatch ? ' aria-current="page"' : '' ?>>Delivery dispatch</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="deliveries.php?action=today">Today's deliveries</a>
            <?php endif; ?>
            <?php if ($showOwnerMenu): ?>
                <a href="reports.php">Reports</a>
                <a href="users.php">Users</a>
            <?php endif; ?>
        </nav>
        <div class="who">
            <?= e($user->getName()) ?> · <?= e($user->getRole()->value) ?> ·
            <a href="login.php?action=logout">Sign out</a>
        </div>
    <?php endif; ?>
</header>
<?php if (DEV_AUTO_LOGIN != ''): ?>
    <div class="devbar">
        Development mode: signed in automatically as "<?= e(DEV_AUTO_LOGIN) ?>".
        Set DEV_AUTO_LOGIN to '' in config/config.php before the demo.
    </div>
<?php endif; ?>
<main>
<?php $flash = take_flash(); // a one-time message from set_flash(), shown once ?>
<?php if ($flash != null): ?>
    <p class="<?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
<?php endif; ?>
