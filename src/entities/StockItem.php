<?php

/** StockItem: entity for SRS Feature 5 (SR-3.1 to SR-3.5). */
class StockItem
{
    private string $stockItemId;
    private string $name;
    private ?string $description;
    private int $quantity;
    private float $rate;
    private bool $takenForDelivery = false;
    private string $loggedAt;
    private bool $flagged = false;

    public function __construct(string $name, ?string $description, int $quantity, float $rate)
    {
        $this->stockItemId = new_id('S');
        $this->name = $name;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->rate = $rate;
        $this->loggedAt = now();   // AC-3.3: the system sets this, never the caller
    }

    public function getStockItemId(): string { return $this->stockItemId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getQuantity(): int { return $this->quantity; }
    public function getRate(): float { return $this->rate; }
    public function getTotalValue(): float { return round($this->quantity * $this->rate, 2); }
    public function isTakenForDelivery(): bool { return $this->takenForDelivery; }
    public function getLoggedAt(): string { return $this->loggedAt; }
    public function isFlagged(): bool { return $this->flagged; }

    public static function fromRow(array $row)
    {
        $item = new StockItem($row['item_name'], $row['description'], (int) $row['quantity'], (float) $row['rate']);
        $item->stockItemId = $row['stock_item_id'];
        $item->takenForDelivery = (bool) $row['taken_for_delivery'];
        $item->loggedAt = $row['logged_at'];
        $item->flagged = (bool) $row['is_flagged'];
        return $item;
    }
}