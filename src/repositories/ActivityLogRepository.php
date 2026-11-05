<?php
/**
 * Query baca tabel activity_logs. Append-only: penulisan hanya lewat log_activity(),
 * dan sengaja tidak ada method update/delete.
 */
declare(strict_types=1);

class ActivityLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function latest(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.id, l.action, l.description, l.created_at, a.name AS admin_name
             FROM activity_logs l JOIN admins a ON a.id = l.admin_id
             ORDER BY l.created_at DESC, l.id DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * @param string|null $from YYYY-MM-DD (inklusif)
     * @param string|null $to   YYYY-MM-DD (inklusif)
     */
    public function paginate(int $page, int $perPage, ?string $from, ?string $to, ?int $adminId): array
    {
        $where = [];
        $params = [];

        if ($from !== null) {
            $where[] = 'l.created_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        if ($to !== null) {
            $where[] = 'l.created_at <= ?';
            $params[] = $to . ' 23:59:59';
        }
        if ($adminId !== null) {
            $where[] = 'l.admin_id = ?';
            $params[] = $adminId;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM activity_logs l {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT l.id, l.action, l.description, l.created_at, a.name AS admin_name
             FROM activity_logs l JOIN admins a ON a.id = l.admin_id
             {$whereSql}
             ORDER BY l.created_at DESC, l.id DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, $perPage, $offset]);

        return [
            'items'    => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'per_page' => $perPage,
        ];
    }
}
