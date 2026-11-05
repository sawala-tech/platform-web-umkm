<?php
/**
 * Frame Admin/Login (tanpa layout admin).
 * @var string|null $error
 * @var string      $username
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — Portofolio UMKM</title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/logo-icon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="login">
    <section class="login-hero">
        <div class="login-hero-brand"><img src="<?= e(asset('assets/img/logo-lockup-dark.svg')) ?>" alt="Portofolio UMKM" height="40"></div>
        <div class="login-hero-bottom">
            <h1>Kelola portofolio UMKM dalam satu dashboard.</h1>
        </div>
    </section>

    <section class="login-panel">
        <form class="login-form" method="post" action="<?= e(url('/admin/login')) ?>" novalidate>
            <?= csrf_field() ?>

            <?php foreach (take_flashes() as $flash): ?>
                <?= partial('alert', ['type' => $flash['type'], 'message' => $flash['message']]) ?>
            <?php endforeach; ?>
            <?php if (!empty($error)): ?>
                <?= partial('alert', ['type' => 'error', 'message' => $error]) ?>
            <?php endif; ?>

            <h2 class="t-h2">Masuk ke dashboard</h2>
            <p class="t-small t-muted">Gunakan akun admin yang terdaftar.</p>

            <?= partial('input', [
                'name'        => 'username',
                'label'       => 'Username',
                'value'       => $username ?? '',
                'placeholder' => 'Masukkan username',
                'attrs'       => ['autocomplete' => 'username', 'required' => true, 'autofocus' => true, 'maxlength' => 50],
            ]) ?>
            <?= partial('input', [
                'name'        => 'password',
                'label'       => 'Password',
                'type'        => 'password',
                'placeholder' => 'Masukkan password',
                'attrs'       => ['autocomplete' => 'current-password', 'required' => true],
            ]) ?>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </section>
</body>
</html>
