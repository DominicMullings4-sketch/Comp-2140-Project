<?php
// Shown when a signed-in user opens a page their role can't use (AccessControl::requireRole).
require __DIR__ . '/bootstrap.php';
AccessControl::requireLogin();

http_response_code(403);
$home = AccessControl::homePageFor(AccessControl::currentRole());

render_header('Access denied');
?>
<h1>Access denied</h1>
<p>Your role does not have access to that page.</p>
<p><a href="<?= h($home) ?>">Go to your home page</a></p>
<?php
render_footer();
