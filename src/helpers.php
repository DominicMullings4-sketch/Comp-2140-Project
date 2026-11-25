<?php
// Small functions that every page can use. They are plain functions, not classes,
// so they are not on the class diagram.

// Makes text safe to print in HTML (stops code being typed into a form and run).
// Use it for every value a user typed, for example e($customer->getName())
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// Money for display, for example 2500 becomes $2,500.00
function money($amount)
{
    return '$' . number_format($amount, 2);
}

// A short unique ID, for example new_id('ORD') gives something like ORD-3F9A1C2B
function new_id($prefix)
{
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

// The date and time right now, in the database's format (2026-11-26 14:05:00)
function now()
{
    return date('Y-m-d H:i:s');
}

// Today's date, in the database's format (2026-11-26)
function today()
{
    return date('Y-m-d');
}

// Sends the browser to another page and stops this one.
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

// A one-time message shown on the NEXT page, for example after saving then redirecting.
// set_flash('Customer saved.');   or   set_flash('Customer not found.', 'error');
function set_flash($message, $type = 'success')
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Reads the one-time message and removes it, so it only shows once. header.php calls this.
function take_flash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Prints a list of error messages in a red box (does nothing when the list is empty).
function show_errors($errors)
{
    if (count($errors) == 0) {
        return;
    }
    echo '<ul class="error">';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul>';
}

// Splits an Exception's message into a list of errors.
// The Mgmt classes put several problems in one Exception, one per line, so the page can list them all.
function error_list(Exception $exception)
{
    return explode("\n", $exception->getMessage());
}

// Prints the top of every page: the title and the menu for the user's role.
function render_header($title, $user = null)
{
    require __DIR__ . '/../templates/header.php';
}

// Prints the bottom of every page.
function render_footer()
{
    require __DIR__ . '/../templates/footer.php';
}
