<?php
/**
 * Koneksi database (PDO) dan helper transaksi.
 */
declare(strict_types=1);

/**
 * Koneksi PDO tunggal untuk seluruh request.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Samakan zona waktu MySQL dengan PHP (Asia/Jakarta) agar waktu log/diperbarui konsisten.
        $pdo->exec("SET time_zone = '+07:00'");
    }

    return $pdo;
}

/**
 * Jalankan $fn di dalam satu transaksi. Semua perubahan (termasuk log aktivitas)
 * di-commit bersama atau dibatalkan bersama bila terjadi exception.
 */
function transaction(callable $fn): mixed
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $result = $fn($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
