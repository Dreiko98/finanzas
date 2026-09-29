<?php
$money = static fn (float $amount): string => number_format($amount, 2, ',', '.') . ' €';
$shortDate = static fn (DateTimeImmutable $date): string => $date->format('d/m');
$nextUrl = !$weeklyStatus['plan_done'] ? '/#actualizar' : (!$weeklyStatus['cash_done'] ? '/movimientos#saldo' : '/');
$nextLabel = !$weeklyStatus['plan_done'] ? 'Empezar por los saldos' : (!$weeklyStatus['cash_done'] ? 'Actualizar gastos corrientes' : 'Volver al resumen');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#191923">
    <title>Ayuda semanal · Finanzas</title>
    <link rel="icon" href="/assets/brand.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="brand"><img src="/assets/brand.svg" alt=""><span class="brand-name">Germán Mallo</span><span>Finanzas</span></a>
        <nav aria-label="Principal"><a href="/">El plan</a><a href="/movimientos">Ingresos y gastos</a><a href="/ayuda" aria-current="page">Ayuda</a></nav>
        <a class="header-help" href="/ayuda" aria-current="page">Ayuda</a>
        <form method="post" action="/logout"><input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>"><button class="button-link" type="submit">Salir</button></form>
    </header>

    <main class="container help-page">
        <section class="help-hero">
            <div>
                <p class="eyebrow">Semana del <?= e($shortDate($weeklyStatus['week_start'])) ?> al <?= e($shortDate($weeklyStatus['week_end'])) ?></p>
                <h1><?= $weeklyStatus['all_done'] ? 'Ya está todo hecho' : 'Te guío paso a paso' ?></h1>
                <p class="muted"><?= $weeklyStatus['all_done'] ? 'No necesitas hacer nada más hasta la próxima semana.' : 'Son dos tareas obligatorias y una comprobación opcional. No tardarás más de un minuto.' ?></p>
            </div>
            <div class="guide-progress" aria-label="<?= $weeklyStatus['completed'] ?> de 2 pasos completados">
                <strong><?= $weeklyStatus['completed'] ?>/2</strong><span>completados</span>
            </div>
        </section>

        <a class="guide-primary-action" href="<?= e($nextUrl) ?>"><?= e($nextLabel) ?> <span aria-hidden="true">→</span></a>

        <ol class="guide-steps">
            <li class="guide-card <?= $weeklyStatus['plan_done'] ? 'is-done' : '' ?>">
                <div class="guide-number"><?= $weeklyStatus['plan_done'] ? '✓' : '1' ?></div>
                <div class="guide-content">
                    <div class="guide-heading"><div><p class="eyebrow">Paso 1 · obligatorio</p><h2>Actualiza tus cinco saldos</h2></div><span class="guide-state"><?= $weeklyStatus['plan_done'] ? 'Hecho' : 'Pendiente' ?></span></div>
                    <p>Abre Trade Republic, myInvestor y tu posición de bitcoin. Copia sus valores y añade cuánto dinero mantienes reservado para gastos fijos.</p>
                    <ul>
                        <li>La cartera piso se queda en 0 € mientras no exista.</li>
                        <li>Marca las casillas si este mes ya aportaste <?= $money($monthlyPlan['funds']) ?> a fondos y <?= $money($monthlyPlan['bitcoin']) ?> a bitcoin.</li>
                        <li>No calcules rentabilidades: escribe el valor que ves hoy.</li>
                    </ul>
                    <a class="button-secondary" href="/#actualizar"><?= $weeklyStatus['plan_done'] ? 'Revisar o corregir saldos' : 'Actualizar saldos' ?></a>
                </div>
            </li>

            <li class="guide-card <?= $weeklyStatus['cash_done'] ? 'is-done' : '' ?>">
                <div class="guide-number"><?= $weeklyStatus['cash_done'] ? '✓' : '2' ?></div>
                <div class="guide-content">
                    <div class="guide-heading"><div><p class="eyebrow">Paso 2 · obligatorio</p><h2>Anota cuánto queda para gastos</h2></div><span class="guide-state"><?= $weeklyStatus['cash_done'] ? 'Hecho' : 'Pendiente' ?></span></div>
                    <p>Mira la cuenta que utilizas para gastos corrientes y copia únicamente el saldo restante. El presupuesto de este mes es <?= $money($monthlyPlan['current']) ?>.</p>
                    <p class="guide-note">No apuntes compras individuales ni las clasifiques. La app estima el gasto mediante las revisiones semanales.</p>
                    <a class="button-secondary" href="/movimientos#saldo"><?= $weeklyStatus['cash_done'] ? 'Revisar o corregir saldo' : 'Actualizar gastos corrientes' ?></a>
                </div>
            </li>

            <li class="guide-card is-optional">
                <div class="guide-number">3</div>
                <div class="guide-content">
                    <div class="guide-heading"><div><p class="eyebrow">Paso 3 · solo si aplica</p><h2>¿Has recibido algún ingreso?</h2></div><span class="guide-state"><?= $weekIncomeCount > 0 ? $weekIncomeCount . ($weekIncomeCount === 1 ? ' añadido' : ' añadidos') : 'Opcional' ?></span></div>
                    <p>Si desde la última revisión cobraste el sueldo o un encargo freelance, añade fecha, concepto e importe. Si no recibiste nada, sáltate este paso.</p>
                    <a class="button-secondary" href="/movimientos#ingreso">Añadir un ingreso</a>
                </div>
            </li>
        </ol>

        <section class="guide-finish">
            <p class="eyebrow">Al terminar</p>
            <h2>Vuelve al resumen</h2>
            <p>Comprueba la barra del colchón, los indicadores de aportaciones y la evolución de tus saldos. Si los dos primeros pasos aparecen como hechos, has terminado por esta semana.</p>
            <a class="button-secondary" href="/">Ver mi resumen</a>
        </section>
    </main>
</body>
</html>
