<?php
/**
 * Log aktivitas (US5) — hanya baca.
 */
declare(strict_types=1);

function valid_date_param(mixed $value): ?string
{
    if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
        return null;
    }
    [$y, $m, $d] = array_map('intval', explode('-', $value));
    return checkdate($m, $d, $y) ? $value : null;
}

function logs_index(): void
{
    require_admin();

    $from = valid_date_param($_GET['from'] ?? null);
    $to = valid_date_param($_GET['to'] ?? null);
    $adminIdRaw = $_GET['admin_id'] ?? '';
    $adminId = is_string($adminIdRaw) && ctype_digit($adminIdRaw) ? (int) $adminIdRaw : null;
    $page = max(1, (int) ($_GET['page'] ?? 1));

    $pdo = db();
    render('admin/logs', [
        'title'   => 'Log Aktivitas',
        'active'  => 'logs',
        'result'  => (new ActivityLogRepository($pdo))->paginate($page, PER_PAGE, $from, $to, $adminId),
        'admins'  => (new AdminRepository($pdo))->all(),
        'from'    => $from,
        'to'      => $to,
        'adminId' => $adminId,
    ], 'admin');
}
