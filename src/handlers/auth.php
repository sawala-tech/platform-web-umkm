<?php
/**
 * Login & logout Admin (US1).
 */
declare(strict_types=1);

function show_login(): void
{
    if (current_admin() !== null) {
        redirect('/admin');
    }
    header('Cache-Control: no-store');
    render('admin/login', ['error' => null, 'username' => '']);
}

function do_login(): void
{
    verify_csrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $pdo = db();
    $attempts = new LoginAttemptRepository($pdo);
    $view = ['username' => $username];

    if ($attempts->countRecentFailures($username, LOGIN_LOCK_SECONDS) >= LOGIN_MAX_ATTEMPTS) {
        render('admin/login', $view + ['error' => 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.'], null, 429);
        return;
    }

    $admins = new AdminRepository($pdo);
    $admin = $username !== '' ? $admins->findByUsername($username) : null;

    if ($admin === null || $password === '' || !password_verify($password, $admin['password_hash'])) {
        $attempts->recordFailure($username);
        render('admin/login', $view + ['error' => 'Username atau password salah.'], null, 422);
        return;
    }

    $attempts->clear($username);
    $attempts->purgeOlderThan(86400);

    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        $admins->updatePasswordHash((int) $admin['id'], password_hash($password, PASSWORD_DEFAULT));
    }

    login_admin($admin);
    redirect('/admin');
}

function do_logout(): void
{
    require_admin();
    verify_csrf();

    logout_admin();
    start_session();
    flash('info', 'Anda telah keluar.');
    redirect('/admin/login');
}
