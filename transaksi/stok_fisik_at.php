<?php
require '../config/init.php';

/* =========================
   LIST SUPPLIER
========================= */
$qSupplier = mysqli_query($conn,"
    SELECT id_supplier, nama_supplier
    FROM supplier
    ORDER BY nama_supplier ASC
");

/* =========================
   DATA TERAKHIR
========================= */
$qLast = mysqli_query($conn,"
    SELECT s.*, sp.nama_supplier 
    FROM stok_fisik_at s
    LEFT JOIN supplier sp ON sp.id_supplier = s.id_supplier
    ORDER BY s.tanggal DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Stok Fisik AT</title>

  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php
$page = 'stok_fisik_at';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible">
    Data berhasil disimpan
  </div>
<?php endif; ?>

<section class="content-header">
  <div class="d-flex justify-content-between align-items-center">
    <h1>Stok Fisik AT (Manual)</h1>
  </div>
</section>

<section class="content">

<div class="card">
<div class="card-body">

<form method="POST" action="stok_fisik_at_simpan.php">

<div class="row">

    <div class="col-md-4">
        <label>Tanggal</label>
        <input type="date" name="tanggal" 
               class="form-control" 
               value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="col-md-4">
        <label>Supplier</label>
        <select name="id_supplier" class="form-control" required>
            <option value="">-- Pilih Supplier --</option>
            <?php while($s = mysqli_fetch_assoc($qSupplier)): ?>
                <option value="<?= $s['id_supplier'] ?>">
                    <?= htmlspecialchars($s['nama_supplier']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label>Jumlah Fisik Habis (Kg)</label>
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

<h5>Data Terakhir</h5>

<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
    <th>Tanggal</th>
    <th>Supplier</th>
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
    <td><?= htmlspecialchars($r['nama_supplier'] ?? '-') ?></td>
    <td><?= number_format($r['jumlah'],2) ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>

</table>

</div>
</div>

</section>
</div>

<?php include "../footer.php"; ?>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>

</body>
</html>