<?php
require '../config/init.php';

/* =========================
   AMBIL JENIS MUTASI
========================= */
$kode_jenis = $_GET['jenis'] ?? '';

$qJenis = mysqli_query($conn, "
  SELECT * FROM jenis_mutasi 
  WHERE tipe = 'KOREKSI'
  ORDER BY nama_jenis
");

$jenisAktif = null;
$idJenis = 0;

if ($kode_jenis != '') {
  $qAktif = mysqli_query($conn, "
    SELECT * FROM jenis_mutasi 
    WHERE kode_jenis='$kode_jenis'
  ");
  $jenisAktif = mysqli_fetch_assoc($qAktif);
  $idJenis = $jenisAktif['id_jenis'] ?? 0;
}

/* =========================
   DATA BARANG
========================= */
$barang = mysqli_query($conn, "SELECT * FROM barang ORDER BY nama_barang");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mutasi Stok</title>
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
$page = 'mutasi';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">
<section class="content-header">
  <h1>Mutasi Stok (Koreksi)</h1>
</section>

<section class="content">
<div class="card">

<div class="card-header">
  <h3 class="card-title">Input Koreksi Stok</h3>
</div>

<form method="post" action="mutasi_simpan.php">
<div class="card-body">

<div class="row mb-3">
  <div class="col-md-4">
    <label>Jenis Mutasi</label>
    <select class="form-control"
      onchange="location='mutasi.php?jenis='+this.value" required>
      <option value="">-- Pilih Jenis --</option>
      <?php while($j = mysqli_fetch_assoc($qJenis)) { ?>
        <option value="<?= $j['kode_jenis'] ?>"
          <?= $kode_jenis == $j['kode_jenis'] ? 'selected' : '' ?>>
          <?= $j['nama_jenis'] ?>
        </option>
      <?php } ?>
    </select>
  </div>
</div>

<?php if ($idJenis > 0): ?>

<hr>

<div class="row mb-3">
  <div class="col-md-3">
    <label>Tanggal</label>
    <input type="date" name="tanggal" class="form-control" required>
  </div>

  <div class="col-md-5">
    <label>Barang</label>
    <select name="id_barang" class="form-control" required>
      <option value="">-- Pilih Barang --</option>
      <?php while($b = mysqli_fetch_assoc($barang)) { ?>
        <option value="<?= $b['id_barang'] ?>">
          <?= $b['nama_barang'] ?>
        </option>
      <?php } ?>
    </select>
  </div>

  <div class="col-md-4">
  <label>Stok Sistem Saat Ini</label>
  <input type="text" id="stok_sistem" class="form-control" readonly value="0">
</div>

  <div class="col-md-4">
    <label>Stok Fisik</label>
    <input type="number" step="0.01" name="stok_fisik"
      class="form-control" required>
  </div>
</div>

<input type="hidden" name="kode_jenis" value="<?= $kode_jenis ?>">

<?php else: ?>
<div class="alert alert-info">
  Pilih jenis mutasi terlebih dahulu.
</div>
<?php endif; ?>

</div>

<div class="card-footer text-right">
  <?php if ($idJenis > 0): ?>
  <button class="btn btn-success">
    <i class="fa fa-save"></i> Simpan
  </button>
  <?php endif; ?>
</div>

</form>
</div>
</section>
</div>

<?php include "../footer.php"; ?>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>
<script>
$(document).ready(function(){

  $("select[name='id_barang']").on("change", function(){
    let id_barang = $(this).val();
    if(id_barang == "") {
      $("#stok_sistem").val("0");
      return;
    }

    $.get("mutasi_get_stok.php?id_barang=" + id_barang, function(res){
      let data = JSON.parse(res);
      $("#stok_sistem").val(data.stok);
    });
  });

});
</script>
</body>
</html>
