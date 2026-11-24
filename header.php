<?php
// header.php : kerangka halaman (navbar Bootstrap dengan custom style). Panggil setelah config.php
wajib_login();
$halaman = basename($_SERVER['SCRIPT_NAME']);
function aktif(array $files): string
{
    global $halaman;
    return in_array($halaman, $files, true) ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul ?? 'LaundryCrafty') ?> | LaundryCrafty</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Style sesuai Tema image_1.png -->
    <style>
        /* Menggunakan font Georgia agar menghasilkan style serif yang elegan seperti SMK Coding */
        body {
            background-color: #fcf8f2 !important; /* Warna krem lembut */
            font-family: 'Georgia', 'Times New Roman', serif;
            color: #2b2b2b;
        }

        /* Navbar Marun */
        .navbar-custom {
            background-color: #5c1d24 !important; /* Warna marun gelap */
            border-bottom: 3px solid #4a151b;
        }
        .navbar-custom .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            font-style: italic;
            color: #ffffff !important;
            letter-spacing: 0.5px;
        }
        .navbar-custom .nav-link {
            color: #f5e6d3 !important; /* Krem terang untuk teks tidak aktif */
            font-weight: 500;
            margin-right: 5px;
        }
        .navbar-custom .nav-link:hover, 
        .navbar-custom .nav-link.active {
            color: #ffffff !important;
            font-weight: bold;
            text-decoration: underline; /* underline penanda aktif seperti referensi */
        }
        .navbar-custom .navbar-text {
            color: #f5e6d3 !important;
            font-size: 0.9rem;
        }
        .navbar-custom .btn-logout {
            color: #ffffff;
            border-color: #f5e6d3;
            font-weight: bold;
            border-radius: 4px;
        }
        .navbar-custom .btn-logout:hover {
            background-color: #ffffff;
            color: #5c1d24;
        }

        /* Heading Berwarna Marun Bold Italic */
        h3, .judul-halaman {
            font-style: italic;
            font-weight: bold;
            color: #5c1d24;
            margin-top: 15px;
            font-size: 2rem;
            border-bottom: 2px solid rgba(92, 29, 36, 0.1);
            padding-bottom: 10px;
        }

        /* Card Berwarna Putih dengan border melengkung halus */
        .card {
            background-color: #ffffff !important;
            border: 1px solid #e8dec9 !important; /* Border kecokelatan lembut */
            border-radius: 12px !important; /* Sudut melengkung */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); /* Soft shadow */
            overflow: hidden;
        }
        .card-header {
            background-color: #f9f3ea !important;
            border-bottom: 1px solid #e8dec9 !important;
            font-weight: bold;
            color: #5c1d24;
        }

        /* Label data */
        .text-muted {
            color: #7d6b58 !important; /* muted brown */
        }
        
        /* Tombol & Elemen Form Marun */
        .btn-primary {
            background-color: #5c1d24 !important;
            border-color: #5c1d24 !important;
            border-radius: 6px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #4a151b !important;
            border-color: #4a151b !important;
        }
        .btn-outline-primary {
            color: #5c1d24 !important;
            border-color: #5c1d24 !important;
            border-radius: 6px;
        }
        .btn-outline-primary:hover {
            background-color: #5c1d24 !important;
            color: #ffffff !important;
        }

        /* Input Form */
        .form-control, .form-select {
            border: 1px solid #d4c5b3;
            background-color: #ffffff;
            border-radius: 6px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #5c1d24;
            box-shadow: 0 0 0 0.25rem rgba(92, 29, 36, 0.25);
        }

        /* Tabel bergaya Vintage/Krim */
        .table {
            --bs-table-bg: transparent;
            color: #2b2b2b;
        }
        .table-light {
            background-color: #f9f3ea !important;
            color: #5c1d24 !important;
            font-weight: bold;
        }
        
        /* Alert Info/Sukses */
        .alert-success {
            background-color: #e2f0d9;
            border-color: #c5e0b4;
            color: #385723;
        }
        .alert-danger {
            background-color: #fce4d6;
            border-color: #f8cbad;
            color: #c65911;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">LaundryCrafty</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= aktif(['index.php']) ?>" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link<?= aktif(['pelanggan.php', 'pelanggan-form.php']) ?>" href="pelanggan.php">Pelanggan</a></li>
                <li class="nav-item"><a class="nav-link<?= aktif(['transaksi.php', 'transaksi-form.php']) ?>" href="transaksi.php">Transaksi</a></li>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link<?= aktif(['layanan.php', 'layanan-form.php']) ?>" href="layanan.php">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link<?= aktif(['laporan.php']) ?>" href="laporan.php">Laporan</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text me-3">
                <?= e($_SESSION['user']['username']) ?> (<?= e($_SESSION['user']['role']) ?>)
            </span>
            <a class="btn btn-logout btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>
<main class="container mb-5">
<?php if (!empty($_SESSION['flash'])): [$tipe, $pesan] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert alert-<?= e($tipe) ?> alert-dismissible fade show">
        <?= e($pesan) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>