<?php
/**
 * Tabel log aktivitas (dipakai Dashboard & Log Aktivitas). Tidak ada aksi ubah/hapus.
 * @var array $logs
 */
?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Waktu</th>
                <th scope="col">Admin</th>
                <th scope="col">Aksi</th>
                <th scope="col">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$logs): ?>
                <tr><td colspan="4" class="table-empty">Belum ada aktivitas.</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="cell-muted" style="white-space:nowrap"><?= e(format_datetime($log['created_at'])) ?></td>
                    <td><?= e($log['admin_name']) ?></td>
                    <td><?= partial('badge', [
                        'variant' => ACTION_BADGES[$log['action']] ?? 'draft',
                        'label'   => ACTION_LABELS[$log['action']] ?? $log['action'],
                    ]) ?></td>
                    <td><?= e($log['description']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
