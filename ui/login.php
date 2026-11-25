<?php
// Sign-in page. Already built (foundation), from the team's original login code.
//   login.php            -> step 1: username + password
//   login.php?step=code  -> step 2: 6-digit authenticator code (only when USE_TWO_FACTOR is true)

// ===== TOP: logic only =====
require __DIR__ . '/../bootstrap.php';

// Already signed in? Go straight to your home page.
if (AccessControl::isLoggedIn()) {
    redirect(AccessControl::homePageFor(AccessControl::currentRole()));
}

$auth = new Auth();
$step = $_GET['step'] ?? 'password';
$error = null;

// ----- Step 1: username + password
if ($step === 'password' && is_post()) {
    $user = $auth->checkPassword(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');

    if ($user === null) {
        $error = 'Incorrect username or password.';
    } elseif (!USE_TWO_FACTOR) {
        $auth->startSession($user);
        redirect(AccessControl::homePageFor($user->role));
    } else {
        $auth->setPendingUser($user);
        redirect('login.php?step=code');
    }
}

// ----- Step 2: authenticator code
$needsSetup = false;
if ($step === 'code') {
    $user = $auth->getPendingUser();
    if ($user === null) {
        redirect('login.php');   // nobody passed step 1, so start again
    }

    // First sign-in: the user has no secret yet, so make one for them to add to their app.
    $needsSetup = ($user->twoFactorSecret === null);
    if ($needsSetup && !isset($_SESSION['setup_secret'])) {
        $_SESSION['setup_secret'] = $auth->newSecret();
    }
    $secret = $needsSetup ? $_SESSION['setup_secret'] : $user->twoFactorSecret;

    if (is_post()) {
        if ($auth->verifyCode($secret, trim($_POST['code'] ?? ''))) {
            if ($needsSetup) {
                $auth->saveSecret($user, $secret);   // the secret works, so keep it for next time
            }
            $auth->startSession($user);
            redirect(AccessControl::homePageFor($user->role));
        }
        $error = 'That code is incorrect. Check your app and try again.';
    }
}

$pageTitle = 'Sign in';
?>
<!-- ===== BOTTOM: HTML only ===== -->
<?php include __DIR__ . '/partials/header.php'; ?>

<section class="card narrow">
    <?php if ($error !== null): ?>
        <p class="flash error"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($step === 'code'): ?>
        <h1>Enter verification code</h1>

        <?php if ($needsSetup): ?>
            <p><strong>First sign-in:</strong> add Track-It to your authenticator app.
               Choose "Enter a setup key" and type this key:</p>
            <p><code><?= h($_SESSION['setup_secret']) ?></code></p>
        <?php endif; ?>

        <p>Open your authenticator app and type the 6-digit code.</p>
        <form method="post" action="login.php?step=code">
            <label for="code">Code</label>
            <input id="code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus>
            <button type="submit">Verify</button>
        </form>
        <p><a href="logout.php">Start again</a></p>

    <?php else: ?>
        <h1>Sign in</h1>
        <form method="post" action="login.php">
            <label for="username">Username</label>
            <input id="username" name="username" autocomplete="username" required autofocus>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <button type="submit">Sign in</button>
        </form>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
