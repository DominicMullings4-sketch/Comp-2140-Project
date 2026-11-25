<?php

/**
 * CustomerService: Application Logic layer. Business rules AND SQL for Feature 3 (Storing Customer Information).
 * WORKED EXAMPLE: fully built. Copy this style for your own service.
 *
 * The pattern: the page hands over what the user typed, the service checks the rules,
 * and only if everything passes does it save (through DataAccess).
 * Methods that save return a list of error messages: an EMPTY list means it worked.
 */
class CustomerService
{
    private DataAccess $db;

    public function __construct()
    {
        $this->db = new DataAccess();
    }

    /** 3.3: every customer, for the list. @return Customer[] */
    public function getAllCustomers(): array
    {
        $rows = $this->db->select('SELECT * FROM customers ORDER BY name', []);

        $customers = [];
        foreach ($rows as $row) {
            $customers[] = Customer::fromRow($row);   // each row becomes a Customer object
        }
        return $customers;
    }

    public function getCustomer(int $id): ?Customer
    {
        $row = $this->db->selectOne('SELECT * FROM customers WHERE id = :id', ['id' => $id]);
        return $row === null ? null : Customer::fromRow($row);
    }

    /**
     * 3.1, 3.2: saves a new customer if the details pass every rule.
     * @return string[] error messages (empty = saved)
     */
    public function addCustomer(string $name, string $address, string $phone, string $email): array
    {
        $errors = $this->checkDetails($name, $address, $phone, $email, 0);
        if (count($errors) > 0) {
            return $errors;
        }

        $this->db->execute(
            'INSERT INTO customers (name, delivery_address, phone, email)
             VALUES (:name, :address, :phone, :email)',
            ['name' => $name, 'address' => $address, 'phone' => $phone, 'email' => $email]
        );
        return [];
    }

    /**
     * 3.3: saves changes to a customer. The duplicate check (3.2) applies here too,
     * otherwise editing would be a way around it.
     * @return string[] error messages (empty = saved)
     */
    public function updateCustomer(int $id, string $name, string $address, string $phone, string $email): array
    {
        if ($this->getCustomer($id) === null) {
            return ['That customer no longer exists.'];
        }

        $errors = $this->checkDetails($name, $address, $phone, $email, $id);
        if (count($errors) > 0) {
            return $errors;
        }

        $this->db->execute(
            'UPDATE customers
             SET name = :name, delivery_address = :address, phone = :phone, email = :email
             WHERE id = :id',
            ['name' => $name, 'address' => $address, 'phone' => $phone, 'email' => $email, 'id' => $id]
        );
        return [];
    }

    /**
     * The rules for customer details. $excludeId is the customer being edited (0 when adding),
     * so a customer doesn't count as a duplicate of themselves.
     * @return string[] error messages
     */
    private function checkDetails(string $name, string $address, string $phone, string $email, int $excludeId): array
    {
        $errors = [];

        // Every field is required (3.1)
        if ($name === '') {
            $errors[] = 'Name is required.';
        }
        if ($address === '') {
            $errors[] = 'Delivery address is required.';
        }
        if ($phone === '') {
            $errors[] = 'Phone number is required.';
        }
        if ($email === '') {
            $errors[] = 'Email address is required.';
        }

        // Format checks
        if ($phone !== '' && preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) !== 1) {
            $errors[] = 'Phone number may only contain digits, spaces, +, - and brackets.';
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Email address is not valid.';
        }

        // No duplicates (3.2). Only checked once the format is right.
        if (count($errors) === 0) {
            $samePhone = $this->db->selectOne(
                'SELECT id FROM customers WHERE phone = :phone AND id <> :exclude',
                ['phone' => $phone, 'exclude' => $excludeId]
            );
            if ($samePhone !== null) {
                $errors[] = 'Another customer already has this phone number.';
            }

            $sameEmail = $this->db->selectOne(
                'SELECT id FROM customers WHERE email = :email AND id <> :exclude',
                ['email' => $email, 'exclude' => $excludeId]
            );
            if ($sameEmail !== null) {
                $errors[] = 'Another customer already has this email address.';
            }
        }

        return $errors;
    }
}
