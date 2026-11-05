<?php
/**
 * Toast/Success (titik hijau) di kanan atas.
 * @var string $message
 * @var string $type success | info | error
 */
$type = $type ?? 'success';
?>
<div class="toast toast-<?= e($type) ?>" role="status" data-toast>
    <span class="toast-dot" aria-hidden="true"></span>
    <span><?= e($message) ?></span>
</div>
