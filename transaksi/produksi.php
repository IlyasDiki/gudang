<?php
require_once __DIR__ . '/../config/init.php';

/* =========================
   FILTER
========================= */
$bulan         = $_GET['bulan'] ?? date('Y-m');
$id_barang_atp = $_GET['id_barang_atp'] ?? '';
$id_supplier   = $_GET['id_supplier'] ?? '';

$awal  = $bulan . '-01';
$akhir = date('Y-m-t', strtotime($awal));

/* =========================
   BARANG ATP
========================= */
$barang = mysqli_query($conn, "
SELECT id_barang, nama_barang
FROM barang
WHERE id_kelompok = (
    SELECT id_kelompok FROM kelompok_barang WHERE nama_kelompok='Powder'
)
ORDER BY nama_barang
");

/* =========================
   SUPPLIER
========================= */
$supplier = mysqli_query($conn, "
SELECT id_supplier, nama_supplier
FROM supplier
ORDER BY nama_supplier
");

/* =========================
   DATA PRODUKSI
========================= */
$qProduksi = mysqli_query($conn, "
SELECT 
    p.id_produksi,
    p.tanggal,
    b.nama_barang,
    s.nama_supplier,
    IFNULL(SUM(pd.mixer),0) AS mixer,
    p.keterangan
FROM produksi p
JOIN barang b ON b.id_barang = p.id_barang_atp
LEFT JOIN supplier s ON s.id_supplier = p.id_supplier
LEFT JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
WHERE p.tanggal BETWEEN '$awal' AND '$akhir'
".(!empty($id_barang_atp) ? " AND p.id_barang_atp='$id_barang_atp'" : "")."
".(!empty($id_supplier) ? " AND p.id_supplier='$id_supplier'" : "")."
GROUP BY p.id_produksi
ORDER BY p.tanggal ASC
");
?>

<!DOCTYPE html>
<html lang="id">
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

<!-- ========================= FORM INPUT ========================= -->
<form method="post" action="produksi_simpan.php">

<div class="card">
<div class="card-header">
<h3 class="card-title">Input Produksi</h3>
</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">
<label>Tanggal</label>
<input type="date" name="tanggal" class="form-control form-control-sm" required>
</div>

<div class="col-md-3">
<label>ATP</label>
<select name="id_barang_atp" id="id_barang_atp" class="form-control form-control-sm" required>
<option value="">-- Pilih --</option>
<?php while($b=mysqli_fetch_assoc($barang)): ?>
<option value="<?= $b['id_barang'] ?>">
<?= htmlspecialchars($b['nama_barang']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-md-3">
<label>Supplier</label>
<select name="id_supplier" id="id_supplier" class="form-control form-control-sm" required>
<option value="">-- Pilih --</option>
<?php while($s=mysqli_fetch_assoc($supplier)): ?>
<option value="<?= $s['id_supplier'] ?>">
<?= htmlspecialchars($s['nama_supplier']) ?>
</option>
<?php endwhile; ?>
</select>
</div>

</div>

<hr>

<!-- BOX SALDO -->
<div class="row mt-2">

<div class="col-md-3">
<label>Total ATP</label>
<input type="text" id="total_atp"
class="form-control form-control-sm"
value="0" readonly>
</div>

<div class="col-md-3">
<label>Total Mixer</label>
<input type="text" id="total_mixer"
class="form-control form-control-sm"
value="0" readonly>
</div>

<div class="col-md-3">
<label>Saldo ATP</label>
<input type="text" id="saldo_atp"
class="form-control form-control-sm"
value="0" readonly>
</div>

</div>

<hr>

<div id="formInput" style="display:none;">
<div class="row">

<div class="col-md-3">
<label>Mixer</label>
<input type="number" step="0.01" name="mixer" class="form-control form-control-sm" required>
</div>

<div class="col-md-6">
<label>Keterangan</label>
<input type="text" name="keterangan" class="form-control form-control-sm">
</div>

</div>
</div>

</div>

<div class="card-footer">
<button class="btn btn-success">
<i class="fa fa-save"></i> Simpan
</button>
</div>

</div>

</form>

<!-- ========================= TABLE ========================= -->
<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
<th>Tanggal</th>
<th>ATP</th>
<th>Supplier</th>
<th class="text-right">Mixer</th>
<th>Keterangan</th>
</tr>
</thead>

<tbody>

<?php while($r=mysqli_fetch_assoc($qProduksi)): ?>
<tr>
<td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
<td><?= htmlspecialchars($r['nama_barang']) ?></td>
<td><?= htmlspecialchars($r['nama_supplier'] ?? '-') ?></td>
<td class="text-right"><?= number_format($r['mixer'],2) ?></td>
<td><?= htmlspecialchars($r['keterangan'] ?? '') ?></td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

</div>

<?php include "../footer.php"; ?>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>

<script>
$('#id_supplier').change(function(){

    let supplier = $(this).val();

    if(!supplier){
        $('#total_atp').val('0');
        $('#total_mixer').val('0');
        $('#saldo_atp').val('Stok tidak ada');
        $('#formInput').hide();
        return;
    }

    fetch('ajax_get_saldo_atp.php?id_supplier=' + supplier)
    .then(res => res.json())
    .then(data => {

        console.log(data); // DEBUG

        if(data.status !== 'ok'){
            $('#saldo_atp').val('Error');
            return;
        }

        $('#total_atp').val(data.total_atp);
        $('#total_mixer').val(data.total_mixer);
        $('#saldo_atp').val(data.saldo);

        $('#formInput').show();
    })
    .catch(err => {
        console.log(err);
        $('#saldo_atp').val('Gagal load');
    });

});
</script>

</body>
</html>