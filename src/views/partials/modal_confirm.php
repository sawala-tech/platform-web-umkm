<?php
/**
 * Modal/Konfirmasi Hapus berbasis <dialog>. Diisi oleh admin.js dari atribut data-* tombol Hapus.
 */
?>
<dialog class="modal" id="confirm-delete" aria-labelledby="confirm-delete-title">
    <form method="post" action="" class="modal-body" data-delete-form>
        <?= csrf_field() ?>
        <h2 class="t-h3" id="confirm-delete-title">Hapus portofolio</h2>
        <p class="t-body t-muted">
            Hapus portofolio <strong data-delete-name></strong>?
            Halaman <span data-delete-slug></span> tidak dapat diakses lagi.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
    </form>
</dialog>
