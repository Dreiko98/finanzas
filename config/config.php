<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$envFile = $root . DIRECTORY_SEPARATOR . '.env';

if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

$read = static fn (string $key, ?string $default = null): ?string => (($value = getenv($key)) !== false ? $value : $default);

return [
    'app' => [
        'env' => $read('APP_ENV', 'production'),
        'url' => rtrim((string) $read('APP_URL', 'https://finanzas.germanmallo.com'), '/'),
        'timezone' => $read('APP_TIMEZONE', 'Europe/Madrid'),
    ],
    'database' => [
        'driver' => $read('DB_DRIVER', 'sqlite'),
        'sqlite_path' => $read('DB_SQLITE_PATH') ?: $root . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'finanzas.sqlite',
        'host' => $read('DB_HOST', 'localhost'),
        'port' => $read('DB_PORT', '3306'),
        'name' => $read('DB_NAME'),
        'user' => $read('DB_USER'),
        'password' => $read('DB_PASSWORD'),
    ],
    'auth' => [
        'email' => $read('AUTH_EMAIL'),
        'password_hash' => $read('AUTH_PASSWORD_HASH'),
    ],
];

