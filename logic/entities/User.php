<?php

/**
 * User: entity class. A pre-loaded account that can sign in.
 * Bearers are users whose role is ROLE_BEARER.
 */
class User
{
    // The two roles (stored in users.role). Use these instead of typing 'OfficeStaff' or 'DeliveryWorker'.
    public const ROLE_STAFF = 'OfficeStaff';
    public const ROLE_BEARER = 'DeliveryWorker';

    public int $id;
    public string $name;
    public string $username;
    public string $passwordHash;
    public ?string $twoFactorSecret;
    public string $role;

    /** Builds a User from a row of the users table. */
    public static function fromRow(array $row): User
    {
        $user = new User();
        $user->id = (int) $row['user_id'];
        $user->name = $row['name'];
        $user->username = $row['username'];
        $user->passwordHash = $row['password_hash'];
        $user->twoFactorSecret = $row['two_factor_secret'];
        $user->role = $row['role'];
        return $user;
    }
}
