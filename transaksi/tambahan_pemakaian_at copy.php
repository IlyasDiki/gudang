<?php
require '../config/init.php';

/* =========================
   DATA TERAKHIR
========================= */
$qLast = mysqli_query($conn,"
    SELECT * FROM tambahan
    ORDER BY tanggal DESC, id_tambahan DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Tambahan Pemakaian AT</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">
<div class="content-wrapper p-3">

<div class="card shadow">
<div class="card-header bg-warning">
    <h4 class="mb-0">INPUT TAMBAHAN PEMAKAIAN AT</h4>
</div>

<div class="card-body">

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Data berhasil disimpan</div>
<?php endif; ?>

<form method="POST" action="tambahan_pemakaian_at_simpan.php">

<div class="row">

    <div class="col-md-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" 
               class="form-control" 
               value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="col-md-3">
        <label>Nama Item</label>
        <input type="text" name="nama_item" 
               class="form-control" required>
    </div>

    <div class="col-md-3">
        <label>Jenis</label>
        <select name="jenis" class="form-control" required>
            <option value="">-- Pilih Jenis --</option>
            <option value="Pemakaian">Pemakaian</option>
            <option value="Tambahan">Tambahan</option>
            <option value="Lainnya">Lainnya</option>
        </select>
    </div>

    <div class="col-md-3">
        <label>Jumlah (Kg)</label>
        <input type="number" step="0.01" 
               name="jumlah" 
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
</div>

</form>

<hr>

<h5>10 Data Terakhir</h5>

<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
    <th>Tanggal</th>
    <th>Nama Item</th>
    <th>Jenis</th>
    <th>Jumlah</th>
    <th>Keterangan</th>
</tr>
</thead>
<tbody>

<?php if(mysqli_num_rows($qLast)==0): ?>
<tr>
    <td colspan="5" class="text-center">Belum ada data</td>
</tr>
<?php else: ?>
<?php while($r = mysqli_fetch_assoc($qLast)): ?>
<tr>
    <td><?= date('d-M-Y', strtotime($r['tanggal'])) ?></td>
    <td><?= htmlspecialchars($r['nama_item']) ?></td>
    <td><?= htmlspecialchars($r['jenis']) ?></td>
    <td><?= number_format($r['jumlah'],2) ?></td>
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