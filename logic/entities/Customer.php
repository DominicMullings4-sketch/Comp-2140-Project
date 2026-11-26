<?php

/**
 * Customer: entity class. A customer record (Feature 3).
 * Customers do not sign in; staff records them.
 */
class Customer
{
    public ?int $id = null;        // null until the customer is saved
    public string $name;
    public string $deliveryAddress;
    public string $phone;
    public string $email;

    /** Builds a Customer object from a row of the customers table. */
    public static function fromRow(array $row): Customer
    {
        $customer = new Customer();
        $customer->id = (int) $row['customer_id'];
        $customer->name = $row['name'];
        $customer->deliveryAddress = $row['delivery_address'];
        $customer->phone = $row['phone'];
        $customer->email = $row['email'];
        return $customer;
    }
}
