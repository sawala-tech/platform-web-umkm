<?php
/**
 * Layout admin: sidebar kiri + area konten (frame Admin/Dashboard).
 * @var string      $content
 * @var string|null $title
 * @var string      $active  dashboard | portfolios | logs | password
 */
$admin = current_admin();
$active = $active ?? '';
$menu = [
    'dashboard'  => ['Dashboard', '/admin'],
    'portfolios' => ['Portofolio UMKM', '/admin/portfolios'],
    'logs'       => ['Log Aktivitas', '/admin/logs'],
    'password'   => ['Ubah Password', '/admin/password'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e(($title ?? 'Admin') . ' — Portofolio UMKM') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-title">Portofolio UMKM</div>
            <div class="sidebar-subtitle">Panel admin</div>
        </div>
        <nav class="sidebar-nav" aria-label="Menu admin">
            <?php foreach ($menu as $key => [$label, $path]): ?>
                <a href="<?= e(url($path)) ?>" class="sidebar-link<?= $active === $key ? ' is-active' : '' ?>"
                    <?= $active === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
            <form method="post" action="<?= e(url('/admin/logout')) ?>" class="sidebar-logout">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-link sidebar-link-danger">Logout</button>
            </form>
        </nav>
        <?php if ($admin !== null): ?>
            <div class="sidebar-user t-small">
                <span class="t-muted">Masuk sebagai</span>
                <strong><?= e($admin['name']) ?></strong>
            </div>
        <?php endif; ?>
    </aside>

    <main class="content">
        <?= partial('flashes') ?>
        <?= $content ?>
    </main>

    <script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
    <?php if (!empty($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= e(asset($script)) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
