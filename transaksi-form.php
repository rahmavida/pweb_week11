<?php
require 'config.php';
wajib_login();

$id = (int)($_GET['id'] ?? 0);
$t = [
    'id_transaksi' => 0, 'id_pelanggan' => (int)($_GET['pelanggan'] ?? 0), 'id_layanan' => 0,
    'tanggal_masuk' => date('Y-m-d'), 'tanggal_selesai' => date('Y-m-d', strtotime('+2 day')),
    'berat' => '', 'status' => 'Proses',
];
if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM transaksi WHERE id_transaksi = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $t = $stmt->get_result()->fetch_assoc();
    if (!$t) {
        flash('danger', 'Transaksi tidak ditemukan.');
        redirect('transaksi.php');
    }
}

$pelanggan = $db->query('SELECT id_pelanggan, nama, no_hp FROM pelanggan ORDER BY nama');
$layanan   = $db->query('SELECT * FROM layanan ORDER BY harga_per_kg');
$judul = $id ? 'Edit Transaksi' : 'Transaksi Baru';
include 'header.php';
?>
<h3 class="mb-3"><?= e($judul) ?></h3>
<div class="card" style="max-width:640px"><div class="card-body">
    <form action="transaksi-proses.php" method="post" id="f" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= (int)$t['id_transaksi'] ?>">

        <div class="mb-3">
            <label class="form-label">Pelanggan</label>
            <select name="id_pelanggan" class="form-select" required>
                <option value="">-- pilih pelanggan --</option>
                <?php while ($p = $pelanggan->fetch_assoc()): ?>
                    <option value="<?= (int)$p['id_pelanggan'] ?>" <?= $p['id_pelanggan'] == $t['id_pelanggan'] ? 'selected' : '' ?>>
                        <?= e($p['nama']) ?> (<?= e($p['no_hp']) ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <div class="form-text">Belum terdaftar? <a href="pelanggan-form.php">Tambah pelanggan baru</a>.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Layanan</label>
            <select name="id_layanan" id="layanan" class="form-select" required>
                <option value="">-- pilih layanan --</option>
                <?php while ($l = $layanan->fetch_assoc()): ?>
                    <option value="<?= (int)$l['id_layanan'] ?>" data-harga="<?= (int)$l['harga_per_kg'] ?>"
                        <?= $l['id_layanan'] == $t['id_layanan'] ? 'selected' : '' ?>>
                        <?= e($l['nama_layanan']) ?> (<?= rupiah($l['harga_per_kg']) ?>/kg)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Berat (kg)</label>
                <input type="number" step="0.01" min="0.1" max="999" name="berat" id="berat"
                       class="form-control" required value="<?= e($t['berat']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" class="form-control" required value="<?= e($t['tanggal_masuk']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control" required value="<?= e($t['tanggal_selesai']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['Proses', 'Selesai', 'Sudah Diambil'] as $s): ?>
                    <option <?= $s === $t['status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="alert alert-info">
            Perkiraan total: <strong id="total">Rp 0</strong>
            <div class="small">Angka final dihitung ulang oleh server saat disimpan.</div>
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="transaksi.php" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<script>
const layanan = document.getElementById('layanan');
const berat = document.getElementById('berat');
const total = document.getElementById('total');

// Hitung total di browser hanya sebagai pratinjau
function hitung() {
    const harga = parseInt(layanan.selectedOptions[0]?.dataset.harga || 0, 10);
    const b = parseFloat(berat.value || 0);
    total.textContent = 'Rp ' + Math.round(harga * b).toLocaleString('id-ID');
}
layanan.addEventListener('change', hitung);
berat.addEventListener('input', hitung);
hitung();

document.getElementById('f').addEventListener('submit', function (ev) {
    const f = ev.target, pesan = [];
    if (!f.id_pelanggan.value) pesan.push('Pilih pelanggan.');
    if (!f.id_layanan.value) pesan.push('Pilih layanan.');
    if (!(parseFloat(f.berat.value) > 0)) pesan.push('Berat harus lebih dari 0.');
    if (f.tanggal_selesai.value < f.tanggal_masuk.value) pesan.push('Tanggal selesai tidak boleh sebelum tanggal masuk.');
    if (pesan.length) { ev.preventDefault(); alert(pesan.join('\n')); }
});
</script>
<?php include 'footer.php'; ?>
