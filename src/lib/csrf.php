<?php
/**
 * Proteksi CSRF: satu token per sesi untuk semua form POST.
 */
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Hentikan request bila token CSRF tidak cocok.
 */
function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $expected = $_SESSION['_csrf'] ?? '';

    if (!is_string($sent) || $expected === '' || !hash_equals($expected, $sent)) {
        render('public/error', [
            'title'   => 'Permintaan ditolak',
            'message' => 'Sesi formulir kedaluwarsa, silakan coba lagi',
        ], 'public', 400);
        exit;
    }
}
