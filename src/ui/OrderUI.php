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
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::showOrders() is not built yet.');
    }

    /**
     * SR-3.1, SR-3.2: items with their available quantity, quantity boxes and an optional order source.
     */
    public function showNewOrderForm(string $customerId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::showNewOrderForm() is not built yet.');
    }

    /**
     * SR-3.1 to SR-3.6: createOrder(), addOrderLine() per line, saveOrder(); show errors such as quantity
     * above the available quantity.
     */
    public function submitOrder(array $form): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitOrder() is not built yet.');
    }

    /**
     * SR-3.7: OrderMgmt::cancelOrder().
     */
    public function submitCancel(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitCancel() is not built yet.');
    }

    /**
     * SR-4.1: OrderMgmt::generateInvoice().
     */
    public function submitGenerateInvoice(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitGenerateInvoice() is not built yet.');
    }

    /**
     * SR-4.6: OrderMgmt::confirmOrder().
     */
    public function submitConfirm(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::submitConfirm() is not built yet.');
    }

    /**
     * SR-4.4: OrderMgmt::downloadInvoicePdf(), then send the file to the browser with a PDF content type.
     */
    public function downloadInvoice(string $orderId): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderUI::downloadInvoice() is not built yet.');
    }
}
