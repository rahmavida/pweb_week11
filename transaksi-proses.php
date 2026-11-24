<?php
require 'config.php';
wajib_login();
cek_csrf();

const STATUS_VALID = ['Proses', 'Selesai', 'Sudah Diambil'];

function tanggal_valid(string $d): bool
{
    $x = DateTime::createFromFormat('Y-m-d', $d);
    return $x && $x->format('Y-m-d') === $d;
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($aksi === 'simpan') {
    $id_pel  = (int)($_POST['id_pelanggan'] ?? 0);
    $id_lay  = (int)($_POST['id_layanan'] ?? 0);
    $berat   = (float)($_POST['berat'] ?? 0);
    $masuk   = $_POST['tanggal_masuk'] ?? '';
    $selesai = $_POST['tanggal_selesai'] ?? '';
    $status  = $_POST['status'] ?? 'Proses';
    $balik   = $id ? "transaksi-form.php?id=$id" : 'transaksi-form.php';

    $err = [];
    if ($id_pel <= 0 || $id_lay <= 0)                    $err[] = 'Pelanggan dan layanan wajib dipilih.';
    if ($berat <= 0 || $berat > 999)                     $err[] = 'Berat harus antara 0,1 dan 999 kg.';
    if (!tanggal_valid($masuk) || !tanggal_valid($selesai)) $err[] = 'Format tanggal tidak valid.';
    elseif ($selesai < $masuk)                           $err[] = 'Tanggal selesai tidak boleh sebelum tanggal masuk.';
    if (!in_array($status, STATUS_VALID, true))          $err[] = 'Status tidak valid.';
    if ($err) {
        flash('danger', implode(' ', $err));
        redirect($balik);
    }

    // Harga diambil dari database, bukan dari browser, supaya tidak bisa dimanipulasi
    $stmt = $db->prepare('SELECT harga_per_kg FROM layanan WHERE id_layanan = ?');
    $stmt->bind_param('i', $id_lay);
    $stmt->execute();
    $lay = $stmt->get_result()->fetch_assoc();
    if (!$lay) {
        flash('danger', 'Layanan tidak ditemukan.');
        redirect($balik);
    }
    $total = (int)round($lay['harga_per_kg'] * $berat);

    try {
        if ($id > 0) {
            $stmt = $db->prepare('UPDATE transaksi SET id_pelanggan=?, id_layanan=?, tanggal_masuk=?, tanggal_selesai=?,
                                  berat=?, total_harga=?, status=? WHERE id_transaksi=?');
            $stmt->bind_param('iissdisi', $id_pel, $id_lay, $masuk, $selesai, $berat, $total, $status, $id);
            $stmt->execute();
            flash('success', 'Transaksi diperbarui.');
        } else {
            $stmt = $db->prepare('INSERT INTO transaksi (id_pelanggan, id_layanan, tanggal_masuk, tanggal_selesai,
                                  berat, total_harga, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iissdis', $id_pel, $id_lay, $masuk, $selesai, $berat, $total, $status);
            $stmt->execute();
            flash('success', 'Transaksi tersimpan. Total ' . rupiah($total) . '.');
        }
    } catch (mysqli_sql_exception $e) {
        flash('danger', 'Gagal menyimpan transaksi. Pastikan pelanggan dan layanan masih ada.');
        redirect($balik);
    }
    redirect('transaksi.php');
}

if ($aksi === 'status') {
    $status = $_POST['status'] ?? '';
    if ($id > 0 && in_array($status, STATUS_VALID, true)) {
        $stmt = $db->prepare('UPDATE transaksi SET status = ? WHERE id_transaksi = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        flash('success', "Status transaksi #$id diubah menjadi $status.");
    }
    redirect('transaksi.php');
}

if ($aksi === 'hapus') {
    wajib_role('admin');
    $stmt = $db->prepare('DELETE FROM transaksi WHERE id_transaksi = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'Transaksi dihapus.');
}
redirect('transaksi.php');
