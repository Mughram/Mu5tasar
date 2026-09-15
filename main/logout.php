<?php
require_once __DIR__ . '/../config/security.php';
if ($method !== 'POST') {
    header('Allow: POST');
    abort_request(405, 'Method not allowed.');
}
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => $params['secure'],
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_destroy();
header('Location: index.php');
exit;
?>
