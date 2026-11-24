<?php
require 'config.php';
wajib_role('admin');
cek_csrf();

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($aksi === 'simpan') {
    $nama  = trim($_POST['nama_layanan'] ?? '');
    $harga = (int)($_POST['harga_per_kg'] ?? 0);

    if ($nama === '' || mb_strlen($nama) > 64 || $harga <= 0) {
        flash('danger', 'Nama layanan wajib diisi dan harga harus lebih dari 0.');
        redirect($id ? "layanan-form.php?id=$id" : 'layanan-form.php');
    }

    if ($id > 0) {
        $stmt = $db->prepare('UPDATE layanan SET nama_layanan = ?, harga_per_kg = ? WHERE id_layanan = ?');
        $stmt->bind_param('sii', $nama, $harga, $id);
        $stmt->execute();
        flash('success', 'Layanan diperbarui.');
    } else {
        $stmt = $db->prepare('INSERT INTO layanan (nama_layanan, harga_per_kg) VALUES (?, ?)');
        $stmt->bind_param('si', $nama, $harga);
        $stmt->execute();
        flash('success', 'Layanan baru ditambahkan.');
    }
    redirect('layanan.php');
}

if ($aksi === 'hapus') {
    try {
        $stmt = $db->prepare('DELETE FROM layanan WHERE id_layanan = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        flash('success', 'Layanan dihapus.');
    } catch (mysqli_sql_exception $e) {
        flash('danger', 'Layanan tidak bisa dihapus karena sudah dipakai transaksi.');
    }
}
redirect('layanan.php');
