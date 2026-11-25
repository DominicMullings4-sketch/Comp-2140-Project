<?php
// TOTP: the 6-digit codes that authenticator apps show (the standard is called RFC 6238).
// TOTP was imported as is, no action or implementation required. 

// The 32 characters an authenticator secret is written with (called "base-32").
const TOTP_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

// A new random secret, 32 characters long, for example "JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP".
// This is what the user types or scans into their authenticator app.
function totp_random_secret()
{
    $secret = '';
    for ($i = 0; $i < 32; $i++) {
        $secret .= TOTP_ALPHABET[random_int(0, 31)];
    }
    return $secret;
}

// The 6-digit code for a secret at a given moment. With no $time, it uses right now.
// The phone does exactly the same calculation, which is why both get the same number.
function totp_code($secret, $time = null)
{
    if ($time === null) {
        $time = time();
    }

    // 1. Which 30-second block are we in? (Both sides count blocks since 1 January 1970.)
    $block = intdiv($time, 30);

    // 2. Mix the block number with the secret using a hash (HMAC-SHA1). The result is 20 bytes.
    $hash = hash_hmac('sha1', pack('J', $block), totp_secret_to_bytes($secret), true);

    // 3. Read 4 bytes from a spot in the hash (the last byte says where), and turn them into a number.
    $spot = ord($hash[19]) & 0x0F;
    $number = ((ord($hash[$spot]) & 0x7F) << 24)
            | (ord($hash[$spot + 1]) << 16)
            | (ord($hash[$spot + 2]) << 8)
            |  ord($hash[$spot + 3]);

    // 4. Keep the last 6 digits, with leading zeros (so 81804 becomes "081804").
    return str_pad($number % 1000000, 6, '0', STR_PAD_LEFT);
}

// Turns the 32-character secret back into the raw bytes the hash needs.
// Each character stands for 5 bits; we join all the bits, then cut them into bytes of 8.
function totp_secret_to_bytes($secret)
{
    $secret = strtoupper(str_replace([' ', '='], '', $secret));

    $bits = '';
    for ($i = 0; $i < strlen($secret); $i++) {
        $value = strpos(TOTP_ALPHABET, $secret[$i]);
        if ($value === false) {
            throw new Exception('This is not a valid authenticator secret.');
        }
        $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
    }

    $bytes = '';
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $bytes .= chr(bindec(substr($bits, $i, 8)));
    }
    return $bytes;
}
