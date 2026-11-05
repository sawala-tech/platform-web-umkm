<?php
/**
 * Aturan validasi murni (tanpa database) — diuji oleh tests/run.php.
 */
declare(strict_types=1);

/** Slug yang dipakai sistem dan tidak boleh menjadi alamat portofolio. */
const RESERVED_SLUGS = ['admin', 'login', 'logout', 'assets', 'uploads', '404', 'index', 'index-php'];

/**
 * Ubah teks bebas menjadi slug URL-safe: "Kopi Nusantara!" → "kopi-nusantara".
 */
function normalize_slug(string $value): string
{
    $value = trim($value);

    // Transliterasi huruf beraksen (é → e): pisahkan tanda diakritik lalu buang (bila ext-intl ada),
    // kemudian iconv untuk sisa karakter non-ASCII.
    if (class_exists('Normalizer')) {
        $decomposed = Normalizer::normalize($value, Normalizer::FORM_D);
        if (is_string($decomposed)) {
            $value = preg_replace('/\p{Mn}+/u', '', $decomposed) ?? $value;
        }
    }
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($ascii === false || $ascii === '') {
        $ascii = $value;
    }
    // iconv di beberapa sistem (mis. macOS) menghasilkan "'e" / "~n": buang simbol aksennya.
    $ascii = str_replace(["'", '"', '`', '^', '~'], '', $ascii);
    $slug = strtolower($ascii);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = preg_replace('/-+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    if (strlen($slug) > 150) {
        $slug = trim(substr($slug, 0, 150), '-');
    }

    return $slug;
}

function is_valid_slug(string $slug): bool
{
    $length = strlen($slug);
    return $length >= 3 && $length <= 150
        && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
}

function is_reserved_slug(string $slug): bool
{
    return in_array($slug, RESERVED_SLUGS, true);
}

/**
 * Bersihkan & normalisasi nomor WhatsApp ke format 62xxxx. Null bila tidak valid.
 */
function normalize_whatsapp(string $value): ?string
{
    $clean = str_replace([' ', '-', '(', ')', '.'], '', trim($value));

    if (preg_match('/^\+?[0-9]{10,15}$/', $clean) !== 1) {
        return null;
    }

    $clean = ltrim($clean, '+');
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    }

    return $clean;
}

/**
 * Format tampilan nomor: 6281234567890 → 0812-3456-7890.
 */
function format_whatsapp_display(string $wa62): string
{
    $local = str_starts_with($wa62, '62') ? '0' . substr($wa62, 2) : $wa62;
    $groups = [substr($local, 0, 4)];
    $rest = substr($local, 4);
    while (strlen($rest) > 0) {
        $take = strlen($rest) <= 5 && strlen($rest) > 4 ? strlen($rest) : 4;
        $groups[] = substr($rest, 0, $take);
        $rest = substr($rest, $take);
    }
    return implode('-', array_filter($groups, fn($g) => $g !== ''));
}

/**
 * Validasi password baru. Mengembalikan pesan error per field (kosong bila valid).
 */
function validate_new_password(string $current, string $new, string $confirm): array
{
    $errors = [];

    if (mb_strlen($new) < 8) {
        $errors['new_password'] = 'Minimal 8 karakter.';
    } elseif ($new === $current) {
        $errors['new_password'] = 'Password baru harus berbeda dari password lama.';
    }

    if ($new !== $confirm) {
        $errors['new_password_confirmation'] = 'Konfirmasi password tidak sama.';
    }

    return $errors;
}

/**
 * Username media sosial (Instagram/TikTok) dari "@nama", "nama", atau URL profil → "nama".
 * Null bila tidak valid. Hanya huruf, angka, titik, dan garis bawah (maks 30).
 */
function normalize_social_username(string $value): ?string
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    // URL profil: ambil segmen path pertama (instagram.com/nama, tiktok.com/@nama)
    if (preg_match('#^(?:https?://)?(?:[a-z0-9-]+\.)*(?:instagram\.com|tiktok\.com)/([^/?\#]+)#i', $value, $m) === 1) {
        $value = $m[1];
    }

    $value = ltrim($value, '@');
    if (preg_match('/^[A-Za-z0-9._]{1,30}$/', $value) !== 1) {
        return null;
    }

    return $value;
}

/**
 * URL http/https yang aman untuk ditautkan. Tambah "https://" bila skema kosong.
 * Null bila tidak valid, skema lain (mis. javascript:), atau lebih dari 255 karakter.
 */
function normalize_http_url(string $value): ?string
{
    $value = trim($value);
    if ($value === '' || preg_match('/\s/', $value) === 1) {
        return null;
    }
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $value) !== 1) {
        $value = 'https://' . ltrim($value, '/');
    }

    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
    $host = (string) parse_url($value, PHP_URL_HOST);
    if (!in_array($scheme, ['http', 'https'], true) || $host === '' || !str_contains($host, '.')) {
        return null;
    }
    if (filter_var($value, FILTER_VALIDATE_URL) === false || strlen($value) > 255) {
        return null;
    }

    return $value;
}

/**
 * Teks tampilan URL tanpa skema & "www.", tanpa garis miring akhir: https://www.toko.id/a/ → toko.id/a
 */
function display_url(string $url): string
{
    $display = preg_replace('#^https?://(www\.)?#i', '', $url) ?? $url;
    return rtrim($display, '/');
}
