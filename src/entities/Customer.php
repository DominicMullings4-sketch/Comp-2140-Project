<?php

/**
 * Customer: Entity class (shared by all layers).
 * Feature owner: P3 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Customer
{
    private string $customerId;
    private string $name;
    private string $phone;
    private string $address;
    private ?string $socialMediaHandle;

    public function __construct(string $name, string $phone, string $address, ?string $socialMediaHandle = null)
    {
        $this->customerId = new_id('C');
        $this->name = $name;
        $this->phone = $phone;
        $this->address = $address;
        $this->socialMediaHandle = $socialMediaHandle;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getSocialMediaHandle(): ?string
    {
        return $this->socialMediaHandle;
    }

    /**
     * SR-2.7. CustomerMgmt::getOrderHistory() already loads a customer's orders from the database.
     * Simplest option: have CustomerMgmt pass the orders in and return them here, or ask Tevin to remove
     * this method from the diagram.
     * @return Order[]
     */
    public function getOrderHistory(): array
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('Customer::getOrderHistory() is not built yet.');
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setSocialMediaHandle(?string $handle = null): void
    {
        $this->socialMediaHandle = $handle;
    }

    /**
     * SC-5: replace name, phone, address and social media handle with placeholder values (for example
     * 'Anonymized customer'), so past orders stay but the person can't be identified. CustomerMgmt checks
     * there are no outstanding deliveries first.
     */
    public function anonymize(): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('Customer::anonymize() is not built yet.');
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row)
    {
        $customer = new Customer($row['name'], $row['phone'], $row['address'], $row['social_media_handle']);
        $customer->customerId = $row['customer_id'];
        return $customer;
    }
}
