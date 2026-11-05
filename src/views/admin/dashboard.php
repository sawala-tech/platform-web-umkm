<?php
/**
 * Frame Admin/Dashboard (3 kartu stat: Total, Published, Draft).
 * @var array $counts
 * @var array $logs
 */
?>
<?= partial('page_header', [
    'heading'  => 'Dashboard',
    'subtitle' => 'Ringkasan portofolio dan aktivitas terbaru.',
    'actions'  => partial('button', ['label' => 'Tambah Portofolio', 'href' => url('/admin/portfolios/create')]),
]) ?>

<section class="stats" aria-label="Ringkasan portofolio">
    <?= partial('stat_card', ['label' => 'Total portofolio', 'value' => $counts['total']]) ?>
    <?= partial('stat_card', ['label' => 'Published', 'value' => $counts['published']]) ?>
    <?= partial('stat_card', ['label' => 'Draft', 'value' => $counts['draft']]) ?>
</section>

<section>
    <h2 class="t-h3 section-title">Aktivitas terbaru</h2>
    <?= partial('log_table', ['logs' => $logs]) ?>
</section>
