<?php
// Staff deliveries page.
//   Feature 5: Confirm Delivery (5.5, 5.6)         Owner: ______
//   Feature 6: review and filter (6.4, 6.5)        Owner: ______
// Two sections on one page; each owner builds their own section.
// Copy the pattern from customers.php.
//
// Plan:
//   TOP:    $delivery = new DeliveryService();
//           $delivery->refreshStatuses();                      (5.6)
//           Feature 5 section: $orders = $delivery->getOrdersForDate($date);  $bearers = $delivery->getBearers();
//                              on POST: $delivery->assignBearer($orderId, $bearerId); set_flash + redirect
//           Feature 6 section: $statusFilter = $_GET['status'] ?? null;
//                              $all = (new DeliveryFeedbackService())->getDeliveries($statusFilter);
//   BOTTOM: Feature 5: orders due on a date, each with a bearer dropdown + Assign button.
//           Feature 6: a status filter (dropdown of Order::ALL_STATUSES) + table with status and comment.

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);

$pageTitle = 'Deliveries';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Deliveries</h1>

<section class="card todo">
    <h2>Assign bearers (Feature 5)</h2>
    <p><strong>Not built yet.</strong> This section will let staff:</p>
    <ul>
        <li>see each order's delivery date (5.1, 5.3, 5.4: same day before 10:00 AM, next day after) and status (5.2)</li>
        <li>5.5 assign a bearer to an order</li>
        <li>5.6 have assigned orders change to "Out for delivery" on their delivery date</li>
    </ul>
</section>

<section class="card todo">
    <h2>Delivery feedback (Feature 6)</h2>
    <p><strong>Not built yet.</strong> This section will let staff:</p>
    <ul>
        <li>6.4 review each delivery's status and the bearer's comments</li>
        <li>6.5 filter deliveries by status</li>
    </ul>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
