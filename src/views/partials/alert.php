<?php
/**
 * Alert.
 * @var string   $type    error | info | success
 * @var string   $message
 * @var string[] $items   daftar poin opsional
 */
?>
<div class="alert alert-<?= e($type ?? 'error') ?>" role="<?= ($type ?? 'error') === 'error' ? 'alert' : 'status' ?>">
    <?= e($message) ?>
    <?php if (!empty($items)): ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
