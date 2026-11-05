<?php
/**
 * Field input berlabel dengan state error.
 * @var string      $name
 * @var string      $label
 * @var string      $type        text | password | textarea | ...
 * @var string|null $value
 * @var string|null $error
 * @var string|null $placeholder
 * @var string|null $help        teks bantu di bawah field
 * @var string|null $prefix      teks prefix tampilan (mis. domain untuk slug)
 * @var array       $attrs       atribut tambahan
 */
$type = $type ?? 'text';
$id = 'f-' . $name;
$hasError = !empty($error);
$attrHtml = '';
foreach (($attrs ?? []) as $attrName => $attrValue) {
    $attrHtml .= $attrValue === true
        ? ' ' . e((string) $attrName)
        : ' ' . e((string) $attrName) . '="' . e((string) $attrValue) . '"';
}
$describedBy = $hasError ? ' aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '';
?>
<div class="field<?= $hasError ? ' field-error' : '' ?>">
    <label class="field-label" for="<?= e($id) ?>"><?= e($label) ?></label>
    <?php if ($type === 'textarea'): ?>
        <textarea class="input" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="5"
            placeholder="<?= e($placeholder ?? '') ?>"<?= $describedBy ?><?= $attrHtml ?>><?= e($value ?? '') ?></textarea>
    <?php elseif (!empty($prefix)): ?>
        <div class="input input-prefixed">
            <span class="input-prefix"><?= e($prefix) ?></span>
            <input id="<?= e($id) ?>" type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($value ?? '') ?>"
                placeholder="<?= e($placeholder ?? '') ?>"<?= $describedBy ?><?= $attrHtml ?>>
        </div>
    <?php else: ?>
        <input class="input" id="<?= e($id) ?>" type="<?= e($type) ?>" name="<?= e($name) ?>"
            value="<?= $type === 'password' ? '' : e($value ?? '') ?>"
            placeholder="<?= e($placeholder ?? '') ?>"<?= $describedBy ?><?= $attrHtml ?>>
    <?php endif; ?>
    <?php if ($hasError): ?>
        <p class="field-message" id="<?= e($id) ?>-error"><?= e($error) ?></p>
    <?php elseif (!empty($help)): ?>
        <p class="field-help"><?= e($help) ?></p>
    <?php endif; ?>
</div>
