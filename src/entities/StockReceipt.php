<?php

/**
 * StockReceipt: Entity class (shared by all layers).
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class StockReceipt
{
    private string $receiptID;
    private string $dateReceived;   // date as Y-m-d
    private string $enteredAt;   // date and time as Y-m-d H:i:s
    private Supplier $supplier;
    private User $enteredBy;
    private array $lines;   // StockReceiptLine[]

    /**
     * @param string $dateReceived  date as Y-m-d
     */
    public function __construct(Supplier $supplier, string $dateReceived, User $enteredBy)
    {
        $this->receiptID = new_id('SR');
        $this->supplier = $supplier;
        $this->dateReceived = $dateReceived;
        $this->enteredBy = $enteredBy;
        $this->enteredAt = now();
        $this->lines = [];
    }

    /**
     * @return StockReceiptLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * SR-1.1, SR-1.3, SR-1.10: quantity must be a whole number above 0 and unit cost 0 or more; otherwise
     * throw new Exception('...') with a message for the user, then add new StockReceiptLine($item,
     * $quantity, $unitCost) to $this->lines.
     */
    public function addLine(Item $item, int $quantity, float $unitCost): void
    {
        // Same rules as StockReceiptService::recordReceipt() on based-on-old-srs (1.3 and unit cost).
        if ($quantity < 1) {
            throw new Exception('Quantity must be a whole number, 1 or more.');
        }
        if ($unitCost < 0) {
            throw new Exception('Unit cost must be a number, 0 or more.');
        }
        $this->lines[] = new StockReceiptLine($item, $quantity, $unitCost);
    }

    public function getEnteredBy(): User
    {
        return $this->enteredBy;
    }

    public function getReceiptId(): string
    {
        return $this->receiptID;
    }

    /**
     * @return string  date as Y-m-d
     */
    public function getDateReceived(): string
    {
        return $this->dateReceived;
    }

    /**
     * @return string  date and time as Y-m-d H:i:s
     */
    public function getEnteredAt(): string
    {
        return $this->enteredAt;
    }

    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     * @param StockReceiptLine[] $lines
     */
    public static function fromRow(array $row, Supplier $supplier, User $enteredBy, array $lines)
    {
        $receipt = new StockReceipt($supplier, $row['date_received'], $enteredBy);
        $receipt->receiptID = $row['receipt_id'];
        $receipt->enteredAt = $row['entered_at'];
        $receipt->lines = $lines;
        return $receipt;
    }
}
