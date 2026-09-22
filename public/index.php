<?php

declare(strict_types=1);

use App\Csrf;

try {
    ['config' => $config, 'auth' => $auth] = require dirname(__DIR__) . '/src/bootstrap.php';
} catch (Throwable $exception) {
    http_response_code(503);
    error_log($exception->__toString());
    exit('La aplicación todavía no está configurada. Consulta el README para completar la instalación.');
}

$path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$error = null;

if ($path === '/login') {
    if ($auth->check()) {
        redirect('/');
    }

    if ($method === 'POST') {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(419);
            $error = 'La sesión ha caducado. Recarga la página e inténtalo de nuevo.';
        } elseif ($auth->tooManyAttempts($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            http_response_code(429);
            $error = 'Demasiados intentos. Espera 15 minutos antes de volver a probar.';
        } elseif ($auth->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''), $_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            redirect('/');
        } else {
            http_response_code(422);
            $error = 'El email o la contraseña no son correctos.';
        }
    }

    require dirname(__DIR__) . '/src/views/login.php';
    exit;
}

if (!$auth->check()) {
    redirect('/login');
}

if ($path === '/logout' && $method === 'POST') {
    if (!Csrf::verify($_POST['_token'] ?? null)) {
        http_response_code(419);
        exit('La sesión ha caducado.');
    }
    $auth->logout();
    redirect('/login');
}

if ($path !== '/') {
    http_response_code(404);
}

require dirname(__DIR__) . '/src/views/dashboard.php';

