<?php
/**
 * Dashboard Admin (US1 + ringkasan US5).
 */
declare(strict_types=1);

function show_dashboard(): void
{
    require_admin();

    $pdo = db();
    render('admin/dashboard', [
        'title'  => 'Dashboard',
        'active' => 'dashboard',
        'counts' => (new PortfolioRepository($pdo))->counts(),
        'logs'   => (new ActivityLogRepository($pdo))->latest(5),
    ], 'admin');
}
