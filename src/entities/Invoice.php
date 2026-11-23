<?php

/**
 * Invoice: Entity class (shared by all layers).
 * Feature owner: P4 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Invoice
{
    private string $invoiceNumber;
    private InvoiceStatus $status;
    private Order $order;

    /**
     * SR-4.2: every invoice gets a unique number. OrderMgmt checks SR-4.1 (Pending Confirmation, no
     * invoice yet) before creating one.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $this->status = InvoiceStatus::Active;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    /**
     * SR-4.7: set status to Void (when an invoiced order is cancelled).
     */
    public function markVoid(): void
    {
        // TODO (P4): build this method. The note above says what it must do.
        throw new Exception('Invoice::markVoid() is not built yet.');
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row, Order $order)
    {
        $invoice = new Invoice($order);
        $invoice->invoiceNumber = $row['invoice_number'];
        $invoice->status = InvoiceStatus::from($row['status']);
        return $invoice;
    }
}
