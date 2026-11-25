<?php

/**
 * TwoFactorAuth: Authentication & Authorization layer.
 * Feature owner: P1 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class TwoFactorAuth
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
     * SC-2: a new random secret for an authenticator app (a TOTP library will be added to the project).
     */
    public function generateSecret(): string
    {
        return totp_random_secret();   // see src/auth/totp.php
    }

    /**
     * SC-2: the QR code / otpauth:// link the user scans into their authenticator app.
     */
    public function getSetupQrCode(User $user, string $secret): string
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('TwoFactorAuth::getSetupQrCode() is not built yet.');
    }

    /**
     * SC-2: check the 6-digit code from the user's authenticator app against their secret.
     */
    public function verifyAppCode(User $user, string $code): bool
    {
        // Ported from Auth::verifyCode() (based-on-old-srs).
        // Also accepts the previous 30-second code, in case the user typed it just as it changed.
        $secret = $user->getTwoFactorSecret();
        if ($secret == null) {
            return false;
        }
        if (hash_equals(totp_code($secret), $code)) {
            return true;
        }
        if (hash_equals(totp_code($secret, time() - 30), $code)) {
            return true;
        }
        return false;
    }

    /**
     * SC-2: optional second method: email a one-time code. The authenticator-app method alone meets SC-2.
     */
    public function sendEmailCode(User $user): void
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('TwoFactorAuth::sendEmailCode() is not built yet.');
    }

    /**
     * SC-2: check the emailed code (if you build sendEmailCode).
     */
    public function verifyEmailCode(User $user, string $code): bool
    {
        // TODO (P1): build this method. The note above says what it must do.
        throw new Exception('TwoFactorAuth::verifyEmailCode() is not built yet.');
    }

    /**
     * First sign-in with two-factor on: saves the new secret once the user has proved it works
     * (they typed a correct code). Ported from Auth::saveSecret() (based-on-old-srs).
     * NOT ON THE CLASS DIAGRAM YET: add it to TwoFactorAuth in the SDS.
     */
    public function saveSecret(User $user, string $secret): void
    {
        $this->users->saveTwoFactorSecret($user, $secret);
    }
}
