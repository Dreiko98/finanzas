<?php
$money = static fn (float $amount): string => number_format($amount, 2, ',', '.') . ' €';
$currentMonth = date('Y-m');
$form = $latest
    ? array_replace($latest, [
        'recorded_on' => date('Y-m-d'),
        'funds_done' => substr((string) $latest['recorded_on'], 0, 7) === $currentMonth ? $latest['funds_done'] : 0,
        'bitcoin_done' => substr((string) $latest['recorded_on'], 0, 7) === $currentMonth ? $latest['bitcoin_done'] : 0,
    ])
    : ['recorded_on' => date('Y-m-d'), 'trade_republic' => '', 'long_term' => '', 'home_portfolio' => '0', 'bitcoin' => '', 'fixed_expenses' => '', 'funds_done' => 0, 'bitcoin_done' => 0];
$chartHistory = array_map(static fn (array $row): array => [
    'date' => $row['recorded_on'], 'trade' => (float) $row['trade_republic'],
    'longTerm' => (float) $row['long_term'], 'home' => (float) $row['home_portfolio'],
    'bitcoin' => (float) $row['bitcoin'], 'fixed' => (float) $row['fixed_expenses'],
], $history);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#191923">
    <title>Resumen · Finanzas</title>
    <link rel="icon" href="/assets/brand.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="brand"><img src="/assets/brand.svg" alt=""><span class="brand-name">Germán Mallo</span><span>Finanzas</span></a>
        <nav aria-label="Principal"><a href="/" aria-current="page">El plan</a><a href="/movimientos">Ingresos y gastos</a><a href="/ayuda">Ayuda</a><a href="#actualizar">Actualizar</a></nav>
        <a class="header-help" href="/ayuda">Ayuda</a>
        <form method="post" action="/logout">
            <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
            <button class="button-link" type="submit">Salir</button>
        </form>
    </header>

    <main class="container">
        <?php if ($flashSuccess): ?><div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-error" role="alert"><?= e($flashError) ?></div><?php endif; ?>

        <section class="page-heading">
            <div><p class="eyebrow">Tu plan, hoy</p><h1><?= $latest ? 'Así vas' : 'Primera revisión' ?></h1></div>
            <?php if ($latest): ?><p class="muted">Actualizado el <?= e(date('d/m/Y', strtotime($latest['recorded_on']))) ?></p><?php endif; ?>
        </section>

        <section class="weekly-guide-banner <?= $weeklyStatus['all_done'] ? 'is-complete' : '' ?>">
            <div>
                <strong><?= $weeklyStatus['all_done'] ? 'Revisión semanal completada' : 'Tu revisión semanal' ?></strong>
                <span><?= $weeklyStatus['all_done'] ? 'Ya has hecho los dos pasos importantes de esta semana.' : $weeklyStatus['pending'] . ($weeklyStatus['pending'] === 1 ? ' paso pendiente.' : ' pasos pendientes.') ?></span>
            </div>
            <a class="button-secondary" href="/ayuda"><?= $weeklyStatus['all_done'] ? 'Ver ayuda' : 'Guíame paso a paso' ?></a>
        </section>

        <section class="overview-section" aria-labelledby="overview-title">
            <div class="overview-heading">
                <div><p class="eyebrow">Tus saldos actuales</p><h2 id="overview-title">Vista general</h2></div>
                <?php if (!$latest): ?><p>Se completará cuando guardes tu primera revisión.</p><?php endif; ?>
            </div>
            <div class="overview-grid">
                <article class="overview-kpi overview-total">
                    <span>Total registrado</span>
                    <strong><?= $balanceOverview['total'] === null ? '—' : $money($balanceOverview['total']) ?></strong>
                    <small>Suma de los cinco saldos</small>
                </article>
                <article class="overview-kpi"><span>Trade Republic</span><strong><?= $balanceOverview['trade_republic'] === null ? '—' : $money($balanceOverview['trade_republic']) ?></strong><small>Colchón de emergencia</small></article>
                <article class="overview-kpi"><span>Fondos · largo plazo</span><strong><?= $balanceOverview['long_term'] === null ? '—' : $money($balanceOverview['long_term']) ?></strong><small>myInvestor</small></article>
                <article class="overview-kpi"><span>Cartera piso</span><strong><?= $balanceOverview['home_portfolio'] === null ? '—' : $money($balanceOverview['home_portfolio']) ?></strong><small>myInvestor</small></article>
                <article class="overview-kpi"><span>Bitcoin</span><strong><?= $balanceOverview['bitcoin'] === null ? '—' : $money($balanceOverview['bitcoin']) ?></strong><small>Valor actual</small></article>
                <article class="overview-kpi"><span>Gastos corrientes</span><strong><?= $balanceOverview['current_expenses'] === null ? '—' : $money($balanceOverview['current_expenses']) ?></strong><small>Saldo restante</small></article>
                <article class="overview-kpi"><span>Gastos fijos</span><strong><?= $balanceOverview['fixed_expenses'] === null ? '—' : $money($balanceOverview['fixed_expenses']) ?></strong><small>Dinero reservado</small></article>
            </div>
        </section>

        <?php if ($planSummary['emergency_reached']): ?>
            <section class="milestone"><strong>Colchón completado 🎉</strong><span>Abre la cartera piso en myInvestor y dirige allí los <?= $money($planSummary['plan']['emergency']) ?> mensuales. Trade Republic queda fijo en 3.000 €.</span></section>
        <?php endif; ?>

        <section class="summary-grid" aria-label="Resumen del plan">
            <article class="card progress-card">
                <div class="card-title"><span>Colchón de emergencia</span><strong><?= (int) $planSummary['emergency_progress'] ?>%</strong></div>
                <div class="progress" role="progressbar" aria-valuenow="<?= (int) $planSummary['emergency_progress'] ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?= (int) $planSummary['emergency_progress'] ?>%"></span></div>
                <p><?= $latest ? $money((float) $latest['trade_republic']) : '0,00 €' ?> de 3.000,00 €</p>
                <?php if (!$planSummary['emergency_reached']): ?><small>Faltan <?= $money($planSummary['emergency_remaining']) ?></small><?php endif; ?>
            </article>
            <article class="card contribution-card">
                <p class="card-label">Aportaciones del mes</p>
                <div class="status-row"><span>Fondos · <?= $money($planSummary['plan']['funds']) ?></span><span class="status status-<?= e($planSummary['funds_status']['tone']) ?>"><?= e($planSummary['funds_status']['label']) ?></span></div>
                <div class="status-row"><span>Bitcoin · <?= $money($planSummary['plan']['bitcoin']) ?></span><span class="status status-<?= e($planSummary['bitcoin_status']['tone']) ?>"><?= e($planSummary['bitcoin_status']['label']) ?></span></div>
            </article>
        </section>

        <?php if (count($history) >= 2): ?>
        <section class="card chart-card">
            <div class="card-title"><div><p class="eyebrow">Histórico</p><h2>Evolución de saldos</h2></div></div>
            <div class="chart-wrap"><canvas id="balance-chart" data-history="<?= e(json_encode($chartHistory, JSON_THROW_ON_ERROR)) ?>" aria-label="Evolución de los cinco saldos"></canvas></div>
            <div class="chart-legend" aria-hidden="true"><span class="trade">Trade Republic</span><span class="long">Largo plazo</span><span class="home">Piso</span><span class="bitcoin">Bitcoin</span><span class="fixed">Gastos fijos</span></div>
        </section>
        <?php endif; ?>

        <section class="card form-card" id="actualizar">
            <div class="section-heading"><div><p class="eyebrow">Menos de un minuto</p><h2>Revisión semanal</h2></div><p class="muted">Guardar de nuevo la misma fecha corrige esa revisión.</p></div>
            <form method="post" action="/plan" class="weekly-form">
                <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
                <label>Fecha<input type="date" name="recorded_on" max="<?= e(date('Y-m-d')) ?>" required value="<?= e($form['recorded_on']) ?>"></label>
                <div class="input-grid">
                    <label>Trade Republic (€)<input type="number" name="trade_republic" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($form['trade_republic']) ?>"></label>
                    <label>myInvestor · largo plazo (€)<input type="number" name="long_term" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($form['long_term']) ?>"></label>
                    <label>myInvestor · piso (€)<input type="number" name="home_portfolio" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($form['home_portfolio']) ?>"></label>
                    <label>Bitcoin (€)<input type="number" name="bitcoin" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($form['bitcoin']) ?>"></label>
                    <label>Gastos fijos reservados (€)<input type="number" name="fixed_expenses" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($form['fixed_expenses']) ?>"></label>
                </div>
                <fieldset>
                    <legend>Aportaciones de este mes</legend>
                    <label class="check"><input type="checkbox" name="funds_done" value="1" <?= !empty($form['funds_done']) ? 'checked' : '' ?>> Fondos hechos (<?= $money($planSummary['plan']['funds']) ?>)</label>
                    <label class="check"><input type="checkbox" name="bitcoin_done" value="1" <?= !empty($form['bitcoin_done']) ? 'checked' : '' ?>> Bitcoin hecho (<?= $money($planSummary['plan']['bitcoin']) ?>)</label>
                </fieldset>
                <button type="submit">Guardar revisión</button>
            </form>
        </section>
    </main>
    <script src="/assets/app.js" defer></script>
</body>
</html>
