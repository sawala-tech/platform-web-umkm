<?php
/**
 * Render semua pesan flash: success/info sebagai Toast, error sebagai Alert.
 */
$flashes = take_flashes();
if (!$flashes) {
    return;
}
?>
<div class="toast-stack">
    <?php foreach ($flashes as $flash): ?>
        <?php if ($flash['type'] !== 'error'): ?>
            <?= partial('toast', ['message' => $flash['message'], 'type' => $flash['type']]) ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php foreach ($flashes as $flash): ?>
    <?php if ($flash['type'] === 'error'): ?>
        <?= partial('alert', ['type' => 'error', 'message' => $flash['message']]) ?>
    <?php endif; ?>
<?php endforeach; ?>
