<?php

/**
 * OrderMgmt: Application Logic layer.
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class OrderMgmt
{
    private DataAccess $db;
    private InventoryMgmt $inventory;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess, InventoryMgmt.
     */
    public function __construct(DataAccess $db, InventoryMgmt $inventory)
    {
        $this->db = $db;
        $this->inventory = $inventory;
    }

    /**
     * SR-3.1: return new Order($customer, $orderSource). Nothing is saved until saveOrder().
     */
    public function createOrder(Customer $customer, ?OrderSource $orderSource = null): Order
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::createOrder() is not built yet.');
    }

    /**
     * SR-3.3: $order->addLine($item, $quantity); the Order checks the quantity.
     */
    public function addOrderLine(Order $order, Item $item, int $quantity): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::addOrderLine() is not built yet.');
    }

    /**
     * SR-3.5, SR-3.6, SF-2: in ONE transaction: INSERT the order, INSERT each line, then
     * $this->inventory->reserveStock($order). Roll back if anything fails.
     */
    public function saveOrder(Order $order): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::saveOrder() is not built yet.');
    }

    /**
     * Every order, newest first, rebuilt with Order::fromRow() (load its customer and lines too).
     * @return Order[]
     */
    public function getOrders(): array
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::getOrders() is not built yet.');
    }

    /**
     * Rebuild the full order: Customer::fromRow(), each OrderLine::fromRow() with its Item,
     * Order::fromRow(), then each Delivery::fromRow() (oldest first, ORDER BY assigned_at), which links
     * itself to the order.
     */
    public function getOrderById(string $orderId): Order
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::getOrderById() is not built yet.');
    }

    /**
     * SR-3.7, SR-3.8, SR-4.7, SF-2: in one transaction: $order->cancelOrder(), UPDATE it,
     * $this->inventory->releaseStock($order), and mark its invoice Void if it has one.
     */
    public function cancelOrder(Order $order): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::cancelOrder() is not built yet.');
    }

    /**
     * SR-4.1, SR-4.2: only for a Pending Confirmation order with no invoice yet. INSERT new
     * Invoice($order) and return it.
     */
    public function generateInvoice(Order $order): Invoice
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::generateInvoice() is not built yet.');
    }

    /**
     * SR-4.6: only when the order has an invoice. $order->confirmOrder(), then UPDATE its order_status.
     */
    public function confirmOrder(Order $order): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::confirmOrder() is not built yet.');
    }

    /**
     * SR-4.3, SR-4.4: build the invoice PDF (a PDF library will be added to the project) and return the
     * file's path.
     * @return string  path of the generated file
     */
    public function downloadInvoicePdf(Invoice $invoice): string
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('OrderMgmt::downloadInvoicePdf() is not built yet.');
    }
}
