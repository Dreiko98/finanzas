<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

final class WeeklyGuide
{
    public function status(?array $snapshot, ?array $review, DateTimeImmutable $today): array
    {
        $weekStart = $today->modify('monday this week')->setTime(0, 0);
        $weekEnd = $weekStart->modify('+6 days');
        $nextWeek = $weekStart->modify('+7 days');
        $planDone = $this->isWithinWeek($snapshot['recorded_on'] ?? null, $weekStart, $nextWeek);
        $cashDone = $this->isWithinWeek($review['reviewed_on'] ?? null, $weekStart, $nextWeek);
        $completed = (int) $planDone + (int) $cashDone;

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'plan_done' => $planDone,
            'cash_done' => $cashDone,
            'completed' => $completed,
            'pending' => 2 - $completed,
            'all_done' => $completed === 2,
        ];
    }

    private function isWithinWeek(mixed $date, DateTimeImmutable $start, DateTimeImmutable $end): bool
    {
        if (!is_string($date) || $date === '') {
            return false;
        }

        $value = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $value !== false && $value >= $start && $value < $end;
    }
}
