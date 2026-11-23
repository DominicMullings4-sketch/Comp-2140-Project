<?php

/**
 * UserUI: User Interface layer.
 * Feature owner: P6 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class UserUI
{
    private UserPermission $permission;
    private LoginValidation $loginValidation;
    private UserMgmt $users;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, LoginValidation, UserMgmt.
     */
    public function __construct(UserPermission $permission, LoginValidation $loginValidation, UserMgmt $users)
    {
        $this->permission = $permission;
        $this->loginValidation = $loginValidation;
        $this->users = $users;
    }

    /**
     * Manage Users (owner only, SC-1): list users with role and status, plus the new-user form.
     */
    public function showUsers(): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('UserUI::showUsers() is not built yet.');
    }

    /**
     * Check LoginValidation::meetsPasswordRules(), hash with hashPassword(), then UserMgmt::createUser().
     */
    public function submitNewUser(array $form): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('UserUI::submitNewUser() is not built yet.');
    }

    /**
     * UserMgmt::changeRole(), then redirect back to users.php.
     */
    public function submitRoleChange(string $userId, UserRole $role): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('UserUI::submitRoleChange() is not built yet.');
    }

    /**
     * UserMgmt::deactivateUser(), then redirect back to users.php.
     */
    public function submitDeactivate(string $userId): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('UserUI::submitDeactivate() is not built yet.');
    }

    /**
     * UserMgmt::reactivateUser(), then redirect back to users.php.
     */
    public function submitReactivate(string $userId): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('UserUI::submitReactivate() is not built yet.');
    }
}
