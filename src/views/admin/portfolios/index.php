<?php
/**
 * Frame Admin/Portofolio - Daftar.
 * @var string $q
 * @var string $status
 * @var array  $result  items, total, page, pages, per_page
 */
$items = $result['items'];
$from = $result['total'] === 0 ? 0 : ($result['page'] - 1) * $result['per_page'] + 1;
$to = $from === 0 ? 0 : $from + count($items) - 1;
$isFiltered = $q !== '' || $status !== '';
?>
<?= partial('page_header', [
    'heading'  => 'Portofolio UMKM',
    'subtitle' => 'Kelola profil usaha yang ditampilkan di halaman publik.',
    'actions'  => partial('button', ['label' => 'Tambah Portofolio', 'href' => url('/admin/portfolios/create')]),
]) ?>

<form class="filters" method="get" action="<?= e(url('/admin/portfolios')) ?>" role="search">
    <label class="sr-only" for="f-q">Cari</label>
    <input class="input input-search" id="f-q" type="search" name="q" value="<?= e($q) ?>"
        placeholder="Cari nama usaha atau slug">
    <label class="sr-only" for="f-status">Status</label>
    <select class="input" id="f-status" name="status" onchange="this.form.submit()">
        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua status</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
    </select>
    <button type="submit" class="btn btn-secondary">Cari</button>
    <?php if ($isFiltered): ?>
        <a class="link-action link-action-muted" href="<?= e(url('/admin/portfolios')) ?>">Reset</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Nama usaha</th>
                <th scope="col">Slug</th>
                <th scope="col">WhatsApp</th>
                <th scope="col">Status</th>
                <th scope="col">Diperbarui</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$items): ?>
                <tr>
                    <td colspan="6" class="table-empty">
                        <?php if ($isFiltered): ?>
                            Tidak ada portofolio yang cocok dengan pencarian.
                        <?php else: ?>
                            Belum ada portofolio. <a class="link-action" href="<?= e(url('/admin/portfolios/create')) ?>">Tambahkan portofolio pertama.</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php foreach ($items as $p): ?>
                <?php $isPublished = $p['status'] === 'published'; ?>
                <tr>
                    <td class="cell-strong"><?= e($p['business_name']) ?></td>
                    <td class="cell-muted"><?= e($p['slug']) ?></td>
                    <td><?= $p['whatsapp'] ? e(format_whatsapp_display($p['whatsapp'])) : '<span class="cell-muted">—</span>' ?></td>
                    <td><?= partial('badge', [
                        'variant' => $isPublished ? 'published' : 'draft',
                        'label'   => $isPublished ? 'Published' : 'Draft',
                    ]) ?></td>
                    <td class="cell-muted" style="white-space:nowrap"><?= e(format_datetime($p['updated_at'])) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="link-action" href="<?= e(url('/admin/portfolios/' . $p['id'] . '/edit')) ?>">Edit</a>
                            <?php if ($isPublished): ?>
                                <a class="link-action" href="<?= e(url('/' . $p['slug'])) ?>" target="_blank" rel="noopener">Lihat halaman</a>
                                <form method="post" action="<?= e(url('/admin/portfolios/' . $p['id'] . '/unpublish')) ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-action link-action-muted">Unpublish</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= e(url('/admin/portfolios/' . $p['id'] . '/publish')) ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-action">Publish</button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="link-action link-action-danger"
                                data-delete-action="<?= e(url('/admin/portfolios/' . $p['id'] . '/delete')) ?>"
                                data-delete-name="<?= e($p['business_name']) ?>"
                                data-delete-slug="/<?= e($p['slug']) ?>">Hapus</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="list-footer">
    <span class="t-small t-muted">Menampilkan <?= e((string) $from) ?>–<?= e((string) $to) ?> dari <?= e((string) $result['total']) ?> portofolio</span>
    <?= partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]) ?>
</div>

<?= partial('modal_confirm') ?>
