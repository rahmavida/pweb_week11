<?php
require 'config.php';
wajib_login();
$judul = 'Dashboard';

// Statistik ringkas
$jml_pelanggan = $db->query('SELECT COUNT(*) c FROM pelanggan')->fetch_assoc()['c'];
$jml_transaksi = $db->query('SELECT COUNT(*) c FROM transaksi')->fetch_assoc()['c'];
$jml_proses    = $db->query("SELECT COUNT(*) c FROM transaksi WHERE status = 'Proses'")->fetch_assoc()['c'];
$pendapatan    = $db->query("SELECT COALESCE(SUM(total_harga),0) s FROM transaksi
                             WHERE MONTH(tanggal_masuk) = MONTH(CURDATE()) AND YEAR(tanggal_masuk) = YEAR(CURDATE())")
                    ->fetch_assoc()['s'];

// Data grafik: 7 hari terakhir
$label = $trx = $omzet = [];
$res = $db->query("SELECT tanggal_masuk d, COUNT(*) jml, SUM(total_harga) total
                   FROM transaksi
                   WHERE tanggal_masuk >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                   GROUP BY tanggal_masuk ORDER BY tanggal_masuk");
$data = [];
while ($r = $res->fetch_assoc()) {
    $data[$r['d']] = $r;
}
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $label[] = date('d/m', strtotime($d));
    $trx[]   = (int)($data[$d]['jml'] ?? 0);
    $omzet[] = (int)($data[$d]['total'] ?? 0);
}

include 'header.php';
?>
<h3 class="mb-4">Dashboard</h3>
<div class="row g-3 mb-4">
    <?php
    $kartu = [
        ['Pelanggan', $jml_pelanggan, 'primary'],
        ['Total Transaksi', $jml_transaksi, 'success'],
        ['Sedang Diproses', $jml_proses, 'warning'],
        ['Pendapatan Bulan Ini', rupiah($pendapatan), 'info'],
    ];
    foreach ($kartu as [$t, $v, $w]): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-<?= $w ?> h-100">
                <div class="card-body">
                    <div class="text-muted small"><?= e($t) ?></div>
                    <div class="fs-4 fw-bold"><?= e($v) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h6>Jumlah Transaksi 7 Hari Terakhir</h6><canvas id="gTrx"></canvas>
    </div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-body">
        <h6>Pendapatan 7 Hari Terakhir (Rp)</h6><canvas id="gOmzet"></canvas>
    </div></div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const label = <?= json_encode($label) ?>;
new Chart(document.getElementById('gTrx'), {
    type: 'bar',
    data: { labels: label, datasets: [{ label: 'Transaksi', data: <?= json_encode($trx) ?> }] },
    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('gOmzet'), {
    type: 'line',
    data: { labels: label, datasets: [{ label: 'Pendapatan', data: <?= json_encode($omzet) ?>, tension: 0.3 }] },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
<?php include 'footer.php'; ?>
