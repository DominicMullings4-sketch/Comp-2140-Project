<?php

/**
 * UserPermission: Authentication & Authorization layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class UserPermission
{
    public function startSession(User $user): void
    {
        session_regenerate_id(true);   // a fresh session ID after signing in
        $_SESSION['user'] = $user;
    }

    public function endSession(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function getCurrentUser(): ?User
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        return null;
    }

    /**
     * SC-2: anyone not signed in is sent to the sign-in page.
     */
    public function requireLogin(): User
    {
        $user = $this->getCurrentUser();
        if ($user == null) {
            redirect('login.php');   // SC-2: not signed in, so go to the sign-in page
        }
        return $user;
    }

    /**
     * SC-1: call this first in every page method, with the roles allowed on that page, for example
     * $this->permission->requireRole([UserRole::BusinessOwner, UserRole::OfficeStaff]). Anyone else sees
     * 'Access denied'.
     * @param UserRole[] $allowedRoles
     */
    public function requireRole(array $allowedRoles): User
    {
        $user = $this->requireLogin();

        if (!in_array($user->getRole(), $allowedRoles)) {
            render_header('Access denied', $user);
            echo '<h1>Access denied</h1>';
            echo '<p>Your role does not have access to this page.</p>';
            render_footer();
            exit;   // stop here, so nothing else on the page runs
        }
        return $user;
    }
}
