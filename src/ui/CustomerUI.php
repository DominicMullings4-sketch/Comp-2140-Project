<?php

/**
 * CustomerUI: User Interface layer.
 * Feature owner: P3 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class CustomerUI
{
    private UserPermission $permission;
    private CustomerMgmt $customers;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, CustomerMgmt.
     */
    public function __construct(UserPermission $permission, CustomerMgmt $customers)
    {
        $this->permission = $permission;
        $this->customers = $customers;
    }

    /**
     * SR-2.6: search box plus the matching customers (CustomerMgmt::searchCustomers()).
     */
    public function showSearch(string $searchText): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::showSearch() is not built yet.');
    }

    /**
     * SR-2.7: the customer's details plus their past orders.
     */
    public function showCustomer(string $customerId): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::showCustomer() is not built yet.');
    }

    /**
     * SR-2.1 to SR-2.5: CustomerMgmt::createCustomer(); on a duplicate, show the existing record.
     */
    public function submitNewCustomer(array $form): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::submitNewCustomer() is not built yet.');
    }

    /**
     * SR-2.2: CustomerMgmt::editCustomer() with the form's values.
     */
    public function submitCustomerEdit(string $customerId, array $form): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::submitCustomerEdit() is not built yet.');
    }

    /**
     * SC-5: CustomerMgmt::anonymizeCustomer() after the user confirms.
     */
    public function submitAnonymize(string $customerId): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::submitAnonymize() is not built yet.');
    }

    /**
     * SR-2.3: show each empty field by name.
     * @param string[] $errors
     */
    private function showFieldErrors(array $errors): void
    {
        // TODO (P3): build this method. The note above says what it must do.
        throw new Exception('CustomerUI::showFieldErrors() is not built yet.');
    }
}
