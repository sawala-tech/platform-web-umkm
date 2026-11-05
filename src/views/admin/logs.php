<?php
/**
 * Frame Admin/Log Aktivitas. Tanpa aksi ubah/hapus (append-only).
 * @var array       $result
 * @var array       $admins
 * @var string|null $from
 * @var string|null $to
 * @var int|null    $adminId
 */
$isFiltered = $from !== null || $to !== null || $adminId !== null;
?>
<?= partial('page_header', [
    'heading'  => 'Log Aktivitas',
    'subtitle' => 'Riwayat perubahan data oleh admin.',
]) ?>

<form class="filters" method="get" action="<?= e(url('/admin/logs')) ?>">
    <label class="t-small t-muted" for="f-from">Dari</label>
    <input class="input" id="f-from" type="date" name="from" value="<?= e($from ?? '') ?>">
    <label class="t-small t-muted" for="f-to">Sampai</label>
    <input class="input" id="f-to" type="date" name="to" value="<?= e($to ?? '') ?>">
    <label class="sr-only" for="f-admin">Admin</label>
    <select class="input" id="f-admin" name="admin_id">
        <option value="">Semua admin</option>
        <?php foreach ($admins as $a): ?>
            <option value="<?= e((string) $a['id']) ?>" <?= $adminId === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary">Terapkan</button>
    <?php if ($isFiltered): ?>
        <a class="link-action link-action-muted" href="<?= e(url('/admin/logs')) ?>">Reset</a>
    <?php endif; ?>
</form>

<?= partial('log_table', ['logs' => $result['items']]) ?>

<div class="list-footer">
    <span class="t-small t-muted"><?= e((string) $result['total']) ?> aktivitas</span>
    <?= partial('pagination', ['page' => $result['page'], 'pages' => $result['pages']]) ?>
</div>
