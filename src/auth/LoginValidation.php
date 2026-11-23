<?php

/**
 * LoginValidation: Authentication & Authorization layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class LoginValidation
{
    private UserMgmt $users;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserMgmt.
     */
    public function __construct(UserMgmt $users)
    {
        $this->users = $users;
    }

    /**
     * SC-2: find the user by username; return it only if they're Active and password_verify($password,
     * $user->getPasswordHash()) is true, otherwise null.
     */
    public function validateCredentials(string $username, string $password): ?User
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginValidation::validateCredentials() is not built yet.');
    }

    /**
     * SC-3: at least 10 characters with an uppercase letter, a lowercase letter, a digit and a symbol.
     */
    public function meetsPasswordRules(string $password): bool
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginValidation::meetsPasswordRules() is not built yet.');
    }

    /**
     * SC-3: return password_hash($password, PASSWORD_DEFAULT). Never store the plain password.
     */
    public function hashPassword(string $password): string
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginValidation::hashPassword() is not built yet.');
    }
}
