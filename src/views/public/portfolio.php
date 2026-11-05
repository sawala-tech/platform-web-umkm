<?php
/**
 * Frame Publik/Portofolio - Desktop/Mobile. Deviasi #2: eyebrow statis "Portofolio UMKM".
 * @var array $portfolio
 */
$name = $portfolio['business_name'];
$whatsapp = $portfolio['whatsapp'];
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

    <?php if (!empty($whatsapp)): ?>
        <div class="profile-actions">
            <a class="btn btn-primary btn-block" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener">Hubungi via WhatsApp</a>
        </div>
        <div class="contact-card">
            <div class="contact-label">WhatsApp</div>
            <div class="contact-value"><?= e(format_whatsapp_display($whatsapp)) ?></div>
        </div>
    <?php endif; ?>
</article>
