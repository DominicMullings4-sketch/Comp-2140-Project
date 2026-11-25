<?php

/**
 * LoginUI: User Interface layer. The sign-in screens.
 * Already built (foundation), from the team's original LoginUI.
 *
 * login.php                -> showLoginForm()  / submitCredentials()
 * login.php?action=code    -> showCodeForm()   / submitCode()      (only when USE_TWO_FACTOR is true)
 * login.php?action=logout  -> logout()
 */
class LoginUI
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /** Step 1: username + password form. */
    public function showLoginForm(?string $error = null): void
    {
        $this->goHomeIfSignedIn();

        render_header('Sign in');
        ?>
        <section class="card narrow">
            <h1>Sign in</h1>
            <?php if ($error !== null): ?>
                <p class="flash error"><?= h($error) ?></p>
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

    /** Step 1 submitted: check the password, then sign in (or go to the code step). */
    public function submitCredentials(string $username, string $password): void
    {
        $user = $this->auth->checkPassword(trim($username), $password);

        if ($user === null) {
            $this->showLoginForm('Incorrect username or password.');
            return;
        }

        if (!USE_TWO_FACTOR) {
            $this->auth->startSession($user);
            redirect(AccessControl::homePageFor($user->role));
        }

        $this->auth->setPendingUser($user);
        redirect('login.php?action=code');
    }

    /** Step 2: the 6-digit code form. First sign-in also shows the setup key. */
    public function showCodeForm(?string $error = null): void
    {
        $user = $this->auth->getPendingUser();
        if ($user === null) {
            redirect('login.php');   // nobody passed step 1, so start again
        }

        // First sign-in: the user has no secret yet, so make one for them to add to their app.
        $needsSetup = ($user->twoFactorSecret === null);
        if ($needsSetup && !isset($_SESSION['setup_secret'])) {
            $_SESSION['setup_secret'] = $this->auth->newSecret();
        }

        render_header('Verification code');
        ?>
        <section class="card narrow">
            <h1>Enter verification code</h1>
            <?php if ($error !== null): ?>
                <p class="flash error"><?= h($error) ?></p>
            <?php endif; ?>

            <?php if ($needsSetup): ?>
                <p><strong>First sign-in:</strong> add Track-It to your authenticator app.
                   Choose "Enter a setup key" and type this key:</p>
                <p><code><?= h($_SESSION['setup_secret']) ?></code></p>
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

    /** Step 2 submitted: check the code, save the secret on first sign-in, then sign in. */
    public function submitCode(string $code): void
    {
        $user = $this->auth->getPendingUser();
        if ($user === null) {
            redirect('login.php');
        }

        $isSetup = ($user->twoFactorSecret === null);
        $secret = $isSetup ? ($_SESSION['setup_secret'] ?? '') : $user->twoFactorSecret;

        if ($secret === '' || !$this->auth->verifyCode($secret, trim($code))) {
            $this->showCodeForm('That code is incorrect. Check your app and try again.');
            return;
        }

        if ($isSetup) {
            $this->auth->saveSecret($user, $secret);   // the secret works, so keep it for next time
        }
        $this->auth->startSession($user);
        redirect(AccessControl::homePageFor($user->role));
    }

    public function logout(): void
    {
        $this->auth->logout();
        redirect('login.php');
    }

    /** Already signed in? Skip the sign-in page and go to your home page. */
    private function goHomeIfSignedIn(): void
    {
        if (AccessControl::isLoggedIn()) {
            redirect(AccessControl::homePageFor(AccessControl::currentRole()));
        }
    }
}
