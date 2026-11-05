<?php
/**
 * Ubah password Admin (US6).
 */
declare(strict_types=1);

function password_show(array $errors = [], int $status = 200): void
{
    require_admin();
    render('admin/password', [
        'title'  => 'Ubah Password',
        'active' => 'password',
        'errors' => $errors,
    ], 'admin', $status);
}

function password_update(): void
{
    $sessionAdmin = require_admin();
    verify_csrf();

    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['new_password_confirmation'] ?? '');

    $repo = new AdminRepository(db());
    $admin = $repo->findById($sessionAdmin['id']);
    if ($admin === null) {
        logout_admin();
        start_session();
        redirect('/admin/login');
    }

    if (!password_verify($current, $admin['password_hash'])) {
        password_show(['current_password' => 'Password lama tidak valid.'], 422);
        return;
    }

    $errors = validate_new_password($current, $new, $confirm);
    if ($errors) {
        password_show($errors, 422);
        return;
    }

    transaction(function (PDO $pdo) use ($repo, $admin, $new): void {
        $repo->updatePasswordHash((int) $admin['id'], password_hash($new, PASSWORD_DEFAULT));
        // Deskripsi log tidak pernah memuat password (FR-041)
        log_activity($pdo, (int) $admin['id'], ACTION_CHANGE_PASSWORD, 'Mengubah password akun');
    });

    session_regenerate_id(true);
    flash('success', 'Password berhasil diubah');
    redirect('/admin/password');
}
