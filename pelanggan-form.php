<?php
require 'config.php';
wajib_login();

// Jika ada ?id= berarti mode edit, jika tidak mode tambah
$id = (int)($_GET['id'] ?? 0);
$p = ['id_pelanggan' => 0, 'nama' => '', 'alamat' => '', 'no_hp' => ''];
if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM pelanggan WHERE id_pelanggan = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) {
        flash('danger', 'Pelanggan tidak ditemukan.');
        redirect('pelanggan.php');
    }
}
$judul = $id ? 'Edit Pelanggan' : 'Tambah Pelanggan';
include 'header.php';
?>
<h3 class="mb-3"><?= e($judul) ?></h3>
<div class="card" style="max-width:640px"><div class="card-body">
    <form action="pelanggan-proses.php" method="post" id="f" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)$p['id_pelanggan'] ?>">
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" maxlength="64" required value="<?= e($p['nama']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" maxlength="255" rows="2" required><?= e($p['alamat']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control" maxlength="20" required
                   placeholder="08xxxxxxxxxx" value="<?= e($p['no_hp']) ?>">
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="pelanggan.php" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<script>
// Validasi sisi client (validasi sisi server tetap ada di pelanggan-proses.php)
document.getElementById('f').addEventListener('submit', function (ev) {
    const f = ev.target;
    const pesan = [];
    if (!f.nama.value.trim()) pesan.push('Nama wajib diisi.');
    if (!f.alamat.value.trim()) pesan.push('Alamat wajib diisi.');
    if (!/^[0-9+]{8,20}$/.test(f.no_hp.value.trim())) pesan.push('No HP harus 8 sampai 20 digit angka.');
    if (pesan.length) { ev.preventDefault(); alert(pesan.join('\n')); }
});
</script>
<?php include 'footer.php'; ?>
