<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

function abort_request(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

set_exception_handler(function (Throwable $error): void {
    // Messages can contain SQL values, credentials, and local paths.
    error_log('Mu5tasar request failed (' . get_class($error) . ').');
    abort_request(500, 'The request could not be completed. Please try again later.');
});

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_text(string $name): string
{
    $value = $_POST[$name] ?? '';
    if (!is_string($value)) {
        abort_request(400, 'Invalid form value.');
    }
    return $value;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function valid_csrf_token($token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}

function require_login(): void
{
    if (!isset($_SESSION['email'])) {
        header('Location: login.php');
        exit;
    }
}

function image_url($filename): ?string
{
    // Images are local basenames only, never visitor-supplied URLs or paths.
    if (!is_string($filename) || !preg_match('/\A[^\/\\\\\x00-\x1F\x7F]+\.(?:jpe?g|png|gif|webp)\z/i', $filename)) {
        return null;
    }
    return 'uploads/' . rawurlencode($filename);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'use_strict_mode' => 1,
        'use_only_cookies' => 1,
        'cookie_httponly' => 1,
        'cookie_secure' => getenv('SESSION_COOKIE_SECURE') !== '0',
        'cookie_samesite' => 'Lax',
    ]);
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST');
    abort_request(405, 'Method not allowed.');
}
if ($method === 'POST' && !valid_csrf_token($_POST['csrf_token'] ?? null)) {
    abort_request(403, 'Invalid CSRF token. Reload the form and try again.');
}
