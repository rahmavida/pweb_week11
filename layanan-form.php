<?php
require 'config.php';
wajib_role('admin');

$id = (int)($_GET['id'] ?? 0);
$l = ['id_layanan' => 0, 'nama_layanan' => '', 'harga_per_kg' => ''];
if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM layanan WHERE id_layanan = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $l = $stmt->get_result()->fetch_assoc();
    if (!$l) {
        flash('danger', 'Layanan tidak ditemukan.');
        redirect('layanan.php');
    }
}
$judul = $id ? 'Edit Layanan' : 'Tambah Layanan';
include 'header.php';
?>
<h3 class="mb-3"><?= e($judul) ?></h3>
<div class="card" style="max-width:520px"><div class="card-body">
    <form action="layanan-proses.php" method="post" id="f" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)$l['id_layanan'] ?>">
        <div class="mb-3">
            <label class="form-label">Nama Layanan</label>
            <input type="text" name="nama_layanan" class="form-control" maxlength="64" required
                   value="<?= e($l['nama_layanan']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Harga per Kg (Rp)</label>
            <input type="number" name="harga_per_kg" class="form-control" min="1" required
                   value="<?= e($l['harga_per_kg']) ?>">
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="layanan.php" class="btn btn-secondary">Batal</a>
    </form>
</div></div>
<script>
document.getElementById('f').addEventListener('submit', function (ev) {
    const f = ev.target, pesan = [];
    if (!f.nama_layanan.value.trim()) pesan.push('Nama layanan wajib diisi.');
    if (!(parseInt(f.harga_per_kg.value, 10) > 0)) pesan.push('Harga per kg harus lebih dari 0.');
    if (pesan.length) { ev.preventDefault(); alert(pesan.join('\n')); }
});
</script>
<?php include 'footer.php'; ?>
