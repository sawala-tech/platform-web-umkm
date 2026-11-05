<?php
/**
 * Front controller: semua permintaan non-file diteruskan ke sini (lihat .htaccess).
 */
declare(strict_types=1);

// Dukungan `php -S localhost:8080 -t public public/index.php`: sajikan file statis apa adanya.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($file !== __FILE__ && is_file($file)) {
        // Jangan pernah mengeksekusi PHP dari folder upload.
        if (str_contains($file, '/uploads/') && preg_match('/\.(php|phtml|phar)$/i', $file)) {
            http_response_code(403);
            exit;
        }
        return false;
    }
}

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/router.php';

foreach (glob(__DIR__ . '/../src/handlers/*.php') as $handlerFile) {
    require_once $handlerFile;
}

// Publik
route('GET', '/', 'home');

// Autentikasi (US1)
route('GET', '/admin/login', 'show_login');
route('POST', '/admin/login', 'do_login');
route('POST', '/admin/logout', 'do_logout');
route('GET', '/admin', 'show_dashboard');

// Portofolio (US2, US4)
route('GET', '/admin/portfolios', 'portfolios_index');
route('GET', '/admin/portfolios/create', 'portfolios_create');
route('POST', '/admin/portfolios', 'portfolios_store');
route('GET', '/admin/portfolios/{id}/edit', 'portfolios_edit');
route('POST', '/admin/portfolios/{id}', 'portfolios_update');
route('POST', '/admin/portfolios/{id}/delete', 'portfolios_destroy');
route('POST', '/admin/portfolios/{id}/publish', 'portfolios_publish');
route('POST', '/admin/portfolios/{id}/unpublish', 'portfolios_unpublish');

// Log aktivitas (US5)
route('GET', '/admin/logs', 'logs_index');

// Ubah password (US6)
route('GET', '/admin/password', 'password_show');
route('POST', '/admin/password', 'password_update');

// Halaman portofolio publik — WAJIB paling akhir
route('GET', '/{slug}', 'show_portfolio');

dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
