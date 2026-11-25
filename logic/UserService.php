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
        $row = $this->db->selectOne('SELECT * FROM users WHERE username = :username', ['username' => $username]);
        return $row === null ? null : User::fromRow($row);
    }

    public function getUser(int $id): ?User
    {
        $row = $this->db->selectOne('SELECT * FROM users WHERE user_id = :id', ['id' => $id]);
        return $row === null ? null : User::fromRow($row);
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
