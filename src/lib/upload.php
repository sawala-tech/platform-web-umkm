<?php
/**
 * Upload gambar: logo & galeri produk (Prinsip III: validasi MIME & ukuran, nama file dari server).
 */
declare(strict_types=1);

const LOGO_DIR_RELATIVE = 'uploads/logos/';
const GALLERY_DIR_RELATIVE = 'uploads/products/';
const IMAGE_MIME_EXTENSIONS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

// Batas galeri (bisa ditimpa di config/config.php)
if (!defined('GALLERY_MAX_IMAGES')) {
    define('GALLERY_MAX_IMAGES', 12);
}
if (!defined('IMAGE_MAX_BYTES')) {
    define('IMAGE_MAX_BYTES', defined('LOGO_MAX_BYTES') ? LOGO_MAX_BYTES : 2097152);
}

function public_path(string $relative = ''): string
{
    return dirname(__DIR__, 2) . '/public/' . ltrim($relative, '/');
}

/**
 * Validasi & simpan satu file gambar dari $_FILES ke $dirRelative.
 * @param string $label  nama benda untuk pesan error, mis. "Logo" / "Foto produk"
 * @return array{path: ?string, error: ?string} path relatif atau pesan error.
 */
function handle_image_upload(?array $file, string $dirRelative, string $label): array
{
    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    $tooBig = "Ukuran {$label} maksimal 2 MB.";
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['path' => null, 'error' => $tooBig];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['path' => null, 'error' => "Gagal mengunggah {$label}."];
    }
    if ((int) $file['size'] > IMAGE_MAX_BYTES) {
        return ['path' => null, 'error' => $tooBig];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);
    if (!isset(IMAGE_MIME_EXTENSIONS[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return ['path' => null, 'error' => ucfirst($label) . ' harus berupa gambar JPG, PNG, atau WEBP.'];
    }

    $relative = $dirRelative . bin2hex(random_bytes(16)) . '.' . IMAGE_MIME_EXTENSIONS[$mime];
    $target = public_path($relative);

    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['path' => null, 'error' => "Gagal mengunggah {$label}."];
    }

    return ['path' => $relative, 'error' => null];
}

/**
 * Proses file logo dari $_FILES['logo'].
 * @return array{path: ?string, error: ?string}
 */
function handle_logo_upload(?array $file): array
{
    return handle_image_upload($file, LOGO_DIR_RELATIVE, 'logo');
}

/**
 * Ubah struktur $_FILES multi-file (name[]/tmp_name[]/…) menjadi daftar file tunggal,
 * melewati slot kosong (tidak ada file dipilih).
 */
function normalize_files_array(?array $files): array
{
    if ($files === null || !isset($files['name']) || !is_array($files['name'])) {
        return [];
    }
    $list = [];
    foreach (array_keys($files['name']) as $i) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $list[] = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i] ?? 0,
        ];
    }
    return $list;
}

/**
 * Proses unggahan foto galeri dari $_FILES['gallery'].
 * Semua-atau-tidak: bila satu file gagal, file yang sudah tersimpan dihapus lagi.
 * @param int $slotsLeft sisa kuota foto untuk portofolio ini
 * @return array{paths: string[], error: ?string}
 */
function handle_gallery_uploads(?array $files, int $slotsLeft): array
{
    $list = normalize_files_array($files);
    if (!$list) {
        return ['paths' => [], 'error' => null];
    }
    if (count($list) > $slotsLeft) {
        return [
            'paths' => [],
            'error' => 'Galeri maksimal ' . GALLERY_MAX_IMAGES . ' foto. Sisa kuota: ' . max(0, $slotsLeft) . ' foto.',
        ];
    }

    $paths = [];
    foreach ($list as $file) {
        $result = handle_image_upload($file, GALLERY_DIR_RELATIVE, 'foto produk');
        if ($result['error'] !== null) {
            delete_uploads($paths);
            return ['paths' => [], 'error' => $result['error'] . ' (' . basename((string) $file['name']) . ')'];
        }
        $paths[] = $result['path'];
    }

    return ['paths' => $paths, 'error' => null];
}

/**
 * Hapus file unggahan (hanya di dalam uploads/logos/ atau uploads/products/).
 */
function delete_upload(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '' || str_contains($relativePath, '..')) {
        return;
    }
    if (!str_starts_with($relativePath, LOGO_DIR_RELATIVE) && !str_starts_with($relativePath, GALLERY_DIR_RELATIVE)) {
        return;
    }
    $full = public_path($relativePath);
    if (is_file($full)) {
        @unlink($full);
    }
}

/** @param string[] $relativePaths */
function delete_uploads(array $relativePaths): void
{
    foreach ($relativePaths as $path) {
        delete_upload($path);
    }
}

/** Alias lama untuk logo. */
function delete_logo(?string $relativePath): void
{
    delete_upload($relativePath);
}
