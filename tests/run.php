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

$pdo = App\Database::connect(['driver' => 'sqlite', 'sqlite_path' => ':memory:']);
$repository = new App\SnapshotRepository($pdo, 'sqlite');
$snapshot = ['recorded_on' => '2026-09-22', 'trade_republic' => 1000, 'long_term' => 500, 'home_portfolio' => 0, 'bitcoin' => 100, 'bbva' => 700, 'funds_done' => true, 'bitcoin_done' => false];
$repository->save($snapshot);
$repository->save(array_replace($snapshot, ['trade_republic' => 1100]));
$assert(count($repository->all()) === 1 && (float) $repository->latest()['trade_republic'] === 1100.0, 'La revisión de una fecha no se actualiza correctamente.');

fwrite(STDOUT, "OK ({$assertions} comprobaciones)\n");
