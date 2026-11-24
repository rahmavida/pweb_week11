<?php
// api.php : API JSON opsional. Dipanggil dengan ?r=...
// Contoh:
//   GET  api.php?r=pelanggan
//   POST api.php?r=transaksi          (body JSON: id_pelanggan, id_layanan, berat, tanggal_masuk, tanggal_selesai)
//   GET  api.php?r=transaksi&id=1
//   PUT  api.php?r=transaksi&id=1     (body JSON: {"status":"Selesai"})
//   GET  api.php?r=laporan&periode=bulan
// Autentikasi memakai session login yang sama dengan aplikasi web.
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['user'])) {
    json_out(['error' => 'Belum login'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$r  = $_GET['r'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Metode yang mengubah data wajib membawa token CSRF di header X-CSRF-Token
if ($method !== 'GET') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        json_out(['error' => 'Token CSRF tidak valid'], 400);
    }
}

if ($r === 'pelanggan' && $method === 'GET') {
    json_out($db->query('SELECT * FROM pelanggan ORDER BY nama')->fetch_all(MYSQLI_ASSOC));
}

if ($r === 'transaksi') {
    $sel = 'SELECT t.*, p.nama, l.nama_layanan FROM transaksi t
            JOIN pelanggan p ON p.id_pelanggan = t.id_pelanggan
            JOIN layanan l ON l.id_layanan = t.id_layanan';

    if ($method === 'GET' && $id > 0) {
        $stmt = $db->prepare($sel . ' WHERE t.id_transaksi = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $row ? json_out($row) : json_out(['error' => 'Tidak ditemukan'], 404);
    }

    if ($method === 'POST') {
        $id_pel = (int)($body['id_pelanggan'] ?? 0);
        $id_lay = (int)($body['id_layanan'] ?? 0);
        $berat  = (float)($body['berat'] ?? 0);
        $masuk  = $body['tanggal_masuk'] ?? date('Y-m-d');
        $selesai = $body['tanggal_selesai'] ?? date('Y-m-d', strtotime('+2 day'));
        if ($id_pel <= 0 || $id_lay <= 0 || $berat <= 0) {
            json_out(['error' => 'id_pelanggan, id_layanan, dan berat wajib valid'], 422);
        }
        $stmt = $db->prepare('SELECT harga_per_kg FROM layanan WHERE id_layanan = ?');
        $stmt->bind_param('i', $id_lay);
        $stmt->execute();
        $lay = $stmt->get_result()->fetch_assoc();
        if (!$lay) json_out(['error' => 'Layanan tidak ditemukan'], 422);
        $total = (int)round($lay['harga_per_kg'] * $berat);
        try {
            $stmt = $db->prepare("INSERT INTO transaksi (id_pelanggan,id_layanan,tanggal_masuk,tanggal_selesai,berat,total_harga,status)
                                  VALUES (?,?,?,?,?,?, 'Proses')");
            $stmt->bind_param('iissdi', $id_pel, $id_lay, $masuk, $selesai, $berat, $total);
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            json_out(['error' => 'Data tidak valid'], 422);
        }
        json_out(['id_transaksi' => $db->insert_id, 'total_harga' => $total], 201);
    }

    if ($method === 'PUT' && $id > 0) {
        $status = $body['status'] ?? '';
        if (!in_array($status, ['Proses', 'Selesai', 'Sudah Diambil'], true)) {
            json_out(['error' => 'Status tidak valid'], 422);
        }
        $stmt = $db->prepare('UPDATE transaksi SET status = ? WHERE id_transaksi = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        json_out(['id_transaksi' => $id, 'status' => $status]);
    }
}

if ($r === 'laporan' && $method === 'GET') {
    if (!is_admin()) json_out(['error' => 'Khusus admin'], 403);
    $dari = ($_GET['periode'] ?? 'bulan') === 'hari' ? date('Y-m-d') : date('Y-m-01');
    $stmt = $db->prepare('SELECT COUNT(*) jumlah_transaksi, COALESCE(SUM(total_harga),0) total_pendapatan
                          FROM transaksi WHERE tanggal_masuk BETWEEN ? AND CURDATE()');
    $stmt->bind_param('s', $dari);
    $stmt->execute();
    json_out(['dari' => $dari, 'sampai' => date('Y-m-d')] + $stmt->get_result()->fetch_assoc());
}

json_out(['error' => 'Endpoint tidak dikenal'], 404);
