<?php
/**
 * Header halaman admin: judul, subjudul, dan slot aksi kanan.
 * @var string      $heading
 * @var string      $subtitle
 * @var string|null $actions  HTML aksi (sudah aman/di-escape oleh pemanggil)
 */
?>
<header class="page-header">
    <div>
        <h1 class="t-h2"><?= e($heading) ?></h1>
        <p class="t-small t-muted page-subtitle"><?= e($subtitle) ?></p>
    </div>
    <?php if (!empty($actions)): ?>
        <div class="page-actions"><?= $actions ?></div>
    <?php endif; ?>
</header>
