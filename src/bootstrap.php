<?php
/**
 * Bootstrap aplikasi: konfigurasi, error handling, helper, repository, sesi.
 */
declare(strict_types=1);

$configFile = dirname(__DIR__) . '/config/config.php';
if (!is_file($configFile)) {
    $message = "File config/config.php belum ada. Salin dari config/config.example.php:\n"
        . "  cp config/config.example.php config/config.php\n";
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message);
    } else {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit(1);
}
require_once $configFile;

date_default_timezone_set('Asia/Jakarta');
mb_internal_encoding('UTF-8');

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

foreach (glob(__DIR__ . '/lib/*.php') as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . '/repositories/*.php') as $file) {
    require_once $file;
}

if (PHP_SAPI !== 'cli') {
    start_session();

    set_exception_handler(function (Throwable $e): void {
        error_log((string) $e);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $message = 'Terjadi kesalahan. Silakan coba lagi.';
        if (APP_ENV === 'development') {
            $message .= ' [' . get_class($e) . ': ' . $e->getMessage() . ']';
        }
        try {
            render('public/error', ['title' => 'Terjadi kesalahan', 'message' => $message], 'public', 500);
        } catch (Throwable) {
            http_response_code(500);
            echo e($message);
        }
    });
}
