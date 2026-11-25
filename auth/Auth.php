<?php

/**
 * Auth: Authentication & Authorization layer. Signing in, two-factor codes and signing out.
 * Already built (foundation), from the team's original login code.
 *
 * Sign-in has two steps:
 *   1. username + password       -> checkPassword()
 *   2. 6-digit authenticator code -> verifyCode()   (only when USE_TWO_FACTOR is true in config.php)
 * Between the steps, the user is "pending": they passed step 1 but aren't signed in yet.
 */
class Auth
{
    private UserService $users;

    public function __construct()
    {
        $this->users = new UserService();
    }

    /** Step 1: returns the user if the username and password are right, otherwise null. */
    public function checkPassword(string $username, string $password): ?User
    {
        $user = $this->users->findByUsername($username);
        if ($user === null) {
            return null;
        }
        // password_verify() checks the typed password against the scrambled one in the database.
        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }
        return $user;
    }

    /** Remembers who passed step 1, while they type their code. */
    public function setPendingUser(User $user): void
    {
        $_SESSION['pending_user_id'] = $user->id;
    }

    /** Who passed step 1? Null means nobody (they skipped the password step). */
    public function getPendingUser(): ?User
    {
        if (!isset($_SESSION['pending_user_id'])) {
            return null;
        }
        return $this->users->getUser($_SESSION['pending_user_id']);
    }

    /** First sign-in with 2FA: a new secret for the user to add to their authenticator app. */
    public function newSecret(): string
    {
        return totp_random_secret();
    }

    /** Step 2: is this the right 6-digit code for the secret? Also accepts the previous 30-second code. */
    public function verifyCode(string $secret, string $code): bool
    {
        if (hash_equals(totp_code($secret), $code)) {
            return true;
        }
        if (hash_equals(totp_code($secret, time() - 30), $code)) {
            return true;
        }
        return false;
    }

    /** First sign-in with 2FA: saves the secret once the user has proved it works. */
    public function saveSecret(User $user, string $secret): void
    {
        $this->users->saveTwoFactorSecret($user, $secret);
    }

    /** Signs the user in: the session now remembers who they are and their role. */
    public function startSession(User $user): void
    {
        session_regenerate_id(true);   // a fresh session ID after signing in (stops session hijacking)
        unset($_SESSION['pending_user_id'], $_SESSION['setup_secret']);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
