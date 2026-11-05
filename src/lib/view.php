<?php
/**
 * Helper tampilan: escape, URL, render template, redirect, flash, isian lama.
 */
declare(strict_types=1);

/**
 * Escape teks untuk output HTML (wajib untuk semua data dinamis).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL absolut dari path aplikasi, mis. url('/admin') → http://localhost:8080/admin
 */
function url(string $path = '/'): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * URL file statis di folder public/, mis. asset('assets/css/admin.css').
 */
function asset(string $path): string
{
    return url('/' . ltrim($path, '/'));
}

/**
 * Render template src/views/{view}.php, opsional dibungkus layout src/views/layouts/{layout}.php.
 * Variabel $content berisi hasil view di dalam layout.
 */
function render(string $view, array $data = [], ?string $layout = null, int $status = 200): void
{
    http_response_code($status);

    $content = render_partial($view, $data);

    if ($layout === null) {
        echo $content;
        return;
    }

    echo render_partial('layouts/' . $layout, $data + ['content' => $content]);
}

/**
 * Render template dan kembalikan HTML-nya sebagai string (dipakai juga untuk partial).
 */
function render_partial(string $view, array $data = []): string
{
    $file = dirname(__DIR__) . '/views/' . $view . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("View tidak ditemukan: {$view}");
    }

    extract($data, EXTR_SKIP);
    ob_start();
    try {
        include $file;
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    return (string) ob_get_clean();
}

/**
 * Sertakan partial komponen dari src/views/partials/.
 */
function partial(string $name, array $data = []): string
{
    return render_partial('partials/' . $name, $data);
}

/**
 * Redirect 302 ke path aplikasi lalu hentikan eksekusi.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path), true, 302);
    exit;
}

/**
 * Simpan pesan flash (tampil sekali). Tipe: success, error, info.
 */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus semua pesan flash.
 */
function take_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

/**
 * Simpan isian form untuk ditampilkan ulang pada request berikutnya.
 */
function with_old(array $input): void
{
    $_SESSION['_old'] = $input;
}

/**
 * Nilai isian lama (dari with_old) — dihapus setelah dibaca.
 */
function old(string $key, mixed $default = ''): mixed
{
    if (!isset($_SESSION['_old']) || !array_key_exists($key, $_SESSION['_old'])) {
        return $default;
    }
    $value = $_SESSION['_old'][$key];
    unset($_SESSION['_old'][$key]);
    if ($_SESSION['_old'] === []) {
        unset($_SESSION['_old']);
    }
    return $value;
}

/**
 * Format waktu "05 Nov 2026, 10:12" dengan nama bulan Indonesia.
 */
function format_datetime(?string $timestamp): string
{
    if ($timestamp === null || $timestamp === '') {
        return '';
    }
    $months = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $time = strtotime($timestamp);
    if ($time === false) {
        return $timestamp;
    }
    return date('d', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y, H:i', $time);
}

/**
 * Dua huruf inisial nama usaha untuk pengganti logo, mis. "Warung Bu Sri" → "WB".
 */
function initials(string $name): string
{
    $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $letters = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $letters .= mb_substr($word, 0, 1);
    }
    if (mb_strlen($letters) < 2 && isset($words[0])) {
        $letters = mb_substr($words[0], 0, 2);
    }
    return mb_strtoupper($letters);
}

/**
 * Bangun query string dari parameter saat ini + perubahan (untuk pagination/filter).
 */
function query_with(array $changes): string
{
    $params = array_merge($_GET, $changes);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return $params ? '?' . http_build_query($params) : '';
}
