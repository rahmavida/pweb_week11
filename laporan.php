<?php
require 'config.php';
wajib_role('admin');
$judul = 'Laporan';

// Filter tanggal. Default: bulan berjalan. Periode cepat: hari, minggu, bulan
$periode = $_GET['periode'] ?? '';
$dari    = $_GET['dari'] ?? date('Y-m-01');
$sampai  = $_GET['sampai'] ?? date('Y-m-d');

if ($periode === 'hari') {
    $dari = $sampai = date('Y-m-d');
} elseif ($periode === 'minggu') {
    $dari = date('Y-m-d', strtotime('monday this week'));
    $sampai = date('Y-m-d');
} elseif ($periode === 'bulan') {
    $dari = date('Y-m-01');
    $sampai = date('Y-m-d');
}

$ok = fn($d) => ($x = DateTime::createFromFormat('Y-m-d', $d)) && $x->format('Y-m-d') === $d;
if (!$ok($dari) || !$ok($sampai)) {
    $dari = date('Y-m-01');
    $sampai = date('Y-m-d');
}

$stmt = $db->prepare('SELECT t.*, p.nama, l.nama_layanan
                      FROM transaksi t
                      JOIN pelanggan p ON p.id_pelanggan = t.id_pelanggan
                      JOIN layanan l ON l.id_layanan = t.id_layanan
                      WHERE t.tanggal_masuk BETWEEN ? AND ?
                      ORDER BY t.tanggal_masuk, t.id_transaksi');
$stmt->bind_param('ss', $dari, $sampai);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = array_sum(array_column($rows, 'total_harga'));
$kg    = array_sum(array_column($rows, 'berat'));

// Rekap per hari
$harian = [];
foreach ($rows as $r) {
    $harian[$r['tanggal_masuk']]['jml'] = ($harian[$r['tanggal_masuk']]['jml'] ?? 0) + 1;
    $harian[$r['tanggal_masuk']]['total'] = ($harian[$r['tanggal_masuk']]['total'] ?? 0) + $r['total_harga'];
}

include 'header.php';
?>
<h3 class="mb-3">Laporan Pendapatan</h3>

<form class="row g-2 align-items-end mb-3" method="get">
    <div class="col-auto"><label class="form-label mb-0">Dari</label>
        <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control"></div>
    <div class="col-auto"><label class="form-label mb-0">Sampai</label>
        <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control"></div>
    <div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div>
    <div class="col-auto ms-md-3">
        <a href="?periode=hari" class="btn btn-outline-secondary">Hari ini</a>
        <a href="?periode=minggu" class="btn btn-outline-secondary">Minggu ini</a>
        <a href="?periode=bulan" class="btn btn-outline-secondary">Bulan ini</a>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Total Pendapatan</div><div class="fs-4 fw-bold"><?= rupiah($total) ?></div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Jumlah Transaksi</div><div class="fs-4 fw-bold"><?= count($rows) ?></div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Total Berat</div><div class="fs-4 fw-bold"><?= e(rtrim(rtrim(number_format($kg, 2, '.', ''), '0'), '.') ?: '0') ?> kg</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-header">Rekap Harian</div>
        <table class="table table-sm mb-0">
            <thead><tr><th>Tanggal</th><th>Transaksi</th><th class="text-end">Pendapatan</th></tr></thead>
            <tbody>
            <?php foreach ($harian as $d => $h): ?>
                <tr><td><?= tgl($d) ?></td><td><?= (int)$h['jml'] ?></td><td class="text-end"><?= rupiah($h['total']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$harian): ?><tr><td colspan="3" class="text-center text-muted">Tidak ada data.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Rincian Transaksi</div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead><tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Layanan</th><th>Status</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id_transaksi'] ?></td><td><?= tgl($r['tanggal_masuk']) ?></td>
                    <td><?= e($r['nama']) ?></td><td><?= e($r['nama_layanan']) ?></td>
                    <td><?= e($r['status']) ?></td><td class="text-end"><?= rupiah($r['total_harga']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
<?php include 'footer.php'; ?>
