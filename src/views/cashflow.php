<?php
$money = static fn (float $amount): string => number_format($amount, 2, ',', '.') . ' €';
$monthLabel = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][(int) $monthDate->format('n') - 1] . ' ' . $monthDate->format('Y');
$selectedIsCurrent = $month === date('Y-m');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#191923">
    <title>Ingresos y gastos · Finanzas</title>
    <link rel="icon" href="/assets/brand.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="brand"><img src="/assets/brand.svg" alt=""><span class="brand-name">Germán Mallo</span><span>Finanzas</span></a>
        <nav aria-label="Principal"><a href="/">El plan</a><a href="/movimientos" aria-current="page">Ingresos y gastos</a><a href="/ayuda">Ayuda</a><a href="#saldo">Actualizar saldo</a></nav>
        <a class="header-help" href="/ayuda">Ayuda</a>
        <form method="post" action="/logout"><input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>"><button class="button-link" type="submit">Salir</button></form>
    </header>

    <main class="container">
        <?php if ($flashSuccess): ?><div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-error" role="alert"><?= e($flashError) ?></div><?php endif; ?>

        <section class="page-heading">
            <div><p class="eyebrow">Ingresos y gastos</p><h1><?= e(ucfirst($monthLabel)) ?></h1></div>
            <form method="get" action="/movimientos" class="month-picker"><label>Ver mes<input type="month" name="month" max="<?= e(date('Y-m')) ?>" value="<?= e($month) ?>" onchange="this.form.submit()"></label></form>
        </section>

        <section class="cash-summary">
            <article class="card"><span>Ingresado</span><strong><?= $money($cashSummary['income_total']) ?></strong><small>Objetivo: <?= $money($cashSummary['income_target']) ?></small></article>
            <article class="card"><span>Gasto corriente estimado</span><strong><?= $cashSummary['spending'] === null ? '—' : $money($cashSummary['spending']) ?></strong><small>Presupuesto: <?= $money($cashSummary['spending_target']) ?></small></article>
            <article class="card"><span>Saldo corriente</span><strong><?= $cashSummary['remaining'] === null ? '—' : $money($cashSummary['remaining']) ?></strong><small><?= count($reviews) ?> <?= count($reviews) === 1 ? 'revisión' : 'revisiones' ?> este mes</small></article>
        </section>

        <?php if ($selectedIsCurrent): ?>
        <section class="quick-forms">
            <article class="card form-card" id="saldo">
                <p class="eyebrow">Una vez por semana</p><h2>Saldo de gastos corrientes</h2>
                <form method="post" action="/cash/balance" class="compact-form">
                    <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
                    <label>Fecha<input type="date" name="reviewed_on" max="<?= e(date('Y-m-d')) ?>" required value="<?= e(date('Y-m-d')) ?>"></label>
                    <label>Saldo restante (€)<input type="number" name="remaining_balance" min="0" max="99999999.99" step="0.01" inputmode="decimal" required value="<?= e($latestReview['remaining_balance'] ?? '') ?>"></label>
                    <button type="submit">Guardar saldo</button>
                </form>
            </article>

            <article class="card form-card" id="ingreso">
                <p class="eyebrow">Solo cuando ocurra</p><h2>Añadir ingreso</h2>
                <form method="post" action="/cash/income" class="compact-form">
                    <input type="hidden" name="_token" value="<?= e(\App\Csrf::token()) ?>">
                    <label>Fecha<input type="date" name="income_date" max="<?= e(date('Y-m-d')) ?>" required value="<?= e(date('Y-m-d')) ?>"></label>
                    <label>Concepto<input type="text" name="concept" maxlength="120" placeholder="Sueldo, encargo…" required></label>
                    <label>Importe (€)<input type="number" name="amount" min="0.01" max="99999999.99" step="0.01" inputmode="decimal" required></label>
                    <button type="submit">Añadir ingreso</button>
                </form>
            </article>
        </section>
        <?php endif; ?>

        <section class="card income-list">
            <div class="card-title"><h2>Ingresos del mes</h2><strong><?= $money($cashSummary['income_total']) ?></strong></div>
            <?php if ($incomes === []): ?>
                <p class="empty">Todavía no hay ingresos en este mes.</p>
            <?php else: ?>
                <div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Concepto</th><th>Importe</th></tr></thead><tbody>
                <?php foreach ($incomes as $income): ?><tr><td><?= e(date('d/m/Y', strtotime($income['income_date']))) ?></td><td><?= e($income['concept']) ?></td><td><?= $money((float) $income['amount']) ?></td></tr><?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
