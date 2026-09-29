<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

final class PlanService
{
    public const EMERGENCY_TARGET = 3000.0;

    public function monthlyPlan(DateTimeImmutable $date): array
    {
        if ($date->format('Y-m') <= '2026-12') {
            return ['salary' => 1700.0, 'fixed' => 400.0, 'emergency' => 500.0, 'current' => 500.0, 'funds' => 270.0, 'bitcoin' => 30.0];
        }

        return ['salary' => 1500.0, 'fixed' => 400.0, 'emergency' => 400.0, 'current' => 450.0, 'funds' => 230.0, 'bitcoin' => 20.0];
    }

    public function summary(?array $latest, DateTimeImmutable $today): array
    {
        $plan = $this->monthlyPlan($today);
        $tradeRepublic = (float) ($latest['trade_republic'] ?? 0);
        $reached = $tradeRepublic >= self::EMERGENCY_TARGET;
        $isCurrentMonth = isset($latest['recorded_on']) && substr((string) $latest['recorded_on'], 0, 7) === $today->format('Y-m');

        return [
            'plan' => $plan,
            'emergency_progress' => min(100, round(($tradeRepublic / self::EMERGENCY_TARGET) * 100)),
            'emergency_remaining' => max(0, self::EMERGENCY_TARGET - $tradeRepublic),
            'emergency_reached' => $reached,
            'funds_status' => $this->status($isCurrentMonth && !empty($latest['funds_done']), $today),
            'bitcoin_status' => $this->status($isCurrentMonth && !empty($latest['bitcoin_done']), $today),
        ];
    }

    public function balanceOverview(?array $latest, ?array $latestReview): array
    {
        $balances = [
            'trade_republic' => $latest === null ? null : (float) $latest['trade_republic'],
            'long_term' => $latest === null ? null : (float) $latest['long_term'],
            'home_portfolio' => $latest === null ? null : (float) $latest['home_portfolio'],
            'bitcoin' => $latest === null ? null : (float) $latest['bitcoin'],
            'fixed_expenses' => $latest === null ? null : (float) $latest['fixed_expenses'],
            'current_expenses' => $latestReview === null ? null : (float) $latestReview['remaining_balance'],
        ];

        $complete = !in_array(null, $balances, true);
        return ['total' => $complete ? array_sum($balances) : null] + $balances;
    }

    private function status(bool $done, DateTimeImmutable $today): array
    {
        if ($done) {
            return ['tone' => 'green', 'label' => 'Al día'];
        }
        if ((int) $today->format('j') >= 25) {
            return ['tone' => 'red', 'label' => 'Pendiente'];
        }
        return ['tone' => 'amber', 'label' => 'Por hacer'];
    }
}
