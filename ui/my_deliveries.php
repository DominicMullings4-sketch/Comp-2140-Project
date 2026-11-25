<?php
// Feature 6: Provide Delivery Feedback, bearer side (6.1 - 6.3). Bearers' home page.  Owner: ______
// Copy the pattern from customers.php.
//
// Plan:
//   TOP:    (new DeliveryService())->refreshStatuses();          (5.6, so today's orders show "Out for delivery")
//           $service = new DeliveryFeedbackService();
//           $bearerId = AccessControl::currentUserId();          (the signed-in bearer)
//           on POST: $service->updateDelivery($orderId, $bearerId, $status, $comment); set_flash + redirect
//           $deliveries = $service->getTodaysDeliveries($bearerId);
//   BOTTOM: one row (or card, easier on a phone) per delivery: order ID, customer name, phone, address,
//           current status, and a small form: status dropdown (Delivered / Failed) + comment + Save.

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_BEARER);

$pageTitle = 'My Deliveries';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>My Deliveries</h1>
<section class="card todo">
    <p><strong>Not built yet.</strong> This page will let a bearer:</p>
    <ul>
        <li>6.1 see their assigned deliveries for today: order number, customer name, phone number and delivery address</li>
        <li>6.2 update a delivery's status to "Delivered" or "Failed"</li>
        <li>6.3 add a comment to a delivery</li>
    </ul>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
