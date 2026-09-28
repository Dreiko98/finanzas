<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$config = require $root . '/config/config.php';

date_default_timezone_set((string) $config['app']['timezone']);

spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = $root . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$production = $config['app']['env'] === 'production';
if ($production) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
if ($production) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('finanzas_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $production,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

$pdo = App\Database::connect($config['database']);
$auth = new App\Auth($pdo, $config['auth']['email'], $config['auth']['password_hash']);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

return compact('config', 'pdo', 'auth');
