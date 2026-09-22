<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

final class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public function __construct(
        private readonly PDO $pdo,
        private readonly ?string $email,
        private readonly ?string $passwordHash,
    ) {
    }

    public function configured(): bool
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false
            && is_string($this->passwordHash)
            && password_get_info($this->passwordHash)['algo'] !== null;
    }

    public function check(): bool
    {
        return isset($_SESSION['authenticated'], $_SESSION['auth_email'])
            && $_SESSION['authenticated'] === true
            && is_string($this->email)
            && hash_equals($this->email, (string) $_SESSION['auth_email']);
    }

    public function tooManyAttempts(string $ip): bool
    {
        $this->pruneAttempts();
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_hash = :ip AND attempted_at >= :since');
        $statement->execute([
            'ip' => $this->hashIp($ip),
            'since' => (new DateTimeImmutable('-' . self::WINDOW_MINUTES . ' minutes'))->format('Y-m-d H:i:s'),
        ]);

        return (int) $statement->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public function attempt(string $email, string $password, string $ip): bool
    {
        if (!$this->configured() || $this->tooManyAttempts($ip)) {
            return false;
        }

        $valid = hash_equals(strtolower((string) $this->email), strtolower(trim($email)))
            && password_verify($password, (string) $this->passwordHash);

        if (!$valid) {
            $statement = $this->pdo->prepare('INSERT INTO login_attempts (ip_hash, attempted_at) VALUES (:ip, :at)');
            $statement->execute([
                'ip' => $this->hashIp($ip),
                'at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
            return false;
        }

        $statement = $this->pdo->prepare('DELETE FROM login_attempts WHERE ip_hash = :ip');
        $statement->execute(['ip' => $this->hashIp($ip)]);
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['auth_email'] = $this->email;

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private function pruneAttempts(): void
    {
        $statement = $this->pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < :expired');
        $statement->execute(['expired' => (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s')]);
    }

    private function hashIp(string $ip): string
    {
        return hash('sha256', $ip);
    }
}
