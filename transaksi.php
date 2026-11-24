<?php
require 'config.php';
wajib_login();
$judul = 'Transaksi';

// Pencarian berdasarkan nama pelanggan atau tanggal masuk (YYYY-MM-DD)
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT t.*, p.nama, l.nama_layanan
        FROM transaksi t
        JOIN pelanggan p ON p.id_pelanggan = t.id_pelanggan
        JOIN layanan l ON l.id_layanan = t.id_layanan';
if ($q !== '') {
    $sql .= ' WHERE p.nama LIKE ? OR t.tanggal_masuk = ? ORDER BY t.id_transaksi DESC';
    $stmt = $db->prepare($sql);
    $like = '%' . $q . '%';
    $stmt->bind_param('ss', $like, $q);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $rows = $db->query($sql . ' ORDER BY t.id_transaksi DESC');
}

$warna = ['Proses' => 'warning', 'Selesai' => 'info', 'Sudah Diambil' => 'success'];
include 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Transaksi Laundry</h3>
    <a href="transaksi-form.php" class="btn btn-primary">+ Transaksi Baru</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="q" value="<?= e($q) ?>" class="form-control"
               placeholder="Nama pelanggan atau tanggal (2026-11-24)">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Cari</button>
        <?php if ($q !== ''): ?><a href="transaksi.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
    </div>
</form>

<div class="card"><div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
        <tr>
            <th>No</th><th>Pelanggan</th><th>Layanan</th><th>Masuk</th><th>Selesai</th>
            <th>Berat</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php $ada = false; while ($r = $rows->fetch_assoc()): $ada = true; ?>
        <tr>
            <td>#<?= (int)$r['id_transaksi'] ?></td>
            <td><?= e($r['nama']) ?></td>
            <td><?= e($r['nama_layanan']) ?></td>
            <td><?= tgl($r['tanggal_masuk']) ?></td>
            <td><?= tgl($r['tanggal_selesai']) ?></td>
            <td><?= e(rtrim(rtrim($r['berat'], '0'), '.')) ?> kg</td>
            <td><?= rupiah($r['total_harga']) ?></td>
            <td>
                <!-- Ubah status langsung dari tabel -->
                <form action="transaksi-proses.php" method="post" class="d-flex gap-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="status">
                    <input type="hidden" name="id" value="<?= (int)$r['id_transaksi'] ?>">
                    <select name="status" class="form-select form-select-sm border-<?= $warna[$r['status']] ?>"
                            onchange="this.form.submit()">
                        <?php foreach ($warna as $s => $_): ?>
                            <option <?= $s === $r['status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <td class="text-end text-nowrap">
                <a href="nota.php?id=<?= (int)$r['id_transaksi'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Nota</a>
                <a href="transaksi-form.php?id=<?= (int)$r['id_transaksi'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <?php if (is_admin()): ?>
                <form action="transaksi-proses.php" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus transaksi ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$r['id_transaksi'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if (!$ada): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div></div>
<?php include 'footer.php'; ?>
