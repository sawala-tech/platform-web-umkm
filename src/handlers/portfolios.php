<?php
/**
 * Kelola portofolio UMKM (US2) dan publish/unpublish (US4).
 */
declare(strict_types=1);

/* ---------- Validasi ---------- */

/**
 * Validasi & normalisasi input form portofolio.
 * @return array{0: array, 1: array<string,string>, 2: string[]} [data, errors, notices]
 */
function validate_portfolio_input(array $post, ?array $existing, PortfolioRepository $repo): array
{
    $errors = [];
    $notices = [];

    // Nama usaha
    $businessName = trim((string) ($post['business_name'] ?? ''));
    if ($businessName === '') {
        $errors['business_name'] = 'Nama usaha wajib diisi.';
    } elseif (mb_strlen($businessName) > 150) {
        $errors['business_name'] = 'Nama usaha maksimal 150 karakter.';
    }

    // Slug: turunkan dari nama bila kosong, lalu normalisasi
    $rawSlug = trim((string) ($post['slug'] ?? ''));
    $slug = normalize_slug($rawSlug !== '' ? $rawSlug : $businessName);
    if (strlen($slug) < 3) {
        $errors['slug'] = 'Slug minimal 3 karakter.';
    } elseif (!is_valid_slug($slug)) {
        $errors['slug'] = 'Slug minimal 3 karakter.';
    } elseif (is_reserved_slug($slug)) {
        $errors['slug'] = 'Slug tidak dapat digunakan.';
    } elseif ($repo->slugExists($slug, (int) ($existing['id'] ?? 0))) {
        $errors['slug'] = 'Slug sudah digunakan. Pilih slug lain.';
    } elseif ($rawSlug !== '' && $rawSlug !== $slug) {
        $notices[] = "Slug disimpan sebagai /{$slug}.";
    }

    // Bio
    $bio = trim((string) ($post['bio'] ?? ''));
    if (mb_strlen($bio) > 2000) {
        $errors['bio'] = 'Bio maksimal 2.000 karakter.';
    }

    // WhatsApp
    $rawWhatsapp = trim((string) ($post['whatsapp'] ?? ''));
    $whatsapp = null;
    if ($rawWhatsapp !== '') {
        $whatsapp = normalize_whatsapp($rawWhatsapp);
        if ($whatsapp === null) {
            $errors['whatsapp'] = 'Nomor WhatsApp harus 10–15 digit angka.';
        }
    }

    // Portofolio yang sudah terbit harus tetap lengkap
    if ($existing !== null && $existing['status'] === 'published') {
        if ($bio === '' && !isset($errors['bio'])) {
            $errors['bio'] = 'Portofolio yang sudah terbit harus tetap lengkap.';
        }
        if ($rawWhatsapp === '' && !isset($errors['whatsapp'])) {
            $errors['whatsapp'] = 'Portofolio yang sudah terbit harus tetap lengkap.';
        }
        if (!isset($errors['slug']) && $slug !== $existing['slug'] && ($post['confirm_slug_change'] ?? '') !== '1') {
            $errors['slug'] = "URL publik akan berubah dari /{$existing['slug']} ke /{$slug}. "
                . 'Centang konfirmasi untuk melanjutkan.';
        }
    }

    // Media sosial (username) & toko online (URL) — semuanya opsional
    $social = [];
    foreach (['instagram' => 'Instagram', 'tiktok' => 'TikTok'] as $field => $label) {
        $raw = trim((string) ($post[$field] ?? ''));
        $social[$field] = $raw === '' ? null : normalize_social_username($raw);
        if ($raw !== '' && $social[$field] === null) {
            $errors[$field] = "Username {$label} hanya boleh huruf, angka, titik, dan garis bawah (maks 30).";
        }
    }
    $shops = [
        'shopee_url'    => ['Shopee', 'https://shopee.co.id/nama-toko'],
        'tokopedia_url' => ['Tokopedia', 'https://tokopedia.com/nama-toko'],
    ];
    foreach ($shops as $field => [$label, $example]) {
        $raw = trim((string) ($post[$field] ?? ''));
        $social[$field] = $raw === '' ? null : normalize_http_url($raw);
        if ($raw !== '' && $social[$field] === null) {
            $errors[$field] = "URL toko {$label} tidak valid. Contoh: {$example}";
        }
    }

    [$links, $linkErrors] = validate_links_input($post['links'] ?? []);
    $errors = array_merge($errors, $linkErrors);

    $data = [
        'business_name' => $businessName,
        'slug'          => $slug,
        'bio'           => $bio === '' ? null : $bio,
        'whatsapp'      => $whatsapp,
        'instagram'     => $social['instagram'],
        'tiktok'        => $social['tiktok'],
        'shopee_url'    => $social['shopee_url'],
        'tokopedia_url' => $social['tokopedia_url'],
        'links'         => $links,
    ];

    return [$data, $errors, $notices];
}

/**
 * Validasi baris "link lain" dari form (links[i][label], links[i][url]). Baris kosong diabaikan.
 * @return array{0: array<int, array{label:string, url:string}>, 1: array<string,string>} [links, errors "links.i"]
 */
function validate_links_input(mixed $rows): array
{
    $links = [];
    $errors = [];
    if (!is_array($rows)) {
        return [$links, $errors];
    }

    foreach (array_values($rows) as $i => $row) {
        $label = trim((string) (is_array($row) ? ($row['label'] ?? '') : ''));
        $rawUrl = trim((string) (is_array($row) ? ($row['url'] ?? '') : ''));
        if ($label === '' && $rawUrl === '') {
            continue;
        }
        $url = $rawUrl === '' ? null : normalize_http_url($rawUrl);

        if ($label === '') {
            $errors["links.{$i}"] = 'Label link wajib diisi, mis. Website atau GoFood.';
        } elseif (mb_strlen($label) > 50) {
            $errors["links.{$i}"] = 'Label link maksimal 50 karakter.';
        } elseif ($url === null) {
            $errors["links.{$i}"] = 'URL tidak valid. Gunakan alamat http:// atau https://.';
        } elseif (count($links) >= LINKS_MAX) {
            $errors["links.{$i}"] = 'Maksimal ' . LINKS_MAX . ' link lain.';
        } else {
            $links[] = ['label' => $label, 'url' => $url];
        }
    }

    return [$links, $errors];
}

/**
 * Label field yang belum lengkap untuk publish (kosong = siap terbit).
 */
function publish_missing_fields(array $p): array
{
    $missing = [];
    if (trim((string) ($p['business_name'] ?? '')) === '') {
        $missing[] = 'Nama usaha';
    }
    if (!is_valid_slug((string) ($p['slug'] ?? ''))) {
        $missing[] = 'Slug';
    }
    if (trim((string) ($p['bio'] ?? '')) === '') {
        $missing[] = 'Bio';
    }
    $wa = trim((string) ($p['whatsapp'] ?? ''));
    if ($wa === '' || normalize_whatsapp($wa) === null) {
        $missing[] = 'Nomor WhatsApp';
    }
    return $missing;
}

function is_duplicate_key(PDOException $e): bool
{
    return ($e->errorInfo[1] ?? null) === 1062;
}

/* ---------- Tampilan ---------- */

function render_portfolio_form(?array $portfolio, array $values, array $errors = [], int $status = 200): void
{
    $images = $portfolio ? (new PortfolioMediaRepository(db()))->images((int) $portfolio['id']) : [];

    render('admin/portfolios/form', [
        'title'     => $portfolio ? 'Edit Portofolio' : 'Tambah Portofolio',
        'active'    => 'portfolios',
        'portfolio' => $portfolio,
        'values'    => $values,
        'errors'    => $errors,
        'images'    => $images,
        'scripts'   => ['assets/js/slug.js'],
    ], 'admin', $status);
}

/** Id foto yang dicentang "Hapus" pada form (hanya angka positif). */
function posted_remove_image_ids(): array
{
    $raw = $_POST['remove_images'] ?? [];
    return is_array($raw) ? array_values(array_filter(array_map('intval', $raw), fn(int $id) => $id > 0)) : [];
}

function portfolios_index(): void
{
    require_admin();

    $q = trim((string) ($_GET['q'] ?? ''));
    $status = (string) ($_GET['status'] ?? '');
    if (!in_array($status, ['', 'draft', 'published'], true)) {
        $status = '';
    }
    $page = max(1, (int) ($_GET['page'] ?? 1));

    $result = (new PortfolioRepository(db()))->paginate($q, $status, $page, PER_PAGE);

    render('admin/portfolios/index', [
        'title'  => 'Portofolio UMKM',
        'active' => 'portfolios',
        'q'      => $q,
        'status' => $status,
        'result' => $result,
    ], 'admin');
}

function portfolios_create(): void
{
    require_admin();
    render_portfolio_form(null, []);
}

function portfolios_edit(int $id): void
{
    require_admin();
    $portfolio = (new PortfolioRepository(db()))->find($id) ?? not_found(true);

    $values = $portfolio;
    $values['whatsapp'] = $portfolio['whatsapp'] ? format_whatsapp_display($portfolio['whatsapp']) : '';
    $values['links'] = (new PortfolioMediaRepository(db()))->links($id);
    render_portfolio_form($portfolio, $values);
}

/* ---------- Simpan ---------- */

function portfolios_store(): void
{
    $admin = require_admin();
    verify_csrf();

    $repo = new PortfolioRepository(db());
    [$data, $errors, $notices] = validate_portfolio_input($_POST, null, $repo);

    $logo = ['path' => null, 'error' => null];
    $gallery = ['paths' => [], 'error' => null];
    if (!$errors) {
        $logo = handle_logo_upload($_FILES['logo'] ?? null);
        if ($logo['error'] !== null) {
            $errors['logo'] = $logo['error'];
        }
    }
    if (!$errors) {
        $gallery = handle_gallery_uploads($_FILES['gallery'] ?? null, GALLERY_MAX_IMAGES);
        if ($gallery['error'] !== null) {
            $errors['gallery'] = $gallery['error'];
        }
    }

    if ($errors) {
        delete_logo($logo['path']);
        render_portfolio_form(null, $_POST, $errors, 422);
        return;
    }

    $data['logo_path'] = $logo['path'];
    $data['status'] = 'draft';

    try {
        transaction(function (PDO $pdo) use ($repo, $data, $gallery, $admin): void {
            $id = $repo->insert($data);
            $media = new PortfolioMediaRepository($pdo);
            $media->addImages($id, $gallery['paths']);
            $media->replaceLinks($id, $data['links']);
            log_activity($pdo, $admin['id'], ACTION_CREATE_PORTFOLIO,
                "Menambah portofolio \"{$data['business_name']}\" (/{$data['slug']})");
        });
    } catch (PDOException $e) {
        delete_logo($logo['path']);
        delete_uploads($gallery['paths']);
        if (!is_duplicate_key($e)) {
            throw $e;
        }
        render_portfolio_form(null, $_POST, ['slug' => 'Slug sudah digunakan. Pilih slug lain.'], 422);
        return;
    }

    flash('success', 'Portofolio berhasil ditambahkan.');
    foreach ($notices as $notice) {
        flash('info', $notice);
    }
    redirect('/admin/portfolios');
}

function portfolios_update(int $id): void
{
    $admin = require_admin();
    verify_csrf();

    $repo = new PortfolioRepository(db());
    $existing = $repo->find($id) ?? not_found(true);

    [$data, $errors, $notices] = validate_portfolio_input($_POST, $existing, $repo);

    $media = new PortfolioMediaRepository(db());
    $existingImageIds = array_map(fn(array $img) => (int) $img['id'], $media->images($id));
    $removeIds = array_values(array_intersect(posted_remove_image_ids(), $existingImageIds));
    $existingLinks = $media->links($id);

    $logo = ['path' => null, 'error' => null];
    $gallery = ['paths' => [], 'error' => null];
    if (!$errors) {
        $logo = handle_logo_upload($_FILES['logo'] ?? null);
        if ($logo['error'] !== null) {
            $errors['logo'] = $logo['error'];
        }
    }
    if (!$errors) {
        $slotsLeft = GALLERY_MAX_IMAGES - (count($existingImageIds) - count($removeIds));
        $gallery = handle_gallery_uploads($_FILES['gallery'] ?? null, $slotsLeft);
        if ($gallery['error'] !== null) {
            $errors['gallery'] = $gallery['error'];
        }
    }

    if ($errors) {
        delete_logo($logo['path']);
        render_portfolio_form($existing, $_POST, $errors, 422);
        return;
    }

    // Logo: baru diunggah > dihapus > tetap
    $removeLogo = ($_POST['remove_logo'] ?? '') === '1';
    if ($logo['path'] !== null) {
        $data['logo_path'] = $logo['path'];
    } elseif ($removeLogo) {
        $data['logo_path'] = null;
    } else {
        $data['logo_path'] = $existing['logo_path'];
    }

    $labels = [
        'business_name' => 'nama usaha',
        'slug'          => 'slug',
        'bio'           => 'bio',
        'whatsapp'      => 'WhatsApp',
        'logo_path'     => 'logo',
        'instagram'     => 'Instagram',
        'tiktok'        => 'TikTok',
        'shopee_url'    => 'Shopee',
        'tokopedia_url' => 'Tokopedia',
    ];
    $changed = [];
    foreach ($labels as $field => $label) {
        if (($data[$field] ?? null) !== ($existing[$field] ?? null)) {
            $changed[] = $label;
        }
    }
    $linksChanged = $data['links'] !== $existingLinks;
    if ($linksChanged) {
        $changed[] = 'link lain';
    }
    if ($removeIds || $gallery['paths']) {
        $changed[] = 'galeri';
    }

    if (!$changed) {
        flash('info', 'Tidak ada perubahan.');
        redirect('/admin/portfolios');
    }

    $removedPaths = [];
    try {
        transaction(function (PDO $pdo) use ($repo, $id, $data, $admin, $changed, $removeIds, $gallery, $linksChanged, &$removedPaths): void {
            $repo->update($id, $data);
            $media = new PortfolioMediaRepository($pdo);
            $removedPaths = $media->deleteImages($id, $removeIds);
            $media->addImages($id, $gallery['paths']);
            if ($linksChanged) {
                $media->replaceLinks($id, $data['links']);
            }
            log_activity($pdo, $admin['id'], ACTION_UPDATE_PORTFOLIO,
                "Mengubah portofolio \"{$data['business_name']}\" (/{$data['slug']}): " . implode(', ', $changed));
        });
    } catch (PDOException $e) {
        delete_logo($logo['path']);
        delete_uploads($gallery['paths']);
        if (!is_duplicate_key($e)) {
            throw $e;
        }
        render_portfolio_form($existing, $_POST, ['slug' => 'Slug sudah digunakan. Pilih slug lain.'], 422);
        return;
    }

    // File logo lama dihapus hanya setelah commit berhasil
    if ($existing['logo_path'] !== null && $existing['logo_path'] !== $data['logo_path']) {
        delete_logo($existing['logo_path']);
    }
    delete_uploads($removedPaths);

    flash('success', 'Perubahan portofolio disimpan.');
    foreach ($notices as $notice) {
        flash('info', $notice);
    }
    redirect('/admin/portfolios');
}

function portfolios_destroy(int $id): void
{
    $admin = require_admin();
    verify_csrf();

    $repo = new PortfolioRepository(db());
    $portfolio = $repo->find($id) ?? not_found(true);
    $imagePaths = (new PortfolioMediaRepository(db()))->imagePaths($id);

    // Baris galeri & link ikut terhapus lewat ON DELETE CASCADE
    transaction(function (PDO $pdo) use ($repo, $portfolio, $admin): void {
        $repo->delete((int) $portfolio['id']);
        log_activity($pdo, $admin['id'], ACTION_DELETE_PORTFOLIO,
            "Menghapus portofolio \"{$portfolio['business_name']}\" (/{$portfolio['slug']})");
    });

    delete_logo($portfolio['logo_path']);
    delete_uploads($imagePaths);

    flash('success', 'Portofolio dihapus.');
    redirect('/admin/portfolios');
}

/* ---------- Publish / Unpublish (US4) ---------- */

function portfolios_publish(int $id): void
{
    $admin = require_admin();
    verify_csrf();

    $repo = new PortfolioRepository(db());
    $portfolio = $repo->find($id) ?? not_found(true);

    if ($portfolio['status'] !== 'published') {
        $missing = publish_missing_fields($portfolio);
        if ($missing) {
            flash('error', 'Data belum lengkap untuk diterbitkan: ' . implode(', ', $missing));
            redirect("/admin/portfolios/{$id}/edit");
        }

        transaction(function (PDO $pdo) use ($repo, $portfolio, $admin): void {
            $repo->setStatus((int) $portfolio['id'], 'published');
            log_activity($pdo, $admin['id'], ACTION_PUBLISH_PORTFOLIO,
                "Menerbitkan halaman \"{$portfolio['business_name']}\" (/{$portfolio['slug']})");
        });
        $portfolio = $repo->find($id);
        flash('success', 'Portofolio berhasil diterbitkan');
    }

    header('Cache-Control: no-store');
    render('admin/portfolios/published', [
        'title'     => 'Publish Portofolio',
        'active'    => 'portfolios',
        'portfolio' => $portfolio,
    ], 'admin');
}

function portfolios_unpublish(int $id): void
{
    $admin = require_admin();
    verify_csrf();

    $repo = new PortfolioRepository(db());
    $portfolio = $repo->find($id) ?? not_found(true);

    if ($portfolio['status'] === 'published') {
        transaction(function (PDO $pdo) use ($repo, $portfolio, $admin): void {
            $repo->setStatus((int) $portfolio['id'], 'draft');
            log_activity($pdo, $admin['id'], ACTION_UNPUBLISH_PORTFOLIO,
                "Membatalkan terbit \"{$portfolio['business_name']}\" (/{$portfolio['slug']})");
        });
        flash('success', 'Portofolio dikembalikan ke Draft. Halaman publik tidak dapat diakses.');
    }

    redirect('/admin/portfolios');
}
