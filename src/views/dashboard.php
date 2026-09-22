<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Resumen · Finanzas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="brand">Germán Mallo <span>Finanzas</span></a>
        <form method="post" action="/logout">
            <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
            <button class="button-link" type="submit">Salir</button>
        </form>
    </header>
    <main class="container">
        <p class="eyebrow">Esta semana</p>
        <h1>Todo bajo control</h1>
        <div class="card"><p>El seguimiento del plan aparecerá aquí.</p></div>
    </main>
</body>
</html>
