<?php
require 'config.php';
wajib_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT t.*, p.nama, p.no_hp, l.nama_layanan, l.harga_per_kg
                      FROM transaksi t
                      JOIN pelanggan p ON p.id_pelanggan = t.id_pelanggan
                      JOIN layanan l ON l.id_layanan = t.id_layanan
                      WHERE t.id_transaksi = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
if (!$t) {
    http_response_code(404);
    die('Transaksi tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota #<?= (int)$t['id_transaksi'] ?></title>
    <style>
        body { font-family: monospace; max-width: 320px; margin: 20px auto; font-size: 13px; }
        h2, p { text-align: center; margin: 2px 0; }
        hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; }
        td:last-child { text-align: right; }
        .no-print { text-align: center; margin-top: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h2>LAUNDRYCRAFTY</h2>
    <p>Nota Transaksi #<?= (int)$t['id_transaksi'] ?></p>
    <hr>
    <table>
        <tr><td>Pelanggan</td><td><?= e($t['nama']) ?></td></tr>
        <tr><td>No HP</td><td><?= e($t['no_hp']) ?></td></tr>
        <tr><td>Masuk</td><td><?= tgl($t['tanggal_masuk']) ?></td></tr>
        <tr><td>Selesai</td><td><?= tgl($t['tanggal_selesai']) ?></td></tr>
    </table>
    <hr>
    <table>
        <tr><td>Layanan</td><td><?= e($t['nama_layanan']) ?></td></tr>
        <tr><td>Harga/kg</td><td><?= rupiah($t['harga_per_kg']) ?></td></tr>
        <tr><td>Berat</td><td><?= e(rtrim(rtrim($t['berat'], '0'), '.')) ?> kg</td></tr>
    </table>
    <hr>
    <table>
        <tr><td><strong>TOTAL</strong></td><td><strong><?= rupiah($t['total_harga']) ?></strong></td></tr>
        <tr><td>Status</td><td><?= e($t['status']) ?></td></tr>
    </table>
    <hr>
    <p>Terima kasih</p>
    <div class="no-print"><button onclick="window.print()">Cetak</button></div>
</body>
</html>
