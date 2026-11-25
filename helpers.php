<?php
// Small helper functions that every page can use.
// They are plain functions, not classes, so they are not on the class diagram.

// Makes text safe to print in HTML. Use it on EVERY value you print, for example h($customer->name).
// If someone types <script> into a form, it shows as text instead of running as code.
function h($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Money for display: 2500 becomes $2,500.00
function money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

// True when the page was reached by submitting a form (POST), false when just opened (GET).
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Sends the browser to another page and stops this one.
function redirect(string $page): void
{
    header('Location: ' . $page);
    exit;
}

// A one-time message shown on the NEXT page, for example after saving then redirecting.
// set_flash('Customer saved.');   or   set_flash('Customer not found.', 'error');
function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Prints the top of every page: the title, the menu for the user's role and any one-time message.
function render_header(string $pageTitle): void
{
    require __DIR__ . '/ui/partials/header.php';
}

// Prints the bottom of every page.
function render_footer(): void
{
    require __DIR__ . '/ui/partials/footer.php';
}

// Reads the one-time message and removes it, so it only shows once. header.php calls this.
function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
