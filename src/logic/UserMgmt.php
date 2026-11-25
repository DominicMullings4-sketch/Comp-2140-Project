<?php

/**
 * UserMgmt: Application Logic layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class UserMgmt
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
     * Reject a username that already exists; INSERT new User(...) and return it. The password arrives
     * already hashed (LoginValidation::hashPassword).
     */
    public function createUser(string $name, string $username, string $email, string $passwordHash, UserRole $role): User
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('UserMgmt::createUser() is not built yet.');
    }

    /**
     * Every user, sorted by name.
     * @return User[]
     */
    public function getUsers(): array
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('UserMgmt::getUsers() is not built yet.');
    }

    /**
     * SELECT by user_id and return User::fromRow().
     */
    public function getUserById(string $userId): User
    {
        $rows = $this->db->select('SELECT * FROM users WHERE user_id = :id', ['id' => $userId]);
        if (count($rows) == 0) {
            throw new Exception('User not found.');
        }
        return User::fromRow($rows[0]);
    }

    /**
     * SELECT by username; return User::fromRow() or null when there is no such user.
     */
    public function findByUsername(string $username): ?User
    {
        $rows = $this->db->select('SELECT * FROM users WHERE username = :username', ['username' => $username]);
        if (count($rows) == 0) {
            return null;
        }
        return User::fromRow($rows[0]);
    }

    /**
     * SC-1: $user->setRole($role), then UPDATE.
     */
    public function changeRole(User $user, UserRole $role): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('UserMgmt::changeRole() is not built yet.');
    }

    /**
     * $user->setStatus(UserStatus::Inactive), then UPDATE. Inactive users can't sign in.
     */
    public function deactivateUser(User $user): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('UserMgmt::deactivateUser() is not built yet.');
    }

    /**
     * $user->setStatus(UserStatus::Active), then UPDATE.
     */
    public function reactivateUser(User $user): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('UserMgmt::reactivateUser() is not built yet.');
    }

    /**
     * SC-2: $user->setTwoFactorSecret($secret), then UPDATE (consider DataAccess::encrypt() for the
     * secret).
     */
    public function saveTwoFactorSecret(User $user, string $secret): void
    {
        // Ported from UserService::saveTwoFactorSecret() (based-on-old-srs). The secret is stored as it is,
        // like before; DataAccess::encrypt() can be added later if the team wants it.
        $user->setTwoFactorSecret($secret);
        $this->db->execute('UPDATE users SET two_factor_secret = :secret WHERE user_id = :id',
            ['secret' => $secret, 'id' => $user->getId()]);
    }
}
