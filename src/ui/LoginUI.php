<?php

/**
 * LoginUI: User Interface layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 * Ported from LoginUI on the based-on-old-srs branch.
 *
 * login.php                -> showLoginForm()  / submitCredentials()
 * login.php?action=code    -> showCodeForm()   / submitCode()      (only when USE_TWO_FACTOR is true)
 * login.php?action=logout  -> logout()
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
     * $error is only passed in by submitCredentials() when the sign-in failed.
     */
    public function showLoginForm(?string $error = null): void
    {
        $this->goHomeIfSignedIn();

        render_header('Sign in');
        ?>
        <section class="card narrow">
            <h1>Sign in</h1>
            <?php if ($error != null): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="login.php">
                <label for="username">Username</label>
                <input id="username" name="username" autocomplete="username" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>

                <button type="submit">Sign in</button>
            </form>
        </section>
        <?php
        render_footer();
    }

    /**
     * SC-2: $this->loginValidation->validateCredentials(); if valid, remember the user as 'pending' in
     * $_SESSION and redirect to login.php?action=code.
     */
    public function submitCredentials(string $username, string $password): void
    {
        $user = $this->loginValidation->validateCredentials(trim($username), $password);

        if ($user == null) {
            $this->showLoginForm('Incorrect username or password.');
            return;
        }

        // Two-factor switched off in config.php (while building): sign in straight away.
        if (!USE_TWO_FACTOR) {
            $this->permission->startSession($user);
            redirect('index.php');
        }

        // Between the two steps the user is "pending": they passed the password step but aren't signed in yet.
        $_SESSION['pending_user'] = $user;
        redirect('login.php?action=code');
    }

    /**
     * SC-2: form for the 6-digit one-time code.
     * On the user's first sign-in (no secret saved yet) it also shows the setup key for their authenticator app.
     */
    public function showCodeForm(?string $error = null): void
    {
        $user = $this->getPendingUser();

        // First sign-in: the user has no secret yet, so make one for them to add to their app.
        $needsSetup = ($user->getTwoFactorSecret() == null);
        if ($needsSetup && !isset($_SESSION['setup_secret'])) {
            $_SESSION['setup_secret'] = $this->twoFactor->generateSecret();
        }

        render_header('Verification code');
        ?>
        <section class="card narrow">
            <h1>Enter verification code</h1>
            <?php if ($error != null): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <?php if ($needsSetup): ?>
                <p><strong>First sign-in:</strong> add Track-It to your authenticator app.
                   Choose "Enter a setup key" and type this key:</p>
                <p><code><?= e($_SESSION['setup_secret']) ?></code></p>
            <?php endif; ?>

            <p>Open your authenticator app and type the 6-digit code.</p>
            <form method="post" action="login.php?action=code">
                <label for="code">Code</label>
                <input id="code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus>
                <button type="submit">Verify</button>
            </form>
            <p><a href="login.php?action=logout">Start again</a></p>
        </section>
        <?php
        render_footer();
    }

    /**
     * SC-2: $this->twoFactor->verifyAppCode(); if valid, $this->permission->startSession($user) and
     * redirect to index.php.
     */
    public function submitCode(string $code): void
    {
        $user = $this->getPendingUser();
        $isSetup = ($user->getTwoFactorSecret() == null);

        // On the first sign-in, check the code against the NEW secret. A copy (clone) of the user is used,
        // so the pending user doesn't get the secret until the code is proved right.
        $userToCheck = $user;
        if ($isSetup) {
            $userToCheck = clone $user;
            $userToCheck->setTwoFactorSecret($_SESSION['setup_secret'] ?? '');
        }

        if (!$this->twoFactor->verifyAppCode($userToCheck, trim($code))) {
            $this->showCodeForm('That code is incorrect. Check your app and try again.');
            return;
        }

        if ($isSetup) {
            $this->twoFactor->saveSecret($user, $_SESSION['setup_secret']);   // the secret works, so keep it
        }

        unset($_SESSION['pending_user'], $_SESSION['setup_secret']);
        $this->permission->startSession($user);
        redirect('index.php');
    }

    /**
     * $this->permission->endSession(), then redirect to login.php.
     */
    public function logout(): void
    {
        $this->permission->endSession();
        redirect('login.php');
    }

    /**
     * Who passed the password step? If nobody did, start again at the sign-in form.
     * Helper; not on the class diagram.
     */
    private function getPendingUser(): User
    {
        if (!isset($_SESSION['pending_user'])) {
            redirect('login.php');
        }
        return $_SESSION['pending_user'];
    }

    /**
     * Already signed in? Skip the sign-in page and go to your home page.
     * Helper; not on the class diagram.
     */
    private function goHomeIfSignedIn(): void
    {
        if ($this->permission->getCurrentUser() != null) {
            redirect('index.php');
        }
    }
}
