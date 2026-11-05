<?= partial('page_header', [
    'heading'  => 'Tidak ditemukan',
    'subtitle' => 'Data atau halaman yang Anda cari tidak ada.',
]) ?>
<div class="card not-found-card">
    <h2 class="t-h3">Portofolio tidak ditemukan</h2>
    <p class="t-muted">Portofolio mungkin sudah dihapus atau alamatnya salah.</p>
    <a class="btn btn-secondary" href="<?= e(url('/admin/portfolios')) ?>">← Kembali ke daftar portofolio</a>
</div>
