<?php
require 'config.php';
wajib_login();
cek_csrf();

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($aksi === 'simpan') {
    $nama   = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');

    // Validasi sisi server
    $err = [];
    if ($nama === '' || mb_strlen($nama) > 64)     $err[] = 'Nama wajib diisi (maks 64 karakter).';
    if ($alamat === '' || mb_strlen($alamat) > 255) $err[] = 'Alamat wajib diisi (maks 255 karakter).';
    if (!preg_match('/^[0-9+]{8,20}$/', $no_hp))   $err[] = 'No HP harus 8 sampai 20 digit angka.';
    if ($err) {
        flash('danger', implode(' ', $err));
        redirect($id ? "pelanggan-form.php?id=$id" : 'pelanggan-form.php');
    }

    if ($id > 0) {
        $stmt = $db->prepare('UPDATE pelanggan SET nama = ?, alamat = ?, no_hp = ? WHERE id_pelanggan = ?');
        $stmt->bind_param('sssi', $nama, $alamat, $no_hp, $id);
        $stmt->execute();
        flash('success', 'Data pelanggan diperbarui.');
    } else {
        $stmt = $db->prepare('INSERT INTO pelanggan (nama, alamat, no_hp) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $nama, $alamat, $no_hp);
        $stmt->execute();
        flash('success', 'Pelanggan baru ditambahkan.');
    }
    redirect('pelanggan.php');
}

if ($aksi === 'hapus') {
    wajib_role('admin');
    try {
        $stmt = $db->prepare('DELETE FROM pelanggan WHERE id_pelanggan = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        flash('success', 'Pelanggan dihapus.');
    } catch (mysqli_sql_exception $e) {
        // Gagal karena foreign key: pelanggan masih punya transaksi
        flash('danger', 'Pelanggan tidak bisa dihapus karena sudah punya transaksi.');
    }
    redirect('pelanggan.php');
}

redirect('pelanggan.php');
