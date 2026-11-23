<?php

/**
 * CustomerMgmt: Application Logic layer.
 * Feature owner: P3 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class CustomerMgmt
{
    private DataAccess $db;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess.
     */
    public function __construct(DataAccess $db)
    {
        $this->db = $db;
    }

    /**
     * SR-2.1, SR-2.3, SR-2.4, SR-2.5: reject empty name/phone/address (say which), a phone that isn't
     * exactly 10 digits, and a phone or handle that already belongs to another customer; otherwise INSERT
     * new Customer(...) and return it.
     */
    public function createCustomer(string $name, string $address, string $phone, ?string $socialMedia = null): Customer
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::createCustomer() is not built yet.');
    }

    /**
     * SR-2.2 to SR-2.5: same checks as createCustomer (ignoring this customer's own phone/handle), then
     * the setters, then UPDATE.
     */
    public function editCustomer(Customer $customer, string $name, string $address, string $phone, ?string $socialMedia = null): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::editCustomer() is not built yet.');
    }

    /**
     * SR-2.6: SELECT ... WHERE name LIKE :search OR phone LIKE :search OR social_media_handle LIKE
     * :search, with ['search' => '%' . $searchText . '%'] (the same LIKE search as INFO2180 Lab 5).
     * @return Customer[]
     */
    public function searchCustomers(string $searchText): array
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::searchCustomers() is not built yet.');
    }

    /**
     * SELECT the customer by customer_id and return Customer::fromRow().
     */
    public function getCustomerById(string $customerId): Customer
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::getCustomerById() is not built yet.');
    }

    /**
     * SR-2.7: the customer's orders, newest first (OrderMgmt::getOrderById() shows how to rebuild a full
     * order).
     * @return Order[]
     */
    public function getOrderHistory(Customer $customer): array
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::getOrderHistory() is not built yet.');
    }

    /**
     * SC-5: refuse while the customer has a Pending delivery; otherwise $customer->anonymize() and UPDATE.
     */
    public function anonymizeCustomer(Customer $customer): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerMgmt::anonymizeCustomer() is not built yet.');
    }
}
