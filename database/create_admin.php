<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Istanbul');

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}
if ($argc !== 3 || !filter_var($argv[1], FILTER_VALIDATE_EMAIL) || strlen($argv[1]) > 254 || trim($argv[2]) === '') {
    fwrite(STDERR, "Usage: php database/create_admin.php email \"Full Name\"\n");
    exit(1);
}
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        require dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    }
});
fwrite(STDERR, "Password (stdin): ");
$password = rtrim((string) fgets(STDIN), "\r\n");
if (strlen($password) < 8) {
    fwrite(STDERR, "Password must contain at least 8 bytes.\n");
    exit(1);
}
try {
    $stmt = App\Core\Database::connection()->prepare("INSERT INTO users (email, full_name, password_hash, role, student_type) VALUES (?, ?, ?, 'admin', NULL)");
    $stmt->execute([$argv[1], trim($argv[2]), password_hash($password, PASSWORD_DEFAULT)]);
    echo "Admin created.\n";
} catch (Throwable $e) {
    error_log($e->__toString() . PHP_EOL, 3, dirname(__DIR__) . '/storage/logs/app.log');
    fwrite(STDERR, "Unable to create admin; see application log.\n");
    exit(1);
}
