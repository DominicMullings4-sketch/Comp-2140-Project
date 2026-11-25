<?php

class DeliveryNote{

    private string $noteId;
    private string $noteNumber;
    private string $deliveryAddress;
    private string $specialInstructions;
    private string $issueDate;
    private array $lineItems

    public function __construct(string $noteId, string $noteNumber, string $deliveryAddress, string $specialInstructions, string $issueDate, array $lineItems){
        $this->noteId = $noteId;
        $this->noteNumber = $noteNumber;
        $this->deliveryAddress = $deliveryAddress;
        $this->specialInstructions = $specialInstructions;
        $this->issueDate = $issueDate;
        $this->lineItems = $lineItems;
    }

    public function printNote(): void{
        echo '<article class="delivery-note">';
        echo '<h1>Delivery Note</h1>';
        echo '<p>Note number: ' . e($this->noteNumber) . '</p>';
        echo '<p>Issue date: ' . e($this->issueDate) . '</p>';
        echo '<p>Delivery address: ' . nl2br(e($this->deliveryAddress)) . '</p>';
        echo '<p>Special instructions: ' . nl2br(e($this->specialInstructions)) . '</p>';

        echo '<table>';
        echo '<thead><tr><th>Item</th><th>Quantity</th></tr></thead>';
        echo '<tbody>';

        foreach ($this->lineItems as $line) {
            echo '<tr>';
            echo '<td>' . e($line->getItem()->getName()) . '</td>';
            echo '<td>' . $line->getQuantityOrdered() . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</article>';
    }

    public function getLineItems(): array {
        return $this->lineItems;
    }
}