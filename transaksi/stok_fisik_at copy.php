<?php
require '../config/init.php';

/* =========================
   LIST BARANG (Kelompok AT)
========================= */
$qBarang = mysqli_query($conn,"
    SELECT id_barang, kode_barang, nama_barang
    FROM barang
    WHERE id_kelompok = '6'
    ORDER BY nama_barang ASC
");

/* =========================
   DATA TERAKHIR
========================= */
$qLast = mysqli_query($conn,"
    SELECT s.*, b.nama_barang 
    FROM stok_fisik_at s
    JOIN barang b ON s.id_barang = b.id_barang
    ORDER BY s.tanggal DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Input Stok Fisik AT</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<div class="content-wrapper p-3">

<div class="card shadow">
<div class="card-header bg-info text-white">
    <h4 class="mb-0">INPUT STOK FISIK AT</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="row">

    <div class="col-md-4">
        <label>Tanggal</label>
        <input type="date" name="tanggal" 
               class="form-control" 
               value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="col-md-4">
        <label>Barang</label>
        <select name="id_barang" class="form-control" required>
            <option value="">-- Pilih Barang --</option>
            <?php while($b = mysqli_fetch_assoc($qBarang)): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= htmlspecialchars($b['kode_barang']." - ".$b['nama_barang']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label>Jumlah Fisik Habis (Kg)</label>
        <input type="number" step="0.01" 
               name="jumlah_kg" 
               class="form-control" required>
    </div>

</div>

<div class="mt-3">
    <label>Keterangan</label>
    <textarea name="keterangan" 
              class="form-control" 
              rows="2"></textarea>
</div>

<div class="mt-4">
    <button type="submit" name="simpan" class="btn btn-success">
        <i class="fa fa-save"></i> Simpan
    </button>
    <a href="../index.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Kembali
    </a>
</div>

</form>

<hr>

<h5>Data Terakhir</h5>

<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Jumlah (Kg)</th>
    <th>Keterangan</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($qLast)==0): ?>
<tr>
    <td colspan="4" class="text-center">Belum ada data</td>
</tr>
<?php else: ?>
<?php while($r = mysqli_fetch_assoc($qLast)): ?>
<tr>
    <td><?= date('d-M-Y', strtotime($r['tanggal'])) ?></td>
    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= number_format($r['jumlah_kg'],2) ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

</div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

</body>
</html>