<?php

/**
 * User: Entity class (shared by all layers).
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class User
{
    private string $id;
    private string $name;
    private string $username;
    private string $email;
    private string $passwordHash;
    private ?string $twoFactorSecret;
    private UserRole $role;
    private UserStatus $status;

    public function __construct(string $name, string $username, string $email, string $passwordHash, UserRole $role)
    {
        $this->id = new_id('U');
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->twoFactorSecret = null;
        $this->role = $role;
        $this->status = UserStatus::Active;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function setStatus(UserStatus $status): void
    {
        $this->status = $status;
    }

    public function setTwoFactorSecret(string $secret): void
    {
        $this->twoFactorSecret = $secret;
    }

    /**
     * Rebuilds a record that is already saved in the database.
     * It makes the object with the constructor, then puts back the saved values
     * (the constructor would give it a brand-new ID). Helper for the Mgmt classes; not on the class diagram.
     */
    public static function fromRow(array $row)
    {
        $user = new User($row['name'], $row['username'], $row['email'], $row['password_hash'], UserRole::from($row['role']));
        $user->id = $row['user_id'];
        $user->twoFactorSecret = $row['two_factor_secret'];
        $user->status = UserStatus::from($row['status']);
        return $user;
    }
}
