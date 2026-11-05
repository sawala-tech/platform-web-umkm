<?php
/**
 * Tombol / tautan bergaya tombol.
 * @var string $label
 * @var string|null $href     bila diisi → <a>, selain itu <button>
 * @var string $variant       primary | secondary | danger
 * @var string $type          submit | button
 * @var string $class         kelas tambahan
 * @var array  $attrs         atribut tambahan [nama => nilai]
 */
$variant = $variant ?? 'primary';
$class = trim('btn btn-' . $variant . ' ' . ($class ?? ''));
$attrHtml = '';
foreach (($attrs ?? []) as $name => $value) {
    $attrHtml .= ' ' . e((string) $name) . '="' . e((string) $value) . '"';
}
?>
<?php if (!empty($href)): ?>
<a href="<?= e($href) ?>" class="<?= e($class) ?>"<?= $attrHtml ?>><?= e($label) ?></a>
<?php else: ?>
<button type="<?= e($type ?? 'submit') ?>" class="<?= e($class) ?>"<?= $attrHtml ?>><?= e($label) ?></button>
<?php endif; ?>
