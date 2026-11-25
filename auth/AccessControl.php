<?php

/**
 * AccessControl: Authentication & Authorization layer. The single gate every page goes through.
 * Already built (foundation).
 *
 * The first line of logic on EVERY page in ui/ (except login) is one of:
 *     AccessControl::requireRole(User::ROLE_STAFF);
 *     AccessControl::requireRole(User::ROLE_BEARER);
 * Not signed in -> sent to the sign-in page. Wrong role -> sent to the "Access denied" page.
 */
class AccessControl
{
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** The signed-in user's id (null if nobody is signed in). */
    public static function currentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function currentUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    /** User::ROLE_STAFF, User::ROLE_BEARER, or null if nobody is signed in. */
    public static function currentRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            redirect('login.php');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (self::currentRole() !== $role) {
            redirect('access_denied.php');
        }
    }

    /** The page each role starts on after signing in. */
    public static function homePageFor(string $role): string
    {
        if ($role === User::ROLE_BEARER) {
            return 'my_deliveries.php';
        }
        return 'orders.php';
    }
}
