<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

final class SnapshotRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $driver)
    {
    }

    public function save(array $data): void
    {
        $columns = 'recorded_on, trade_republic, long_term, home_portfolio, bitcoin, bbva, funds_done, bitcoin_done, created_at';
        $values = ':recorded_on, :trade_republic, :long_term, :home_portfolio, :bitcoin, :bbva, :funds_done, :bitcoin_done, :created_at';
        $fields = ['trade_republic', 'long_term', 'home_portfolio', 'bitcoin', 'bbva', 'funds_done', 'bitcoin_done', 'created_at'];
        $updates = $this->driver === 'mysql'
            ? implode(', ', array_map(static fn (string $field): string => "{$field} = VALUES({$field})", $fields))
            : implode(', ', array_map(static fn (string $field): string => "{$field} = excluded.{$field}", $fields));
        $conflict = $this->driver === 'mysql' ? ' ON DUPLICATE KEY UPDATE ' . $updates : ' ON CONFLICT(recorded_on) DO UPDATE SET ' . $updates;

        $statement = $this->pdo->prepare("INSERT INTO financial_snapshots ({$columns}) VALUES ({$values}){$conflict}");
        $statement->execute([
            'recorded_on' => $data['recorded_on'],
            'trade_republic' => $data['trade_republic'],
            'long_term' => $data['long_term'],
            'home_portfolio' => $data['home_portfolio'],
            'bitcoin' => $data['bitcoin'],
            'bbva' => $data['bbva'],
            'funds_done' => !empty($data['funds_done']) ? 1 : 0,
            'bitcoin_done' => !empty($data['bitcoin_done']) ? 1 : 0,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function latest(): ?array
    {
        $row = $this->pdo->query('SELECT * FROM financial_snapshots ORDER BY recorded_on DESC, id DESC LIMIT 1')->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM financial_snapshots ORDER BY recorded_on ASC, id ASC')->fetchAll();
    }
}
