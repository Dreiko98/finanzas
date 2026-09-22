<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

final class CashflowRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $driver)
    {
    }

    public function saveReview(string $date, float $balance): void
    {
        $conflict = $this->driver === 'mysql'
            ? ' ON DUPLICATE KEY UPDATE remaining_balance = VALUES(remaining_balance), created_at = VALUES(created_at)'
            : ' ON CONFLICT(reviewed_on) DO UPDATE SET remaining_balance = excluded.remaining_balance, created_at = excluded.created_at';
        $statement = $this->pdo->prepare('INSERT INTO current_account_reviews (reviewed_on, remaining_balance, created_at) VALUES (:date, :balance, :created_at)' . $conflict);
        $statement->execute([
            'date' => $date,
            'balance' => $balance,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function addIncome(string $date, string $concept, float $amount): void
    {
        $statement = $this->pdo->prepare('INSERT INTO incomes (income_date, concept, amount, created_at) VALUES (:date, :concept, :amount, :created_at)');
        $statement->execute([
            'date' => $date,
            'concept' => $concept,
            'amount' => $amount,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function reviewsForMonth(string $month): array
    {
        [$start, $end] = $this->monthBounds($month);
        $statement = $this->pdo->prepare('SELECT * FROM current_account_reviews WHERE reviewed_on >= :start AND reviewed_on < :end ORDER BY reviewed_on ASC, id ASC');
        $statement->execute(['start' => $start, 'end' => $end]);
        return $statement->fetchAll();
    }

    public function incomesForMonth(string $month): array
    {
        [$start, $end] = $this->monthBounds($month);
        $statement = $this->pdo->prepare('SELECT * FROM incomes WHERE income_date >= :start AND income_date < :end ORDER BY income_date DESC, id DESC');
        $statement->execute(['start' => $start, 'end' => $end]);
        return $statement->fetchAll();
    }

    public function latestReview(): ?array
    {
        $row = $this->pdo->query('SELECT * FROM current_account_reviews ORDER BY reviewed_on DESC, id DESC LIMIT 1')->fetch();
        return $row ?: null;
    }

    private function monthBounds(string $month): array
    {
        $start = new DateTimeImmutable($month . '-01');
        return [$start->format('Y-m-d'), $start->modify('first day of next month')->format('Y-m-d')];
    }
}

