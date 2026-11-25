<?php
// Feature 4.4: the invoice for one order.  invoice.php?id=12   Owner: ______ (same as Feature 4)
//
// Plan:
//   TOP:    $order = (new OrderService())->getOrder((int) ($_GET['id'] ?? 0));
//           not found -> set_flash('Order not found.', 'error'); redirect('orders.php');
//   BOTTOM: order number, date, delivery date, customer name / address / phone / email,
//           a table of lines (product, quantity, unit price, line total) and the grand total.
//           Use money() for prices:  money($line->lineTotal())   money($order->total())

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);

$pageTitle = 'Invoice';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Invoice</h1>
<section class="card todo">
    <p><strong>Not built yet.</strong> This page will show the invoice for a saved order (4.4):
       customer details, each product with quantity and price, and the grand total.</p>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
