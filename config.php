<?php
// config.php : koneksi database, session, dan fungsi bantu
// Sesuaikan 4 konstanta ini dengan XAMPP / Laragon kamu.
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'laundrycrafty';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal. Pastikan MySQL menyala dan database.sql sudah diimpor.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape output agar aman dari XSS
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function rupiah($n): string
{
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

function tgl($d): string
{
    return $d ? date('d-m-Y', strtotime($d)) : '-';
}

// Autentikasi dan role
function wajib_login(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function wajib_role(string $role): void
{
    wajib_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        die('Akses ditolak: halaman ini khusus ' . e($role) . '.');
    }
}

function is_admin(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}

// CSRF token untuk semua form POST
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function cek_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Permintaan tidak valid (CSRF).');
    }
}

// Pesan sekali tampil (flash)
function flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = [$tipe, $pesan];
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
