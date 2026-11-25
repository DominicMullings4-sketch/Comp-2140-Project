<?php
// Feature 4: Record Orders (4.1 - 4.6). Staff's home page.  Owner: ______
// Copy the pattern from customers.php.
//
// Plan:
//   TOP:    $customers = (new CustomerService())->getAllCustomers();   (customer dropdown)
//           $products  = (new InventoryService())->getAllProducts();   (product dropdowns + prices)
//           on POST: $result = (new OrderService())->createOrder($customerId, $lines);
//                    no errors -> redirect('invoice.php?id=' . $result['id']);   (4.4)
//   BOTTOM: customer dropdown, one or more lines (product dropdown + quantity), live grand total (4.3).
//           Put each product's price on its <option> so the JS can read it:
//             <option value="3" data-price="1500.00">Phone case - Black</option>
//           Name the line inputs as arrays: lines[0][product_id], lines[0][quantity], ...
//           Load the script just before the footer:  <script src="js/order_total.js"></script>

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';
AccessControl::requireRole(User::ROLE_STAFF);

$pageTitle = 'Orders';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<h1>Orders</h1>
<section class="card todo">
    <p><strong>Not built yet.</strong> This page will let staff:</p>
    <ul>
        <li>4.1 record an order for a customer</li>
        <li>4.2 be stopped when a line's quantity is less than 1 or more than the stock available</li>
        <li>4.3 see the grand total while entering the order</li>
        <li>4.4 get an invoice of the order details when it's submitted</li>
        <li>4.5 have inventory quantities reduced by the line quantities</li>
        <li>4.6 have the saved order linked to the customer</li>
    </ul>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
