<?php
/**
 * Frame Admin/Portofolio - Form (tambah & edit) + Publish belum lengkap (checklist kelengkapan).
 * @var array|null $portfolio  data tersimpan (null saat tambah)
 * @var array      $values     isian yang ditampilkan
 * @var array      $errors     pesan error per field
 * @var array      $images     foto galeri tersimpan [{id, image_path}]
 */
$isEdit = $portfolio !== null;
$isPublished = $isEdit && $portfolio['status'] === 'published';
$action = $isEdit ? url('/admin/portfolios/' . $portfolio['id']) : url('/admin/portfolios');
$val = fn(string $key): string => (string) ($values[$key] ?? '');
$err = fn(string $key): ?string => $errors[$key] ?? null;
$host = preg_replace('#^https?://#', '', rtrim(url('/'), '/')) . '/';
$logoPath = $isEdit ? $portfolio['logo_path'] : null;
$nameForInitials = $val('business_name') !== '' ? $val('business_name') : ($portfolio['business_name'] ?? '');

// Baris "link lain": selalu LINKS_MAX baris; baris kosong ekstra disembunyikan oleh admin.js
$linkRows = array_values(is_array($values['links'] ?? null) ? $values['links'] : []);
$linkRows = array_slice(array_pad($linkRows, LINKS_MAX, ['label' => '', 'url' => '']), 0, max(LINKS_MAX, count($linkRows)));
$removeImages = array_map('intval', is_array($values['remove_images'] ?? null) ? $values['remove_images'] : []);

// Header: aksi Publish/Unpublish/Lihat halaman (form terpisah) + Batal & Simpan
$actions = '';
if ($isEdit) {
    if ($isPublished) {
        $actions .= partial('button', [
            'label' => 'Lihat halaman', 'href' => url('/' . $portfolio['slug']), 'variant' => 'secondary',
            'attrs' => ['target' => '_blank', 'rel' => 'noopener'],
        ]);
        $actions .= '<form method="post" action="' . e(url('/admin/portfolios/' . $portfolio['id'] . '/unpublish')) . '">'
            . csrf_field() . partial('button', ['label' => 'Unpublish', 'variant' => 'secondary']) . '</form>';
    } else {
        $actions .= '<form method="post" action="' . e(url('/admin/portfolios/' . $portfolio['id'] . '/publish')) . '">'
            . csrf_field() . partial('button', ['label' => 'Terbitkan', 'variant' => 'secondary']) . '</form>';
    }
}
$actions .= partial('button', ['label' => 'Batal', 'href' => url('/admin/portfolios'), 'variant' => 'secondary']);
$actions .= partial('button', ['label' => 'Simpan', 'attrs' => ['form' => 'portfolio-form']]);

$completeness = $isEdit && !$isPublished ? publish_missing_fields($portfolio) : [];
$checklist = [
    'Nama usaha'     => $isEdit ? $portfolio['business_name'] : '',
    'Slug'           => $isEdit ? $portfolio['slug'] : '',
    'Bio'            => $isEdit && $portfolio['bio'] ? 'sudah terisi' : '',
    'Nomor WhatsApp' => $isEdit && $portfolio['whatsapp'] ? format_whatsapp_display($portfolio['whatsapp']) : '',
];
?>
<?= partial('page_header', [
    'heading'  => $isEdit ? 'Edit Portofolio' : 'Tambah Portofolio',
    'subtitle' => $isEdit ? 'Perbarui profil usaha. Slug harus unik.' : 'Isi profil usaha. Slug harus unik.',
    'actions'  => $actions,
]) ?>

<?php if ($errors): ?>
    <?= partial('alert', ['type' => 'error', 'message' => 'Periksa kembali isian yang ditandai.']) ?>
<?php endif; ?>

<form id="portfolio-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= e((string) LOGO_MAX_BYTES) ?>">

    <div class="form-grid">
        <div>
        <div class="card">
            <?= partial('input', [
                'name' => 'business_name', 'label' => 'Nama usaha', 'value' => $val('business_name'),
                'error' => $err('business_name'), 'placeholder' => 'mis. Kopi Nusantara',
                'attrs' => ['maxlength' => 150, 'required' => true, 'data-slug-source' => '1'],
            ]) ?>
            <?= partial('input', [
                'name' => 'slug', 'label' => 'Slug', 'value' => $val('slug'), 'error' => $err('slug'),
                'prefix' => $host, 'placeholder' => 'kopi-nusantara',
                'help' => 'Huruf kecil, angka, dan tanda hubung. Kosongkan untuk dibuat dari nama usaha.',
                'attrs' => [
                    'maxlength' => 150, 'data-slug-target' => '1', 'autocomplete' => 'off',
                    'data-original-slug' => $isPublished ? $portfolio['slug'] : '',
                ],
            ]) ?>
            <?php if ($isPublished): ?>
                <?php $showConfirm = $val('slug') !== '' && normalize_slug($val('slug')) !== $portfolio['slug']; ?>
                <div class="field" data-slug-confirm <?= $showConfirm ? '' : 'hidden' ?>>
                    <?= partial('alert', [
                        'type' => 'info',
                        'message' => 'Portofolio ini sudah terbit. Mengubah slug akan mengubah URL publik, dan URL lama /'
                            . $portfolio['slug'] . ' tidak dapat diakses lagi.',
                    ]) ?>
                    <label class="checkbox">
                        <input type="checkbox" name="confirm_slug_change" value="1"
                            <?= ($values['confirm_slug_change'] ?? '') === '1' ? 'checked' : '' ?>>
                        <span>Saya mengerti URL publik akan berubah.</span>
                    </label>
                </div>
            <?php endif; ?>
            <?= partial('input', [
                'name' => 'bio', 'label' => 'Bio', 'type' => 'textarea', 'value' => $val('bio'),
                'error' => $err('bio'), 'placeholder' => 'Ceritakan usaha Anda secara singkat.',
                'help' => 'Wajib untuk diterbitkan. Maksimal 2.000 karakter.',
                'attrs' => ['maxlength' => 2000],
            ]) ?>
            <?= partial('input', [
                'name' => 'whatsapp', 'label' => 'WhatsApp', 'type' => 'tel', 'value' => $val('whatsapp'),
                'error' => $err('whatsapp'), 'placeholder' => '0812-3456-7890',
                'help' => 'Wajib untuk diterbitkan. 10–15 digit, boleh diawali 0 atau +62.',
                'attrs' => ['inputmode' => 'tel', 'maxlength' => 20],
            ]) ?>
        </div>

        <div class="card" style="margin-top:var(--space-4)">
            <h2 class="t-h3">Media sosial &amp; toko online</h2>
            <p class="t-small t-muted" style="margin:var(--space-1) 0 var(--space-5)">Opsional. Hanya yang diisi yang tampil di halaman publik.</p>
            <?= partial('input', [
                'name' => 'instagram', 'label' => 'Instagram', 'value' => $val('instagram'), 'error' => $err('instagram'),
                'prefix' => 'instagram.com/', 'placeholder' => 'warungbusri',
                'help' => 'Username tanpa @, atau tempel link profil.',
                'attrs' => ['maxlength' => 100, 'autocomplete' => 'off'],
            ]) ?>
            <?= partial('input', [
                'name' => 'tiktok', 'label' => 'TikTok / TikTok Shop', 'value' => $val('tiktok'), 'error' => $err('tiktok'),
                'prefix' => 'tiktok.com/@', 'placeholder' => 'warungbusri',
                'help' => 'Username tanpa @, atau tempel link profil.',
                'attrs' => ['maxlength' => 100, 'autocomplete' => 'off'],
            ]) ?>
            <?= partial('input', [
                'name' => 'shopee_url', 'label' => 'Shopee (URL toko)', 'type' => 'url', 'value' => $val('shopee_url'),
                'error' => $err('shopee_url'), 'placeholder' => 'https://shopee.co.id/nama-toko',
                'attrs' => ['maxlength' => 255, 'inputmode' => 'url'],
            ]) ?>
            <?= partial('input', [
                'name' => 'tokopedia_url', 'label' => 'Tokopedia (URL toko)', 'type' => 'url', 'value' => $val('tokopedia_url'),
                'error' => $err('tokopedia_url'), 'placeholder' => 'https://tokopedia.com/nama-toko',
                'attrs' => ['maxlength' => 255, 'inputmode' => 'url'],
            ]) ?>

            <fieldset class="field link-fieldset" data-links style="margin-bottom:0">
                <legend class="field-label">Link lain (label + URL)</legend>
                <?php foreach ($linkRows as $i => $row): ?>
                    <?php
                    $rowError = $err("links.{$i}");
                    $rowLabel = (string) ($row['label'] ?? '');
                    $rowUrl = (string) ($row['url'] ?? '');
                    $isEmpty = $rowLabel === '' && $rowUrl === '' && !$rowError;
                    ?>
                    <div class="link-row<?= $rowError ? ' field-error' : '' ?>" data-link-row<?= $isEmpty ? ' data-link-empty' : '' ?>>
                        <div class="link-row-inputs">
                            <label class="sr-only" for="f-link-label-<?= $i ?>">Label link <?= $i + 1 ?></label>
                            <input class="input" id="f-link-label-<?= $i ?>" type="text" name="links[<?= $i ?>][label]"
                                value="<?= e($rowLabel) ?>" placeholder="mis. Website" maxlength="50">
                            <label class="sr-only" for="f-link-url-<?= $i ?>">URL link <?= $i + 1 ?></label>
                            <input class="input" id="f-link-url-<?= $i ?>" type="url" name="links[<?= $i ?>][url]"
                                value="<?= e($rowUrl) ?>" placeholder="https://" maxlength="255" inputmode="url"
                                <?= $rowError ? 'aria-invalid="true" aria-describedby="f-link-error-' . $i . '"' : '' ?>>
                        </div>
                        <?php if ($rowError): ?>
                            <p class="field-message" id="f-link-error-<?= $i ?>"><?= e($rowError) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="button" class="link-add" data-link-add hidden>+ Tambah link</button>
                <p class="field-help">Mis. Website, GoFood, GrabFood, atau Linktree. Maksimal <?= e((string) LINKS_MAX) ?> link.</p>
            </fieldset>
        </div>
        </div>

        <div>
            <div class="card">
                <div class="side-label">Logo</div>
                <div class="logo-preview">
                    <?php if ($logoPath): ?>
                        <img src="<?= e(asset($logoPath)) ?>" alt="Logo <?= e($portfolio['business_name']) ?>">
                    <?php else: ?>
                        <span class="logo-initials"><?= e(initials($nameForInitials) ?: 'UM') ?></span>
                    <?php endif; ?>
                </div>
                <p class="t-small t-muted" style="margin:0">PNG, JPG, atau WEBP, maksimal 2 MB.</p>
                <div class="field<?= $err('logo') ? ' field-error' : '' ?>" style="margin-bottom:0">
                    <label class="sr-only" for="f-logo">Unggah logo</label>
                    <input class="file-input" id="f-logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                    <?php if ($err('logo')): ?>
                        <p class="field-message"><?= e($err('logo')) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($logoPath): ?>
                    <label class="checkbox">
                        <input type="checkbox" name="remove_logo" value="1" <?= ($values['remove_logo'] ?? '') === '1' ? 'checked' : '' ?>>
                        <span>Hapus logo</span>
                    </label>
                <?php endif; ?>

                <div class="side-label">Status</div>
                <?= partial('badge', [
                    'variant' => $isPublished ? 'published' : 'draft',
                    'label'   => $isPublished ? 'Published' : 'Draft',
                ]) ?>
            </div>

            <div class="card" style="margin-top:var(--space-4)">
                <div class="side-label">Galeri produk</div>
                <?php if ($images): ?>
                    <ul class="gallery-thumbs">
                        <?php foreach ($images as $i => $image): ?>
                            <li class="gallery-thumb">
                                <img src="<?= e(asset($image['image_path'])) ?>" alt="Foto produk <?= $i + 1 ?>" loading="lazy">
                                <label class="gallery-thumb-remove">
                                    <input type="checkbox" name="remove_images[]" value="<?= e((string) $image['id']) ?>"
                                        <?= in_array((int) $image['id'], $removeImages, true) ? 'checked' : '' ?>>
                                    <span>Hapus</span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="t-small t-muted" style="margin:0 0 var(--space-3)">Belum ada foto produk.</p>
                <?php endif; ?>
                <p class="t-small t-muted" style="margin:0">
                    JPG, PNG, atau WEBP, maks 2 MB per foto, hingga <?= e((string) GALLERY_MAX_IMAGES) ?> foto
                    (<?= count($images) ?>/<?= e((string) GALLERY_MAX_IMAGES) ?> terpakai).
                </p>
                <div class="field<?= $err('gallery') ? ' field-error' : '' ?>" style="margin-bottom:0">
                    <label class="sr-only" for="f-gallery">Unggah foto produk</label>
                    <input class="file-input" id="f-gallery" type="file" name="gallery[]" multiple
                        accept="image/jpeg,image/png,image/webp"<?= $err('gallery') ? ' aria-invalid="true" aria-describedby="f-gallery-error"' : '' ?>>
                    <?php if ($err('gallery')): ?>
                        <p class="field-message" id="f-gallery-error"><?= e($err('gallery')) ?></p>
                    <?php elseif ($errors): ?>
                        <p class="field-help">Pilih ulang foto yang ingin diunggah.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isEdit && !$isPublished): ?>
                <div class="card" style="margin-top:var(--space-4)">
                    <h2 class="t-h3">Kelengkapan data</h2>
                    <p class="t-small t-muted" style="margin:var(--space-1) 0 0">Periksa kelengkapan data sebelum menerbitkan.</p>
                    <ul class="checklist">
                        <?php foreach ($checklist as $label => $value): ?>
                            <?php $missing = in_array($label, $completeness, true); ?>
                            <li class="<?= $missing ? 'is-missing' : '' ?>">
                                <span class="dot" aria-hidden="true"></span>
                                <span><?= e($label) ?>: <?= $missing ? 'belum diisi' : e($value) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>
