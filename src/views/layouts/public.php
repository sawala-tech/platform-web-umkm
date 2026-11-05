<?php
/**
 * Layout publik: header "Portofolio UMKM" + garis gradien, konten, footer.
 * @var string      $content
 * @var string|null $title
 * @var string|null $metaDescription
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Portofolio UMKM') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/logo-icon.svg')) ?>">
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/public.css')) ?>">
</head>
<body class="public">
    <header class="site-header">
        <div class="site-header-inner">
            <a class="site-brand" href="<?= e(url('/')) ?>"><img src="<?= e(asset('assets/img/logo-lockup.svg')) ?>" alt="Portofolio UMKM" height="40"></a>
        </div>
    </header>
    <div class="brand-rule" aria-hidden="true"></div>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="site-footer-inner t-small t-muted">© <?= e(date('Y')) ?> Portofolio UMKM</div>
        <div class="brand-rule" aria-hidden="true"></div>
    </footer>
</body>
</html>
