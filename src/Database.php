<?php

declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(array $config): PDO
    {
        $driver = $config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $path = (string) $config['sqlite_path'];
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
                throw new RuntimeException('No se pudo crear el directorio de datos.');
            }
            $pdo = new PDO('sqlite:' . $path);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
        } elseif ($driver === 'mysql') {
            foreach (['host', 'name', 'user'] as $key) {
                if (empty($config[$key])) {
                    throw new RuntimeException('Falta configurar DB_' . strtoupper($key) . '.');
                }
            }
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['name']);
            $pdo = new PDO($dsn, $config['user'], $config['password'] ?? '');
        } else {
            throw new RuntimeException('El driver de base de datos no es válido.');
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        self::migrate($pdo, $driver);

        return $pdo;
    }

    private static function migrate(PDO $pdo, string $driver): void
    {
        $id = $driver === 'mysql' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $dateTime = $driver === 'mysql' ? 'DATETIME' : 'TEXT';

        $index = $driver === 'mysql' ? ', INDEX idx_login_attempts_ip_time (ip_hash, attempted_at)' : '';
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id {$id},
            ip_hash CHAR(64) NOT NULL,
            attempted_at {$dateTime} NOT NULL
            {$index}
        )");
        if ($driver === 'sqlite') {
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_attempts_ip_time ON login_attempts (ip_hash, attempted_at)');
        }
    }
}
