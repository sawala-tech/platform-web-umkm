<?php
/**
 * Frame Admin/Portofolio - Form (tambah & edit) + Publish belum lengkap (checklist kelengkapan).
 * @var array|null $portfolio  data tersimpan (null saat tambah)
 * @var array      $values     isian yang ditampilkan
 * @var array      $errors     pesan error per field
 */
$isEdit = $portfolio !== null;
$isPublished = $isEdit && $portfolio['status'] === 'published';
$action = $isEdit ? url('/admin/portfolios/' . $portfolio['id']) : url('/admin/portfolios');
$val = fn(string $key): string => (string) ($values[$key] ?? '');
$err = fn(string $key): ?string => $errors[$key] ?? null;
$host = preg_replace('#^https?://#', '', rtrim(url('/'), '/')) . '/';
$logoPath = $isEdit ? $portfolio['logo_path'] : null;
$nameForInitials = $val('business_name') !== '' ? $val('business_name') : ($portfolio['business_name'] ?? '');

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
