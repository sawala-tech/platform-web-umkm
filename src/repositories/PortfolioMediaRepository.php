<?php
/**
 * Query tabel portfolio_images (galeri produk) dan portfolio_links (link kontak tambahan).
 */
declare(strict_types=1);

if (!defined('LINKS_MAX')) {
    define('LINKS_MAX', 5);
}

class PortfolioMediaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /* ---------- Galeri ---------- */

    /** @return array<int, array{id:int, image_path:string, sort_order:int}> */
    public function images(int $portfolioId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, image_path, sort_order FROM portfolio_images
             WHERE portfolio_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$portfolioId]);
        return $stmt->fetchAll();
    }

    /** @param string[] $paths */
    public function addImages(int $portfolioId, array $paths): void
    {
        if (!$paths) {
            return;
        }
        $max = $this->pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM portfolio_images WHERE portfolio_id = ?');
        $max->execute([$portfolioId]);
        $order = (int) $max->fetchColumn();

        $stmt = $this->pdo->prepare(
            'INSERT INTO portfolio_images (portfolio_id, image_path, sort_order) VALUES (?, ?, ?)'
        );
        foreach ($paths as $path) {
            $stmt->execute([$portfolioId, $path, ++$order]);
        }
    }

    /**
     * Hapus foto milik portofolio ini berdasarkan id.
     * @param int[] $ids
     * @return string[] path file yang barisnya terhapus (file dihapus pemanggil setelah commit)
     */
    public function deleteImages(int $portfolioId, array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn(int $id) => $id > 0)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $select = $this->pdo->prepare(
            "SELECT image_path FROM portfolio_images WHERE portfolio_id = ? AND id IN ({$placeholders})"
        );
        $select->execute([$portfolioId, ...$ids]);
        $paths = $select->fetchAll(PDO::FETCH_COLUMN);

        $delete = $this->pdo->prepare("DELETE FROM portfolio_images WHERE portfolio_id = ? AND id IN ({$placeholders})");
        $delete->execute([$portfolioId, ...$ids]);

        return $paths;
    }

    /** @return string[] semua path foto (untuk dibersihkan saat portofolio dihapus) */
    public function imagePaths(int $portfolioId): array
    {
        $stmt = $this->pdo->prepare('SELECT image_path FROM portfolio_images WHERE portfolio_id = ?');
        $stmt->execute([$portfolioId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ---------- Link lain ---------- */

    /** @return array<int, array{label:string, url:string}> */
    public function links(int $portfolioId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT label, url FROM portfolio_links WHERE portfolio_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$portfolioId]);
        return $stmt->fetchAll();
    }

    /** @param array<int, array{label:string, url:string}> $links */
    public function replaceLinks(int $portfolioId, array $links): void
    {
        $this->pdo->prepare('DELETE FROM portfolio_links WHERE portfolio_id = ?')->execute([$portfolioId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO portfolio_links (portfolio_id, label, url, sort_order) VALUES (?, ?, ?, ?)'
        );
        foreach (array_values($links) as $i => $link) {
            $stmt->execute([$portfolioId, $link['label'], $link['url'], $i + 1]);
        }
    }
}
