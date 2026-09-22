<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$target = $root . '/.env';
if (is_file($target)) {
    fwrite(STDERR, "Ya existe .env. No se ha sobrescrito.\n");
    exit(1);
}

$email = getenv('FINANZAS_SETUP_EMAIL') ?: '';
$password = getenv('FINANZAS_SETUP_PASSWORD') ?: '';
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || $password === '') {
    fwrite(STDERR, "Define FINANZAS_SETUP_EMAIL y FINANZAS_SETUP_PASSWORD solo para este comando.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$contents = "APP_ENV=production\n"
    . "APP_URL=https://finanzas.germanmallo.com\n"
    . "APP_TIMEZONE=Europe/Madrid\n\n"
    . "DB_DRIVER=sqlite\n"
    . "DB_SQLITE_PATH=\n"
    . "DB_HOST=\nDB_PORT=3306\nDB_NAME=\nDB_USER=\nDB_PASSWORD=\n\n"
    . "AUTH_EMAIL={$email}\n"
    . "AUTH_PASSWORD_HASH={$hash}\n";

if (file_put_contents($target, $contents, LOCK_EX) === false) {
    fwrite(STDERR, "No se pudo crear .env.\n");
    exit(1);
}
@chmod($target, 0600);
fwrite(STDOUT, ".env creado con la contraseña protegida mediante bcrypt.\n");
