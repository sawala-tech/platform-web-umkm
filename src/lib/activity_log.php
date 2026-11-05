<?php
/**
 * Pencatatan log aktivitas Admin (Prinsip IV). Append-only.
 */
declare(strict_types=1);

const ACTION_CREATE_PORTFOLIO    = 'create_portfolio';
const ACTION_UPDATE_PORTFOLIO    = 'update_portfolio';
const ACTION_DELETE_PORTFOLIO    = 'delete_portfolio';
const ACTION_PUBLISH_PORTFOLIO   = 'publish_portfolio';
const ACTION_UNPUBLISH_PORTFOLIO = 'unpublish_portfolio';
const ACTION_CHANGE_PASSWORD     = 'change_password';

/** Label tampilan per aksi (sesuai badge desain). */
const ACTION_LABELS = [
    ACTION_CREATE_PORTFOLIO    => 'Tambah',
    ACTION_UPDATE_PORTFOLIO    => 'Update',
    ACTION_DELETE_PORTFOLIO    => 'Delete',
    ACTION_PUBLISH_PORTFOLIO   => 'Published',
    ACTION_UNPUBLISH_PORTFOLIO => 'Draft',
    ACTION_CHANGE_PASSWORD     => 'Update',
];

/** Varian badge per aksi. */
const ACTION_BADGES = [
    ACTION_CREATE_PORTFOLIO    => 'log-update',
    ACTION_UPDATE_PORTFOLIO    => 'log-update',
    ACTION_DELETE_PORTFOLIO    => 'log-delete',
    ACTION_PUBLISH_PORTFOLIO   => 'published',
    ACTION_UNPUBLISH_PORTFOLIO => 'draft',
    ACTION_CHANGE_PASSWORD     => 'log-update',
];

/**
 * Catat satu aktivitas. WAJIB dipanggil di dalam transaction() yang sama dengan perubahan data
 * agar data dan log berhasil/batal bersama.
 */
function log_activity(PDO $pdo, int $adminId, string $action, string $description): void
{
    if (!array_key_exists($action, ACTION_LABELS)) {
        throw new InvalidArgumentException("Aksi log tidak dikenal: {$action}");
    }

    $stmt = $pdo->prepare('INSERT INTO activity_logs (admin_id, action, description) VALUES (?, ?, ?)');
    $stmt->execute([$adminId, $action, $description]);
}
