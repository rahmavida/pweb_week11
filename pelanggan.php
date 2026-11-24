<?php
require 'config.php';
wajib_login();
$judul = 'Pelanggan';

// Pencarian berdasarkan nama atau no HP
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $db->prepare('SELECT * FROM pelanggan WHERE nama LIKE ? OR no_hp LIKE ? ORDER BY nama');
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result();
} else {
    $rows = $db->query('SELECT * FROM pelanggan ORDER BY nama');
}

include 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Data Pelanggan</h3>
    <a href="pelanggan-form.php" class="btn btn-primary">+ Tambah Pelanggan</a>
</div>

<form class="row g-2 mb-3" method="get">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Cari nama atau no HP">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary">Cari</button>
        <?php if ($q !== ''): ?><a href="pelanggan.php" class="btn btn-outline-secondary">Reset</a><?php endif; ?>
    </div>
</form>

<div class="card"><div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Nama</th><th>Alamat</th><th>No HP</th><th class="text-end">Aksi</th></tr>
    </thead>
    <tbody>
    <?php $no = 1; while ($r = $rows->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= e($r['nama']) ?></td>
            <td><?= e($r['alamat']) ?></td>
            <td><?= e($r['no_hp']) ?></td>
            <td class="text-end">
                <a href="pelanggan-form.php?id=<?= (int)$r['id_pelanggan'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <?php if (is_admin()): ?>
                <form action="pelanggan-proses.php" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus pelanggan ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$r['id_pelanggan'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if ($no === 1): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div></div>
<?php include 'footer.php'; ?>
