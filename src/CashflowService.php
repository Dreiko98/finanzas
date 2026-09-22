<?php

declare(strict_types=1);

namespace App;

final class CashflowService
{
    public function summary(array $reviews, array $incomes, array $plan): array
    {
        $incomeTotal = array_sum(array_map(static fn (array $income): float => (float) $income['amount'], $incomes));
        $spending = null;
        $remaining = null;

        if ($reviews !== []) {
            $first = (float) $reviews[0]['remaining_balance'];
            $spending = max(0, (float) $plan['current'] - $first);
            $previous = $first;
            foreach (array_slice($reviews, 1) as $review) {
                $current = (float) $review['remaining_balance'];
                $spending += max(0, $previous - $current);
                $previous = $current;
            }
            $remaining = (float) $reviews[array_key_last($reviews)]['remaining_balance'];
        }

        return [
            'income_total' => $incomeTotal,
            'income_target' => (float) $plan['salary'],
            'income_difference' => $incomeTotal - (float) $plan['salary'],
            'spending' => $spending,
            'spending_target' => (float) $plan['current'],
            'remaining' => $remaining,
        ];
    }
}
