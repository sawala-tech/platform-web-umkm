<?php
/**
 * Upload logo portofolio (Prinsip III: validasi MIME & ukuran, nama file dari server).
 */
declare(strict_types=1);

const LOGO_DIR_RELATIVE = 'uploads/logos/';
const LOGO_MIME_EXTENSIONS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

function public_path(string $relative = ''): string
{
    return dirname(__DIR__, 2) . '/public/' . ltrim($relative, '/');
}

/**
 * Proses file logo dari $_FILES['logo'].
 * @return array{path: ?string, error: ?string} path relatif (uploads/logos/…) atau pesan error.
 */
function handle_logo_upload(?array $file): array
{
    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => 'Ukuran logo maksimal 2 MB.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['path' => null, 'error' => 'Gagal mengunggah logo.'];
    }
    if ((int) $file['size'] > LOGO_MAX_BYTES) {
        return ['path' => null, 'error' => 'Ukuran logo maksimal 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);
    if (!isset(LOGO_MIME_EXTENSIONS[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return ['path' => null, 'error' => 'Logo harus berupa gambar JPG, PNG, atau WEBP.'];
    }

    $name = bin2hex(random_bytes(16)) . '.' . LOGO_MIME_EXTENSIONS[$mime];
    $relative = LOGO_DIR_RELATIVE . $name;
    $target = public_path($relative);

    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['path' => null, 'error' => 'Gagal mengunggah logo.'];
    }

    return ['path' => $relative, 'error' => null];
}

/**
 * Hapus file logo (hanya di dalam uploads/logos/).
 */
function delete_logo(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '' || !str_starts_with($relativePath, LOGO_DIR_RELATIVE)) {
        return;
    }
    if (str_contains($relativePath, '..')) {
        return;
    }
    $full = public_path($relativePath);
    if (is_file($full)) {
        @unlink($full);
    }
}
