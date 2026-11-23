<?php
// Home page: sends each user to their starting page.
require __DIR__ . '/src/bootstrap.php';

$user = $userPermission->getCurrentUser();

if ($user == null) {
    redirect('login.php');
} elseif ($user->getRole() == UserRole::DeliveryWorker) {
    redirect('deliveries.php?action=today');
} else {
    redirect('orders.php');
}
