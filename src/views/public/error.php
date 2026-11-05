<?php
/**
 * @var string $title
 * @var string $message
 */
?>
<section class="notfound">
    <h1 class="t-h2"><?= e($title ?? 'Terjadi kesalahan') ?></h1>
    <p><?= e($message ?? 'Terjadi kesalahan. Silakan coba lagi.') ?></p>
    <a class="btn btn-primary" href="<?= e(url('/')) ?>">Kembali ke beranda</a>
</section>
