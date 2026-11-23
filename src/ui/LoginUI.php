<?php

/**
 * LoginUI: User Interface layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class LoginUI
{
    private UserPermission $permission;
    private LoginValidation $loginValidation;
    private TwoFactorAuth $twoFactor;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, LoginValidation, TwoFactorAuth.
     */
    public function __construct(UserPermission $permission, LoginValidation $loginValidation, TwoFactorAuth $twoFactor)
    {
        $this->permission = $permission;
        $this->loginValidation = $loginValidation;
        $this->twoFactor = $twoFactor;
    }

    /**
     * SC-2: username + password form that posts to login.php.
     */
    public function showLoginForm(): void
    {
        // TODO (P1): build this method. The note above says what it must do.

        render_header("Login");
        ?>  

        <h1>Sign In</h1>
        <form method="post" action="login.php">
            <label for="username">Username</label>
            <input id=username name=username required>

            <label for="password">Password</label>
            <input id=password name=password type="password" required>

            <p><button type="submit">Sign In</button></p>
        </form>
        <?php
        render_footer();

        //throw new Exception('LoginUI::showLoginForm() is not built yet.');
    }

    /**
     * SC-2: $this->loginValidation->validateCredentials(); if valid, remember the user as 'pending' in
     * $_SESSION and redirect to login.php?action=code.
     */
    public function submitCredentials(string $username, string $password): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginUI::submitCredentials() is not built yet.');
    }

    /**
     * SC-2: form for the 6-digit one-time code.
     */
    public function showCodeForm(): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginUI::showCodeForm() is not built yet.');
    }

    /**
     * SC-2: $this->twoFactor->verifyAppCode(); if valid, $this->permission->startSession($user) and
     * redirect to index.php.
     */
    public function submitCode(string $code): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginUI::submitCode() is not built yet.');
    }

    /**
     * $this->permission->endSession(), then redirect to login.php.
     */
    public function logout(): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('LoginUI::logout() is not built yet.');
    }
}
