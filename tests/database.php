<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config/connection.php';
restore_exception_handler();

try {
    if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('Enable pdo_pgsql in the PHP configuration used by this command.');
    }
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $name) {
        if (getenv($name) === false || getenv($name) === '') {
            throw new RuntimeException('Configure ' . $name . ' in the server environment.');
        }
    }
} catch (RuntimeException $error) {
    fwrite(STDERR, 'SETUP REQUIRED: ' . $error->getMessage() . PHP_EOL);
    exit(2);
}

try {
    $connection = db();
    $connection->beginTransaction();
    $connection->exec('SET TRANSACTION READ ONLY');
    $connection->query('SELECT id, catagorie, description FROM public.quiz LIMIT 0');
    $connection->query('SELECT email, password FROM public.users LIMIT 0');
    $query = $connection->prepare('SELECT email FROM public.users WHERE email = ?');
    $query->execute(["' OR 1=1 --"]);
    if ($query->fetch()) {
        throw new RuntimeException('Unexpected test match.');
    }
    $connection->rollBack();
    echo "PASS: PDO PostgreSQL connection, table access, and parameterized hostile email check. No records changed.\n";
} catch (Throwable $error) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, "FAIL: Check database credentials, outbound connectivity, TLS CA trust, and table privileges. No exception details displayed.\n");
    exit(1);
}
