<?php

declare(strict_types=1);

use App\Csrf;
use App\PlanService;
use App\SnapshotRepository;

try {
    ['config' => $config, 'pdo' => $pdo, 'auth' => $auth] = require dirname(__DIR__) . '/src/bootstrap.php';
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

$snapshots = new SnapshotRepository($pdo, (string) $config['database']['driver']);

if ($path === '/plan' && $method === 'POST') {
    if (!Csrf::verify($_POST['_token'] ?? null)) {
        http_response_code(419);
        exit('La sesión ha caducado.');
    }

    $date = (string) ($_POST['recorded_on'] ?? '');
    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $validDate = $dateObject !== false && $dateObject->format('Y-m-d') === $date && $date <= date('Y-m-d');
    $fields = ['trade_republic', 'long_term', 'home_portfolio', 'bitcoin', 'bbva'];
    $values = [];
    foreach ($fields as $field) {
        $raw = str_replace(',', '.', trim((string) ($_POST[$field] ?? '')));
        if ($raw === '' || !is_numeric($raw) || (float) $raw < 0 || (float) $raw > 99999999.99) {
            $_SESSION['flash_error'] = 'Revisa los saldos: deben ser importes positivos válidos.';
            redirect('/#actualizar');
        }
        $values[$field] = round((float) $raw, 2);
    }
    if (!$validDate) {
        $_SESSION['flash_error'] = 'La fecha de revisión no es válida.';
        redirect('/#actualizar');
    }

    $snapshots->save($values + [
        'recorded_on' => $date,
        'funds_done' => isset($_POST['funds_done']),
        'bitcoin_done' => isset($_POST['bitcoin_done']),
    ]);
    $_SESSION['flash_success'] = 'Revisión semanal guardada.';
    redirect('/');
}

if ($path !== '/') {
    http_response_code(404);
    exit('Página no encontrada.');
}

$latest = $snapshots->latest();
$history = $snapshots->all();
$planService = new PlanService();
$planSummary = $planService->summary($latest, new DateTimeImmutable('today'));
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

require dirname(__DIR__) . '/src/views/dashboard.php';
