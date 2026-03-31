<?php
require_once __DIR__ . '/../config/init.php';

$bulan = $_GET['bulan'] ?? date('Y-m');
$id_barang_atp = $_GET['id_barang_atp'] ?? '';
$id_supplier = $_GET['id_supplier'] ?? '';

$awal  = $bulan . '-01';
$akhir = date('Y-m-t', strtotime($awal));

/* =========================
   BARANG ATP (POWDER)
========================= */
$barang = mysqli_query($conn,"
SELECT b.id_barang,b.nama_barang,k.nama_kelompok
FROM barang b
JOIN kelompok_barang k ON k.id_kelompok=b.id_kelompok
WHERE k.nama_kelompok='Powder'
ORDER BY b.nama_barang
");

/* =========================
   SUPPLIER
========================= */
$supplier = mysqli_query($conn,"
SELECT id_supplier,nama_supplier
FROM supplier
ORDER BY nama_supplier
");

/* =========================
   DATA PRODUKSI
========================= */
$qProduksi=mysqli_query($conn,"
SELECT 
p.id_produksi,
p.tanggal,
b.nama_barang,
s.nama_supplier,
IFNULL(SUM(pd.mixer),0) AS mixer,
p.keterangan

FROM produksi p

JOIN barang b
ON b.id_barang=p.id_barang_atp

LEFT JOIN supplier s
ON s.id_supplier=p.id_supplier

LEFT JOIN produksi_detail pd
ON pd.id_produksi=p.id_produksi

WHERE p.tanggal BETWEEN '$awal' AND '$akhir'

".(!empty($id_barang_atp) ? " AND p.id_barang_atp='$id_barang_atp'" : "")."
".(!empty($id_supplier) ? " AND p.id_supplier='$id_supplier'" : "")."

GROUP BY p.id_produksi

ORDER BY p.tanggal ASC,p.id_produksi ASC
");

/* =========================
   STOK AWAL ATP
========================= */
$qStokAwal=mysqli_query($conn,"
SELECT IFNULL(SUM(a.atp),0) AS stok_awal
FROM at_detail a
WHERE a.tanggal BETWEEN '$awal' AND '$akhir'
".(!empty($id_barang_atp) ? " AND a.id_barang='$id_barang_atp'" : "")."
".(!empty($id_supplier) ? " AND a.id_supplier='$id_supplier'" : "")."
");

$stok_awal=mysqli_fetch_assoc($qStokAwal)['stok_awal'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Produksi</title>

<link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../dist/css/adminlte.min.css">

</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

<?php
$page='produksi';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">
Data produksi berhasil ditambahkan
</div>
<?php endif; ?>

<section class="content-header">

<div class="d-flex justify-content-between align-items-center">

<h1>Produksi</h1>

<button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
+ Input Produksi
</button>

</div>

</section>

<section class="content">

<!-- FILTER -->
<div class="card">
<div class="card-body">

<form method="get" class="form-inline">

<label class="mr-2">Bulan</label>
<input type="month" name="bulan" value="<?= $bulan ?>" class="form-control mr-2">

<label class="mr-2">ATP</label>
<select name="id_barang_atp" class="form-control mr-2">
<option value="">Semua</option>

<?php
mysqli_data_seek($barang,0);
while($b=mysqli_fetch_assoc($barang)):
?>

<option value="<?= $b['id_barang'] ?>" <?= ($id_barang_atp==$b['id_barang'])?'selected':'' ?> >
<?= htmlspecialchars($b['nama_barang']) ?>
</option>

<?php endwhile; ?>
</select>

<label class="mr-2">Supplier</label>
<select name="id_supplier" class="form-control mr-2">

<option value="">Semua</option>

<?php
mysqli_data_seek($supplier,0);
while($s=mysqli_fetch_assoc($supplier)):
?>

<option value="<?= $s['id_supplier'] ?>" <?= ($id_supplier==$s['id_supplier'])?'selected':'' ?> >
<?= htmlspecialchars($s['nama_supplier']) ?>
</option>

<?php endwhile; ?>

</select>

<button class="btn btn-secondary">Tampilkan</button>

</form>

</div>
</div>

<!-- TABLE -->
<div class="card">

<div class="card-header">
<h3 class="card-title">
Produksi Bulan <?= date('F Y',strtotime($awal)) ?>
</h3>
</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-sm">

<thead class="bg-light">
<tr>
<th>Tanggal</th>
<th>ATP</th>
<th>Supplier</th>
<th class="text-right">Stok Awal</th>
<th class="text-right">Mixer</th>
<th class="text-right">Saldo</th>
<th>Keterangan</th>
</tr>
</thead>

<tbody>

<?php
$saldo=$stok_awal;
?>

<tr class="bg-light">

<td><b><?= date('01-m-Y',strtotime($awal)) ?></b></td>
<td>-</td>
<td>-</td>

<td class="text-right">
<b><?= number_format($stok_awal,2) ?></b>
</td>

<td class="text-right">-</td>

<td class="text-right">
<b><?= number_format($saldo,2) ?></b>
</td>

<td><i>Stok awal dari Pemakaian AT</i></td>

</tr>

<?php while($r=mysqli_fetch_assoc($qProduksi)): ?>

<?php
$mixer=(float)$r['mixer'];
$stokAwalHariIni=$saldo;
$saldo-=$mixer;
?>

<tr>

<td><?= date('d-m-Y',strtotime($r['tanggal'])) ?></td>

<td><?= htmlspecialchars($r['nama_barang']) ?></td>

<td><?= htmlspecialchars($r['nama_supplier'] ?? '-') ?></td>

<td class="text-right">
<?= number_format($stokAwalHariIni,2) ?>
</td>

<td class="text-right">
<?= number_format($mixer,2) ?>
</td>

<td class="text-right font-weight-bold">
<?= number_format($saldo,2) ?>
</td>

<td>
<?= htmlspecialchars($r['keterangan'] ?? '') ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>
</div>

</section>

</div>


<!-- MODAL INPUT PRODUKSI -->

<div class="modal fade" id="modalTambah">

<div class="modal-dialog">

<form method="post" action="produksi_simpan.php">

<div class="modal-content">

<div class="modal-header">

<h4 class="modal-title">Input Produksi</h4>

<button type="button" class="close" data-dismiss="modal">&times;</button>

</div>

<div class="modal-body">

<div class="form-group">

<label>Tanggal</label>

<input type="date" name="tanggal" class="form-control" required>

</div>

<div class="form-group">

<label>ATP (Powder)</label>

<select name="id_barang_atp" class="form-control" required>

<option value="">-- Pilih ATP --</option>

<?php
mysqli_data_seek($barang,0);
while($b=mysqli_fetch_assoc($barang)):
?>

<option value="<?= $b['id_barang'] ?>">
<?= htmlspecialchars($b['nama_barang']) ?>
</option>

<?php endwhile; ?>

</select>

</div>

<div class="form-group">

<label>Supplier</label>

<select name="id_supplier" class="form-control" required>

<option value="">-- Pilih Supplier --</option>

<?php
mysqli_data_seek($supplier,0);
while($s=mysqli_fetch_assoc($supplier)):
?>

<option value="<?= $s['id_supplier'] ?>">
<?= htmlspecialchars($s['nama_supplier']) ?>
</option>

<?php endwhile; ?>

</select>

</div>

<div class="form-group">

<label>Mixer</label>

<input type="number" step="0.01" name="mixer" class="form-control" required>

</div>

<div class="form-group">

<label>Keterangan</label>

<input type="text" name="keterangan" class="form-control">

</div>

</div>

<div class="modal-footer">

<button type="submit" class="btn btn-success">
Simpan
</button>

<button type="button" class="btn btn-secondary" data-dismiss="modal">
Batal
</button>

</div>

</div>

</form>

</div>

</div>


<?php include "../footer.php"; ?>

</div>


<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>

</body>
</html>