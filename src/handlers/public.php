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

    $media = new PortfolioMediaRepository(db());

    render('public/portfolio', [
        'title'           => $portfolio['business_name'] . ' — Portofolio UMKM',
        'metaDescription' => mb_substr($description, 0, 160),
        'portfolio'       => $portfolio,
        'contacts'        => portfolio_contacts($portfolio, $media->links((int) $portfolio['id'])),
        'images'          => $media->images((int) $portfolio['id']),
    ], 'public');
}

/**
 * Daftar kontak yang tampil di halaman publik (hanya yang terisi), urut sesuai desain:
 * WhatsApp, Instagram, TikTok, Shopee, Tokopedia, lalu link lain.
 * @return array<int, array{icon:string, label:string, value:string, url:string}>
 */
function portfolio_contacts(array $p, array $links): array
{
    $contacts = [];
    if (!empty($p['whatsapp'])) {
        $contacts[] = [
            'icon'  => 'whatsapp',
            'label' => 'WhatsApp',
            'value' => format_whatsapp_display($p['whatsapp']),
            'url'   => 'https://wa.me/' . $p['whatsapp'],
        ];
    }
    if (!empty($p['instagram'])) {
        $contacts[] = [
            'icon'  => 'instagram',
            'label' => 'Instagram',
            'value' => '@' . $p['instagram'],
            'url'   => 'https://www.instagram.com/' . rawurlencode($p['instagram']) . '/',
        ];
    }
    if (!empty($p['tiktok'])) {
        $contacts[] = [
            'icon'  => 'tiktok',
            'label' => 'TikTok Shop',
            'value' => '@' . $p['tiktok'],
            'url'   => 'https://www.tiktok.com/@' . rawurlencode($p['tiktok']),
        ];
    }
    foreach (['shopee_url' => ['shopee', 'Shopee'], 'tokopedia_url' => ['tokopedia', 'Tokopedia']] as $field => [$icon, $label]) {
        if (!empty($p[$field])) {
            $contacts[] = ['icon' => $icon, 'label' => $label, 'value' => display_url($p[$field]), 'url' => $p[$field]];
        }
    }
    foreach ($links as $link) {
        $contacts[] = ['icon' => 'link', 'label' => $link['label'], 'value' => display_url($link['url']), 'url' => $link['url']];
    }
    return $contacts;
}
