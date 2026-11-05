<?php
/**
 * Frame Admin/Ubah Password. Field password tidak pernah diisi ulang.
 * @var array $errors
 */
?>
<?= partial('page_header', [
    'heading'  => 'Ubah Password',
    'subtitle' => 'Perbarui password akun Anda secara berkala.',
]) ?>

<form class="card form-narrow" method="post" action="<?= e(url('/admin/password')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= partial('input', [
        'name' => 'current_password', 'label' => 'Password lama', 'type' => 'password',
        'error' => $errors['current_password'] ?? null,
        'attrs' => ['autocomplete' => 'current-password', 'required' => true],
    ]) ?>
    <?= partial('input', [
        'name' => 'new_password', 'label' => 'Password baru', 'type' => 'password',
        'error' => $errors['new_password'] ?? null, 'help' => 'Minimal 8 karakter.',
        'attrs' => ['autocomplete' => 'new-password', 'required' => true, 'minlength' => 8],
    ]) ?>
    <?= partial('input', [
        'name' => 'new_password_confirmation', 'label' => 'Konfirmasi password baru', 'type' => 'password',
        'error' => $errors['new_password_confirmation'] ?? null,
        'attrs' => ['autocomplete' => 'new-password', 'required' => true],
    ]) ?>
    <button type="submit" class="btn btn-primary btn-block">Simpan Password</button>
</form>
