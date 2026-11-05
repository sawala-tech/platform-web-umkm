<?php
/**
 * Halaman publik (US3): beranda dan portofolio berdasarkan slug.
 */
declare(strict_types=1);

function home(): void
{
    render('public/home', ['title' => 'Portofolio UMKM'], 'public');
}

function show_portfolio(string $slug): void
{
    // Format tidak valid → 404 tanpa menyentuh database
    if (strlen($slug) > 150 || !is_valid_slug($slug)) {
        not_found();
    }

    $portfolio = (new PortfolioRepository(db()))->findPublishedBySlug($slug);
    if ($portfolio === null) {
        not_found();
    }

    $bio = (string) ($portfolio['bio'] ?? '');
    $description = trim((string) preg_replace('/\s+/u', ' ', $bio));

    render('public/portfolio', [
        'title'           => $portfolio['business_name'] . ' — Portofolio UMKM',
        'metaDescription' => mb_substr($description, 0, 160),
        'portfolio'       => $portfolio,
    ], 'public');
}
