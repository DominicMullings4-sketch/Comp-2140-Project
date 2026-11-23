<?php

/**
 * Supplier: Entity class (shared by all layers).
 * Feature owner: P2 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Supplier
{
    private string $supplierId;
    private string $name;
    private string $city;
    private string $country;
    private ?string $contactName;
    private ?string $contactPhone;

    public function __construct(string $name, string $city, string $country, ?string $contactName = null, ?string $contactPhone = null)
    {
        $this->supplierId = new_id('S');
        $this->name = $name;
        $this->city = $city;
        $this->country = $country;
        $this->contactName = $contactName;
        $this->contactPhone = $contactPhone;
    }

    public function getSupplierId(): string
    {
        return $this->supplierId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row)
    {
        $supplier = new Supplier($row['name'], $row['city'], $row['country'], $row['contact_name'], $row['contact_phone']);
        $supplier->supplierId = $row['supplier_id'];
        return $supplier;
    }
}
