<?php

/**
 * UserService: Application Logic layer. Looks up the pre-loaded user accounts.
 * Already built (foundation). There is no add-user feature: accounts are loaded
 * straight into the database (see database/testdata.sql).
 */
class UserService
{
    private DataAccess $db;

    public function __construct()
    {
        $this->db = new DataAccess();
    }

    public function findByUsername(string $username): ?User
    {
        $rows = $this->db->select('SELECT * FROM users WHERE username = :username', ['username' => $username]);
        if (count($rows) === 0) {
            return null;   // no user with that username
        }
        return User::fromRow($rows[0]);   // the first (and only) row
    }

    public function getUser(int $id): ?User
    {
        $rows = $this->db->select('SELECT * FROM users WHERE user_id = :id', ['id' => $id]);
        if (count($rows) === 0) {
            return null;   // no user with that id
        }
        return User::fromRow($rows[0]);   // the first (and only) row
    }

    /** @return User[] every bearer, for the "assign a bearer" dropdown (5.5) */
    public function getBearers(): array
    {
        $rows = $this->db->select('SELECT * FROM users WHERE role = :role ORDER BY username', ['role' => User::ROLE_BEARER]);

        $bearers = [];
        foreach ($rows as $row) {
            $bearers[] = User::fromRow($row);
        }
        return $bearers;
    }

    public function saveTwoFactorSecret(User $user, string $secret): void
    {
        $this->db->execute('UPDATE users SET two_factor_secret = :secret WHERE user_id = :id', ['secret' => $secret, 'id' => $user->id]);
        $user->twoFactorSecret = $secret;
    }
}
