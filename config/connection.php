<?php
require_once __DIR__ . '/security.php';

function db(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }
    $settings = [];
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $name) {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new RuntimeException('Database configuration is incomplete.');
        }
        $settings[$name] = $value;
    }
    if (!preg_match('/\A[a-zA-Z0-9.-]+\z/', $settings['DB_HOST']) ||
        !ctype_digit($settings['DB_PORT']) || (int) $settings['DB_PORT'] < 1 ||
        (int) $settings['DB_PORT'] > 65535 ||
        !preg_match('/\A[a-zA-Z0-9_]+\z/', $settings['DB_NAME'])) {
        throw new RuntimeException('Invalid database configuration.');
    }
    $sslMode = getenv('DB_SSLMODE') ?: 'verify-full';
    if (!in_array($sslMode, ['require', 'verify-ca', 'verify-full'], true)) {
        throw new RuntimeException('Database TLS is required.');
    }
    $dsn = 'pgsql:host=' . $settings['DB_HOST'] . ';port=' . $settings['DB_PORT'] .
        ';dbname=' . $settings['DB_NAME'] . ';sslmode=' . $sslMode . ';connect_timeout=10';
    $certificate = getenv('DB_SSLROOTCERT');
    if ($certificate !== false && $certificate !== '') {
        if (preg_match('/[;\r\n\x00]/', $certificate) || !is_file($certificate)) {
            throw new RuntimeException('Invalid database CA certificate.');
        }
        $dsn .= ";sslrootcert='" . str_replace(['\\', "'"], ['\\\\', "\\'"], $certificate) . "'";
    }
    $connection = new PDO($dsn, $settings['DB_USER'], $settings['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
        // Native binding without named server statements also supports poolers.
        PDO::PGSQL_ATTR_DISABLE_PREPARES => true,
    ]);
    $connection->exec("SET client_encoding TO 'UTF8'");
    return $connection;
}
