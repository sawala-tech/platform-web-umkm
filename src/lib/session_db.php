<?php
/**
 * Penyimpanan sesi di database (tabel `sessions`).
 *
 * Diaktifkan hanya bila env SESSION_DRIVER=db (lihat start_session() di auth.php),
 * agar sesi tetap konsisten saat aplikasi berjalan di banyak instance (Wasmer Edge).
 * Penyimpanan default PHP (berbasis file) tetap dipakai di XAMPP/lokal.
 */
declare(strict_types=1);

final class DbSessionHandler implements SessionHandlerInterface
{
    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = db()->prepare('SELECT data FROM sessions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? '' : (string) $row['data'];
    }

    public function write(string $id, string $data): bool
    {
        $stmt = db()->prepare(
            'INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), last_activity = VALUES(last_activity)'
        );
        $stmt->execute([$id, $data, time()]);

        return true;
    }

    public function destroy(string $id): bool
    {
        $stmt = db()->prepare('DELETE FROM sessions WHERE id = ?');
        $stmt->execute([$id]);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = db()->prepare('DELETE FROM sessions WHERE last_activity < ?');
        $stmt->execute([time() - $max_lifetime]);

        return $stmt->rowCount();
    }
}
