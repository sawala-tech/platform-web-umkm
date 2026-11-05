<?php
/**
 * Pagination "← Sebelumnya 1 2 3 Berikutnya →" yang mempertahankan query string.
 * @var int $page
 * @var int $pages
 */
if (($pages ?? 1) <= 1) {
    return;
}
$window = 2;
$start = max(1, $page - $window);
$end = min($pages, $page + $window);
?>
<nav class="pagination" aria-label="Pagination">
    <?php if ($page > 1): ?>
        <a href="<?= e(query_with(['page' => $page - 1])) ?>">← Sebelumnya</a>
    <?php else: ?>
        <span class="is-disabled">← Sebelumnya</span>
    <?php endif; ?>

    <?php if ($start > 1): ?>
        <a href="<?= e(query_with(['page' => 1])) ?>">1</a>
        <?php if ($start > 2): ?><span>…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="is-current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= e(query_with(['page' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($end < $pages): ?>
        <?php if ($end < $pages - 1): ?><span>…</span><?php endif; ?>
        <a href="<?= e(query_with(['page' => $pages])) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
        <a href="<?= e(query_with(['page' => $page + 1])) ?>">Berikutnya →</a>
    <?php else: ?>
        <span class="is-disabled">Berikutnya →</span>
    <?php endif; ?>
</nav>
