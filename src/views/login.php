<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#191923">
    <title>Acceso · Finanzas</title>
    <link rel="icon" href="/assets/brand.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="login-page">
    <main class="login-card">
        <img class="login-logo" src="/assets/brand.svg" alt="">
        <p class="eyebrow">Germán Mallo</p>
        <h1>Mis finanzas</h1>
        <p class="muted">Una revisión a la semana. Nada más.</p>

        <?php if (!$auth->configured()): ?>
            <div class="alert alert-warning">Falta configurar el acceso en el archivo <code>.env</code>.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/login" class="stack">
            <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
            <label>Email
                <input type="email" name="email" autocomplete="username" inputmode="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
            </label>
            <label>Contraseña
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
