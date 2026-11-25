<?php

/**
 * OrderUI: User Interface layer.
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class OrderUI
{
    private UserPermission $permission;
    private OrderMgmt $orders;
    private CustomerMgmt $customers;
    private InventoryMgmt $inventory;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, OrderMgmt, CustomerMgmt, InventoryMgmt.
     */
    public function __construct(UserPermission $permission, OrderMgmt $orders, CustomerMgmt $customers, InventoryMgmt $inventory)
    {
        $this->permission = $permission;
        $this->orders = $orders;
        $this->customers = $customers;
        $this->inventory = $inventory;
    }

    /**
     * Orders list with order status, delivery status and the actions allowed for each.
     */
    public function showOrders(): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);
        $orders = $this->orders->getOrders();

        render_header('Orders', $user);
        ?>
        <h1>Orders</h1>
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Delivery</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order->getOrderId()) ?></td>
                    <td><?= e($order->getCustomer()->getName()) ?></td>
                    <td><?= e($order->getOrderStatus()->value) ?></td>
                    <td><?= e($order->getDeliveryStatus()->value) ?></td>
                    <td><!-- Add links/forms for actions allowed for this order. --></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        render_footer();
    }

    /**
     * SR-3.1, SR-3.2: items with their available quantity, quantity boxes and an optional order source.
     */
    public function showNewOrderForm(string $customerId): void
    {
        $user = $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);
        $customer = $this->customers->getCustomerById($customerId);
        $items = $this->inventory->getItems();

        render_header('New Order', $user);
        ?>
        <form method="post" action="orders.php?action=new">
            <input type="hidden" name="customer_id" value="<?= e($customerId) ?>">
            <label>Order source
                <select name="order_source">
                    <option value="">None</option>
                    <?php foreach (OrderSource::cases() as $source): ?>
                        <option value="<?= e($source->value) ?>"><?= e($source->value) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php foreach ($items as $item): ?>
                <label><?= e($item->getName()) ?> (available: <?= $item->getAvailableQuantity() ?>)
                    <input type="number" min="0" name="quantities[<?= e($item->getItemId()) ?>]" value="0">
                </label>
            <?php endforeach; ?>
            <button type="submit">Create order</button>
        </form>
        <?php
        render_footer();
    }

    /**
     * SR-3.1 to SR-3.6: createOrder(), addOrderLine() per line, saveOrder(); show errors such as quantity
     * above the available quantity.
     */
    public function submitOrder(array $form): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $customer = $this->customers->getCustomerById($form['customer_id']);
            $sourceValue = (string) ($form['order_source'] ?? '');
            $source = $sourceValue === '' ? null : OrderSource::tryFrom($sourceValue);
            if ($sourceValue !== '' && $source === null) {
                throw new Exception('Choose a valid order source.');
            }

            $order = $this->orders->createOrder($customer, $source);
            foreach (($form['quantities'] ?? []) as $itemId => $rawQuantity) {
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
                if ($quantity === false || $quantity < 0) {
                    throw new Exception('Enter a valid quantity.');
                }
                if ($quantity > 0) {
                    $item = $this->inventory->getItemById((string) $itemId);
                    $this->orders->addOrderLine($order, $item, $quantity);
                }
            }

            $this->orders->saveOrder($order);
            redirect('orders.php');
        } catch (Exception $exception) {
            // Render the form again and show e($exception->getMessage()).
        }
    }

    /**
     * SR-3.7: OrderMgmt::cancelOrder().
     */
    public function submitCancel(string $orderId): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $order = $this->orders->getOrderById($orderId);
            $this->orders->cancelOrder($order);
            redirect('orders.php');
        } catch (Exception $exception) {
            // Render an error message safely with e().
        }
    }

    /**
     * SR-4.1: OrderMgmt::generateInvoice().
     */
    public function submitGenerateInvoice(string $orderId): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $order = $this->orders->getOrderById($orderId);
            $invoice = $this->orders->generateInvoice($order);
            redirect('orders.php');
        } catch (Exception $exception) {
            // Render an error message safely with e().
        }
    }

    /**
     * SR-4.6: OrderMgmt::confirmOrder().
     */
    public function submitConfirm(string $orderId): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        try {
            $order = $this->orders->getOrderById($orderId);
            $this->orders->confirmOrder($order);
            redirect('orders.php');
        } catch (Exception $exception) {
            // Render an error message safely with e().
        }
    }

    /**
     * SR-4.4: OrderMgmt::downloadInvoicePdf(), then send the file to the browser with a PDF content type.
     */
    public function downloadInvoice(string $orderId): void
    {
        $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]);

        $order = $this->orders->getOrderById($orderId);
        $invoice = $this->orders->getInvoiceByOrderId($orderId); // Add this method to OrderMgmt.
        $path = $this->orders->downloadInvoicePdf($invoice);

        if (!is_file($path)) {
            throw new Exception('Invoice PDF was not created.');
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice.pdf"');
        readfile($path);
        exit;
    }
}
