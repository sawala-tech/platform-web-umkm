<?php
/**
 * Query tabel site_profile (Portofolio UMKM).
 */
declare(strict_types=1);

class PortfolioRepository
{
    private const COLUMNS = 'id, business_name, slug, bio, logo_path, whatsapp, status, updated_at';

    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT ' . self::COLUMNS . ' FROM site_profile WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . " FROM site_profile WHERE slug = ? AND status = 'published'"
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    /** Apakah slug sudah dipakai portofolio lain (abaikan $ignoreId saat edit). */
    public function slugExists(string $slug, int $ignoreId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM site_profile WHERE slug = ? AND id <> ? LIMIT 1');
        $stmt->execute([$slug, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    public function insert(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO site_profile (business_name, slug, bio, logo_path, whatsapp, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['business_name'],
            $data['slug'],
            $data['bio'] ?? null,
            $data['logo_path'] ?? null,
            $data['whatsapp'] ?? null,
            $data['status'] ?? 'draft',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE site_profile
             SET business_name = ?, slug = ?, bio = ?, logo_path = ?, whatsapp = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );
        $stmt->execute([
            $data['business_name'],
            $data['slug'],
            $data['bio'],
            $data['logo_path'],
            $data['whatsapp'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM site_profile WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['draft', 'published'], true)) {
            throw new InvalidArgumentException("Status tidak valid: {$status}");
        }
        $stmt = $this->pdo->prepare('UPDATE site_profile SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    /** Jumlah portofolio: total, published, draft. */
    public function counts(): array
    {
        $row = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'published'), 0) AS published,
                    COALESCE(SUM(status = 'draft'), 0) AS draft
             FROM site_profile"
        )->fetch();

        return [
            'total'     => (int) $row['total'],
            'published' => (int) $row['published'],
            'draft'     => (int) $row['draft'],
        ];
    }

    /**
     * Daftar dengan pencarian (nama usaha/slug), filter status, dan pagination.
     */
    public function paginate(?string $q, ?string $status, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if ($q !== null && $q !== '') {
            $like = '%' . addcslashes($q, '\\%_') . '%';
            $where[] = '(business_name LIKE ? OR slug LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }
        if ($status === 'draft' || $status === 'published') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM site_profile {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT ' . self::COLUMNS . " FROM site_profile {$whereSql}
             ORDER BY updated_at DESC, id DESC LIMIT ? OFFSET ?"
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
