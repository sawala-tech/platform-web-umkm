<?php
/**
 * Query tabel admins dan login_attempts.
 */
declare(strict_types=1);

class AdminRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, username, password_hash FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, username, password_hash FROM admins WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Daftar admin (id, name) untuk filter log. */
    public function all(): array
    {
        return $this->pdo->query('SELECT id, name FROM admins ORDER BY name')->fetchAll();
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }
}

class LoginAttemptRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Jumlah percobaan gagal untuk username dalam $seconds detik terakhir. */
    public function countRecentFailures(string $username, int $seconds): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE username = ? AND attempted_at > (NOW() - INTERVAL ? SECOND)'
        );
        $stmt->execute([$username, $seconds]);
        return (int) $stmt->fetchColumn();
    }

    public function recordFailure(string $username): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO login_attempts (username) VALUES (?)');
        $stmt->execute([mb_substr($username, 0, 50)]);
    }

    public function clear(string $username): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM login_attempts WHERE username = ?');
        $stmt->execute([$username]);
    }

    public function purgeOlderThan(int $seconds): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL ? SECOND)');
        $stmt->execute([$seconds]);
    }
}
