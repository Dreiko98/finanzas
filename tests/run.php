<?php

declare(strict_types=1);

$root = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($root): void {
    if (str_starts_with($class, 'App\\')) {
        require $root . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    }
});

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$service = new App\PlanService();
$plan2026 = $service->monthlyPlan(new DateTimeImmutable('2026-12-01'));
$plan2027 = $service->monthlyPlan(new DateTimeImmutable('2027-01-01'));
$assert($plan2026['funds'] === 270.0 && $plan2026['bitcoin'] === 30.0, 'El plan de 2026 no coincide.');
$assert($plan2027['current'] === 450.0 && $plan2027['emergency'] === 400.0, 'El plan de 2027 no coincide.');

$summary = $service->summary(['recorded_on' => '2026-09-20', 'trade_republic' => 1500, 'funds_done' => 1, 'bitcoin_done' => 0], new DateTimeImmutable('2026-09-22'));
$assert($summary['emergency_progress'] === 50.0, 'El progreso del colchón no es correcto.');
$assert($summary['funds_status']['tone'] === 'green' && $summary['bitcoin_status']['tone'] === 'amber', 'Los semáforos no son correctos.');
$old = $service->summary(['recorded_on' => '2026-08-31', 'trade_republic' => 3100, 'funds_done' => 1, 'bitcoin_done' => 1], new DateTimeImmutable('2026-09-26'));
$assert($old['emergency_reached'] === true && $old['funds_status']['tone'] === 'red', 'El cambio de mes o el hito no son correctos.');
$overview = $service->balanceOverview(
    ['trade_republic' => 1000, 'long_term' => 500, 'home_portfolio' => 0, 'bitcoin' => 100, 'fixed_expenses' => 400],
    ['remaining_balance' => 500],
);
$assert($overview['total'] === 2500.0 && $overview['current_expenses'] === 500.0, 'La vista general no separa o suma correctamente los gastos.');
$assert($service->balanceOverview(null, null)['total'] === null, 'La vista general vacía debe distinguirse de un saldo cero.');

$pdo = App\Database::connect(['driver' => 'sqlite', 'sqlite_path' => ':memory:']);
$repository = new App\SnapshotRepository($pdo, 'sqlite');
$snapshot = ['recorded_on' => '2026-09-22', 'trade_republic' => 1000, 'long_term' => 500, 'home_portfolio' => 0, 'bitcoin' => 100, 'fixed_expenses' => 400, 'funds_done' => true, 'bitcoin_done' => false];
$repository->save($snapshot);
$repository->save(array_replace($snapshot, ['trade_republic' => 1100]));
$assert(count($repository->all()) === 1 && (float) $repository->latest()['trade_republic'] === 1100.0, 'La revisión de una fecha no se actualiza correctamente.');

$cashRepository = new App\CashflowRepository($pdo, 'sqlite');
$cashRepository->saveReview('2026-09-07', 430);
$cashRepository->saveReview('2026-09-14', 350);
$cashRepository->saveReview('2026-09-14', 340);
$cashRepository->addIncome('2026-09-01', 'Sueldo', 1700);
$cashRepository->addIncome('2026-09-12', 'Encargo', 200);
$cash = (new App\CashflowService())->summary(
    $cashRepository->reviewsForMonth('2026-09'),
    $cashRepository->incomesForMonth('2026-09'),
    $plan2026,
);
$assert(count($cashRepository->reviewsForMonth('2026-09')) === 2, 'La revisión semanal no se actualiza por fecha.');
$assert($cash['spending'] === 160.0 && $cash['remaining'] === 340.0, 'La estimación de gasto no es correcta.');
$assert($cash['income_total'] === 1900.0 && $cash['income_difference'] === 200.0, 'El total mensual de ingresos no es correcto.');

$_SESSION = [];
$auth = new App\Auth($pdo, 'persona@example.com', password_hash('clave-de-prueba', PASSWORD_BCRYPT));
$assert($auth->configured() && !$auth->check(), 'La autenticación de prueba no se inicializa correctamente.');
for ($attempt = 0; $attempt < 5; $attempt++) {
    $assert(!$auth->attempt('persona@example.com', 'incorrecta', '192.0.2.1'), 'Una contraseña incorrecta fue aceptada.');
}
$assert($auth->tooManyAttempts('192.0.2.1'), 'El límite de cinco intentos no se activa.');

$guide = new App\WeeklyGuide();
$weekly = $guide->status(
    ['recorded_on' => '2026-09-22'],
    ['reviewed_on' => '2026-09-21'],
    new DateTimeImmutable('2026-09-27'),
);
$assert($weekly['all_done'] && $weekly['completed'] === 2, 'La guía no reconoce las tareas hechas durante la semana.');
$nextWeek = $guide->status(
    ['recorded_on' => '2026-09-27'],
    ['reviewed_on' => '2026-09-27'],
    new DateTimeImmutable('2026-09-28'),
);
$assert(!$nextWeek['all_done'] && $nextWeek['pending'] === 2, 'La guía no reinicia las tareas al comenzar una semana.');

fwrite(STDOUT, "OK ({$assertions} comprobaciones)\n");
