<?php
/**
 * Frame Admin/Portofolio - Publish berhasil.
 * @var array $portfolio
 */
$publicUrl = url('/' . $portfolio['slug']);
?>
<?= partial('page_header', [
    'heading'  => 'Publish Portofolio',
    'subtitle' => 'Halaman publik sudah aktif.',
]) ?>

<div class="card publish-card">
    <?= partial('badge', ['variant' => 'published', 'label' => 'Published']) ?>
    <p class="t-small t-muted" style="margin:var(--space-4) 0 0">URL publik <?= e($portfolio['business_name']) ?></p>
    <p class="publish-url-text"><?= e($publicUrl) ?></p>

    <div class="publish-url">
        <label class="sr-only" for="public-url">URL publik</label>
        <input class="input" id="public-url" type="text" value="<?= e($publicUrl) ?>" readonly data-copy-source>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-secondary" data-copy-target="#public-url">Salin URL</button>
        <a class="btn btn-primary" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">Buka halaman</a>
    </div>
    <p class="t-small t-muted" style="margin:var(--space-4) 0 0">Diterbitkan <?= e(format_datetime($portfolio['updated_at'])) ?></p>
    <p style="margin:var(--space-6) 0 0"><a class="link-action" href="<?= e(url('/admin/portfolios')) ?>">← Kembali ke daftar portofolio</a></p>
</div>
