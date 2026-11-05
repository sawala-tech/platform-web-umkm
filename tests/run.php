<?php
/**
 * Uji fungsi murni tanpa framework. Jalankan: php tests/run.php
 */
declare(strict_types=1);

require __DIR__ . '/../src/lib/validation.php';

$failures = 0;

function check(string $label, mixed $actual, mixed $expected): void
{
    global $failures;
    if ($actual === $expected) {
        echo "PASS  {$label}\n";
        return;
    }
    $failures++;
    echo "FAIL  {$label}\n      diharapkan: " . var_export($expected, true)
        . "\n      didapat:    " . var_export($actual, true) . "\n";
}

// Slug
check("normalize_slug('Kopi Nusantara!')", normalize_slug('Kopi Nusantara!'), 'kopi-nusantara');
check("normalize_slug('  Café  Ñusa -- Jaya ')", normalize_slug('  Café  Ñusa -- Jaya '), 'cafe-nusa-jaya');
check("normalize_slug('---')", normalize_slug('---'), '');
check("normalize_slug panjang dipotong ≤ 150", strlen(normalize_slug(str_repeat('ab-', 80))) <= 150, true);
check("is_valid_slug('ab')", is_valid_slug('ab'), false);
check("is_valid_slug('kopi-nusantara')", is_valid_slug('kopi-nusantara'), true);
check("is_valid_slug('kopi--x')", is_valid_slug('kopi--x'), false);
check("is_valid_slug('Kopi')", is_valid_slug('Kopi'), false);
check("is_reserved_slug('admin')", is_reserved_slug('admin'), true);
check("is_reserved_slug('kopi-nusantara')", is_reserved_slug('kopi-nusantara'), false);

// WhatsApp
check("normalize_whatsapp('0812-3456-7890')", normalize_whatsapp('0812-3456-7890'), '6281234567890');
check("normalize_whatsapp('+62 812 3456 7890')", normalize_whatsapp('+62 812 3456 7890'), '6281234567890');
check("normalize_whatsapp('08abc')", normalize_whatsapp('08abc'), null);
check("normalize_whatsapp('0812345')", normalize_whatsapp('0812345'), null);
check("format_whatsapp_display('6281234567890')", format_whatsapp_display('6281234567890'), '0812-3456-7890');

// Password
check('validate_new_password: terlalu pendek',
    validate_new_password('admin12345', '1234567', '1234567'),
    ['new_password' => 'Minimal 8 karakter.']);
check('validate_new_password: sama dengan lama',
    validate_new_password('admin12345', 'admin12345', 'admin12345'),
    ['new_password' => 'Password baru harus berbeda dari password lama.']);
check('validate_new_password: konfirmasi beda',
    validate_new_password('admin12345', 'rahasia-baru', 'rahasia-lain'),
    ['new_password_confirmation' => 'Konfirmasi password tidak sama.']);
check('validate_new_password: valid',
    validate_new_password('admin12345', 'rahasia-baru', 'rahasia-baru'),
    []);

// Media sosial & URL
check("normalize_social_username('@kopi.nusa')", normalize_social_username('@kopi.nusa'), 'kopi.nusa');
check("normalize_social_username('kopi_nusa')", normalize_social_username('kopi_nusa'), 'kopi_nusa');
check("normalize_social_username(URL instagram)",
    normalize_social_username('https://www.instagram.com/kopi.nusa/'), 'kopi.nusa');
check("normalize_social_username('tiktok.com/@kopi')", normalize_social_username('tiktok.com/@kopi'), 'kopi');
check("normalize_social_username('kopi nusa')", normalize_social_username('kopi nusa'), null);
check("normalize_social_username('<script>')", normalize_social_username('<script>'), null);
check("normalize_http_url('shopee.co.id/toko')", normalize_http_url('shopee.co.id/toko'), 'https://shopee.co.id/toko');
check("normalize_http_url('http://toko.id')", normalize_http_url('http://toko.id'), 'http://toko.id');
check("normalize_http_url('javascript:alert(1)')", normalize_http_url('javascript:alert(1)'), null);
check("normalize_http_url('ftp://toko.id')", normalize_http_url('ftp://toko.id'), null);
check("normalize_http_url('bukan url')", normalize_http_url('bukan url'), null);
check("normalize_http_url('localhost')", normalize_http_url('localhost'), null);
check("display_url('https://www.toko.id/a/')", display_url('https://www.toko.id/a/'), 'toko.id/a');

echo "\n" . ($failures === 0 ? 'Semua uji PASS.' : "{$failures} uji FAIL.") . "\n";
exit($failures === 0 ? 0 : 1);
