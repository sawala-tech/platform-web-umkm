<?php
/**
 * Sesi & autentikasi Admin (Prinsip III).
 */
declare(strict_types=1);

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('umkm_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Data Admin yang sedang login, atau null.
 */
function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return [
        'id'       => (int) $_SESSION['admin_id'],
        'name'     => (string) ($_SESSION['admin_name'] ?? ''),
        'username' => (string) ($_SESSION['admin_username'] ?? ''),
    ];
}

/**
 * Wajib dipanggil di awal setiap handler Admin. Mengembalikan data Admin yang login.
 */
function require_admin(): array
{
    $admin = current_admin();

    if ($admin === null) {
        redirect('/admin/login');
    }

    $last = (int) ($_SESSION['last_activity'] ?? 0);
    if (time() - $last > SESSION_TIMEOUT) {
        logout_admin();
        start_session();
        flash('error', 'Sesi berakhir, silakan login kembali');
        redirect('/admin/login');
    }

    $_SESSION['last_activity'] = time();

    // Jangan simpan halaman admin di cache agar tombol Back setelah logout tidak menampilkannya.
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    return $admin;
}

function login_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $admin['id'];
    $_SESSION['admin_name']     = (string) $admin['name'];
    $_SESSION['admin_username'] = (string) $admin['username'];
    $_SESSION['last_activity']  = time();
}

function logout_admin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
