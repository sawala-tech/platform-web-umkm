<?php
/**
 * Data awal: akun Admin default + contoh portofolio.
 * Jalankan dari root proyek: php database/seed.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Skrip ini hanya dapat dijalankan dari command line.\n");
}

require __DIR__ . '/../src/bootstrap.php';

$pdo = db();

// 1. Admin default
$adminRepo = new AdminRepository($pdo);
if ($adminRepo->findByUsername('admin') === null) {
    $stmt = $pdo->prepare('INSERT INTO admins (name, username, password_hash) VALUES (?, ?, ?)');
    $stmt->execute(['Administrator', 'admin', password_hash('admin12345', PASSWORD_DEFAULT)]);
    echo "[+] Admin 'admin' dibuat (password awal: admin12345 — segera ganti).\n";
} else {
    echo "[=] Admin 'admin' sudah ada, dilewati.\n";
}

// 2. Contoh portofolio
$portfolioRepo = new PortfolioRepository($pdo);
$samples = [
    [
        'business_name' => 'Warung Bu Sri',
        'slug'          => 'warung-bu-sri',
        'bio'           => 'Warung makan rumahan dengan menu masakan Jawa autentik, dimasak setiap pagi dengan bahan segar.',
        'logo_path'     => null,
        'whatsapp'      => '6281234567890',
        'status'        => 'published',
    ],
    [
        'business_name' => 'Batik Sari',
        'slug'          => 'batik-sari',
        'bio'           => null,
        'logo_path'     => null,
        'whatsapp'      => null,
        'status'        => 'draft',
    ],
];

foreach ($samples as $sample) {
    if ($portfolioRepo->slugExists($sample['slug'])) {
        echo "[=] Portofolio /{$sample['slug']} sudah ada, dilewati.\n";
        continue;
    }
    $portfolioRepo->insert($sample);
    echo "[+] Portofolio \"{$sample['business_name']}\" (/{$sample['slug']}, {$sample['status']}) dibuat.\n";
}

echo "Selesai.\n";
