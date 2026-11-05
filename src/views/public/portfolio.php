<?php
/**
 * Frame Publik/Portofolio - Desktop/Mobile. Deviasi #2: eyebrow statis "Portofolio UMKM".
 * WhatsApp tampil sebagai baris pertama di kartu "Kontak & Toko Online" (tanpa tombol terpisah).
 * Section "Kontak & Toko Online" dan "Galeri Produk" hanya tampil bila ada isinya.
 * @var array $portfolio
 * @var array $contacts  [{icon, label, value, url}] dari portfolio_contacts()
 * @var array $images    [{id, image_path, sort_order}]
 */
$name = $portfolio['business_name'];
?>
<article class="profile">
    <?php if (!empty($portfolio['logo_path'])): ?>
        <img class="profile-logo" src="<?= e(asset($portfolio['logo_path'])) ?>" alt="Logo <?= e($name) ?>" width="96" height="96">
    <?php else: ?>
        <div class="profile-logo profile-initials" role="img" aria-label="Logo <?= e($name) ?>"><?= e(initials($name)) ?></div>
    <?php endif; ?>

    <div class="t-eyebrow profile-eyebrow">Portofolio UMKM</div>
    <h1 class="t-h1 profile-name"><?= e($name) ?></h1>

    <?php if (!empty($portfolio['bio'])): ?>
        <p class="profile-bio"><?= nl2br(e($portfolio['bio'])) ?></p>
    <?php endif; ?>

    <?php if ($contacts): ?>
        <section class="contact-card" aria-labelledby="contact-title">
            <h2 class="contact-title" id="contact-title">Kontak &amp; Toko Online</h2>
            <ul class="contact-list">
                <?php foreach ($contacts as $contact): ?>
                    <li>
                        <a class="contact-link" href="<?= e($contact['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <span class="contact-badge contact-badge-<?= e($contact['icon']) ?>" aria-hidden="true"><?= icon($contact['icon']) ?></span>
                            <span class="contact-text">
                                <span class="contact-label"><?= e($contact['label']) ?></span>
                                <span class="contact-value"><?= e($contact['value']) ?></span>
                            </span>
                            <span class="contact-arrow" aria-hidden="true"><?= icon('arrow-up-right', 18) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</article>

<?php if ($images): ?>
    <section class="gallery" aria-labelledby="gallery-title">
        <h2 class="t-h2 gallery-title" id="gallery-title">Galeri Produk</h2>
        <ul class="gallery-grid">
            <?php foreach ($images as $i => $image): ?>
                <?php
                $ext = strtolower(pathinfo($image['image_path'], PATHINFO_EXTENSION)) ?: 'jpg';
                $downloadName = ($portfolio['slug'] ?? 'foto-produk') . '-' . ($i + 1) . '.' . $ext;
                ?>
                <li>
                    <a class="gallery-item" href="<?= e(asset($image['image_path'])) ?>" data-lightbox
                        data-index="<?= $i ?>" data-download="<?= e($downloadName) ?>">
                        <img src="<?= e(asset($image['image_path'])) ?>" alt="Foto produk <?= $i + 1 ?> — <?= e($name) ?>"
                            loading="lazy" decoding="async" width="320" height="320">
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <dialog class="lightbox" id="lightbox" aria-label="Foto produk <?= e($name) ?>">
        <div class="lightbox-toolbar">
            <span class="lightbox-counter" data-lb-counter aria-live="polite"></span>
            <div class="lightbox-actions">
                <button type="button" class="lightbox-btn" data-lb-zoom aria-label="Perbesar">
                    <span data-lb-zoom-in><?= icon('zoom-in') ?></span>
                    <span data-lb-zoom-out hidden><?= icon('zoom-out') ?></span>
                </button>
                <a class="lightbox-download" data-lb-download href="#" download>
                    <?= icon('download', 18) ?><span>Download</span>
                </a>
                <button type="button" class="lightbox-btn" data-lb-close aria-label="Tutup">
                    <?= icon('x') ?>
                </button>
            </div>
        </div>
        <div class="lightbox-stage" data-lb-stage>
            <img class="lightbox-img" data-lb-img src="" alt="">
        </div>
        <?php if (count($images) > 1): ?>
            <button type="button" class="lightbox-btn lightbox-nav lightbox-prev" data-lb-prev aria-label="Foto sebelumnya">
                <?= icon('chevron-left', 24) ?>
            </button>
            <button type="button" class="lightbox-btn lightbox-nav lightbox-next" data-lb-next aria-label="Foto berikutnya">
                <?= icon('chevron-right', 24) ?>
            </button>
        <?php endif; ?>
    </dialog>
    <script src="<?= e(asset('assets/js/gallery.js')) ?>" defer></script>
<?php endif; ?>
