<?php
require 'config.php';
wajib_role('admin');
$judul = 'Layanan';
$rows = $db->query('SELECT * FROM layanan ORDER BY nama_layanan');
include 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Jenis Layanan</h3>
    <a href="layanan-form.php" class="btn btn-primary">+ Tambah Layanan</a>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Nama Layanan</th><th>Harga per Kg</th><th class="text-end">Aksi</th></tr>
    </thead>
    <tbody>
    <?php $no = 1; while ($r = $rows->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= e($r['nama_layanan']) ?></td>
            <td><?= rupiah($r['harga_per_kg']) ?></td>
            <td class="text-end">
                <a href="layanan-form.php?id=<?= (int)$r['id_layanan'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <form action="layanan-proses.php" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus layanan ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$r['id_layanan'] ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div></div>
<?php include 'footer.php'; ?>
