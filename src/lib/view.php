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
    $rel  = '/' . ltrim($path, '/');
    $file = dirname(__DIR__, 2) . '/public' . $rel;
    // Versi berdasarkan waktu modifikasi agar browser tidak memakai salinan lama dari cache.
    $v    = is_file($file) ? '?v=' . filemtime($file) : '';
    return url($rel) . $v;
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

/**
 * Ikon inline SVG dari Lucide (https://lucide.dev, lisensi ISC) — disimpan lokal, tanpa CDN/JS.
 * Nama kanal kontak dipetakan ke ikon terdekat; nama tak dikenal → globe.
 */
function icon(string $name, int $size = 20): string
{
    static $aliases = [
        'whatsapp'  => 'message-circle',
        'tiktok'    => 'music-2',
        'shopee'    => 'shopping-bag',
        'tokopedia' => 'store',
        'link'      => 'globe',
    ];
    static $icons = [
        'message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
        'instagram'      => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
        'music-2'        => '<circle cx="8" cy="18" r="4"/><path d="M12 18V2l7 4"/>',
        'shopping-bag'   => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'store'          => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
        'globe'          => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        'arrow-up-right' => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
        'x'              => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'chevron-left'   => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right'  => '<path d="m9 18 6-6-6-6"/>',
        'download'       => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
        'zoom-in'        => '<circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/>',
        'zoom-out'       => '<circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="8" x2="14" y1="11" y2="11"/>',
    ];
    $key = $aliases[$name] ?? $name;
    $body = $icons[$key] ?? $icons['globe'];
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"'
        . ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true" focusable="false">' . $body . '</svg>';
}
