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

$supplier = mysqli_query($conn, "
SELECT id_supplier, nama_supplier
FROM supplier
ORDER BY nama_supplier
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
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
</head>
<style>
#btnPlus.active {
  background-color: #198754;
  color: white;
}

#btnMinus.active {
  background-color: #dc3545;
  color: white;
}
</style>
<style>
.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: .25rem;
    height: calc(2.25rem + 2px);
    padding: .375rem .75rem;
}
#previewStok {
    font-size: 14px;
}

#stok_sistem {
    font-weight: bold;
}
</style>
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

<div class="row">

  <!-- TANGGAL -->
  <div class="col-md-3">
    <label>Tanggal</label>
    <input type="date" name="tanggal" class="form-control" required>
  </div>

  <!-- BARANG -->
  <div class="col-md-4">
    <label>Barang</label>
    <select name="id_barang" id="barang" class="form-control select2bs4" required>
      <option value="">-- Cari Barang --</option>
      <?php while($b = mysqli_fetch_assoc($barang)) { ?>
      <option 
        value="<?= $b['id_barang'] ?>"
        data-supplier="<?= $b['pakai_supplier'] ?>"
      >
        <?= $b['nama_barang'] ?>
      </option>
      <?php } ?>
    </select>
  </div>

  <!-- SUPPLIER -->
  <div class="col-md-3" id="supplierBox" style="display:none;">
    <label>Supplier</label>
    <select name="id_supplier" id="supplier" class="form-control select2bs4">
      <option value="">-- Pilih Supplier --</option>
      <?php while($s = mysqli_fetch_assoc($supplier)) { ?>
        <option value="<?= $s['id_supplier'] ?>">
          <?= $s['nama_supplier'] ?>
        </option>
      <?php } ?>
    </select>
  </div>

</div>

<!-- BARIS 2 -->
<div class="row mt-3">

  <!-- STOK -->
  <div class="col-md-3">
    <label>Stok Sistem</label>
    <input type="text" id="stok_sistem" class="form-control bg-light text-bold" readonly value="0">
  </div>

  <!-- JUMLAH -->
  <div class="col-md-4">
    <label>Jumlah Koreksi</label>

    <div class="input-group">

      <input type="number" id="jumlah" name="jumlah" class="form-control text-center" placeholder="0">

      <div class="input-group-append d-flex flex-column">
        <button type="button" class="btn btn-success py-1" id="btnPlus">+</button>
        <button type="button" class="btn btn-danger py-1" id="btnMinus">-</button>
      </div>

    </div>

    <small id="previewStok" class="d-block mt-2 font-weight-bold text-primary">
      Stok setelah: -
    </small>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
let mode = "plus";
let stokAwal = 0;

// ==========================
// LOAD STOK (BARANG + SUPPLIER)
// ==========================
function loadStok() {

    let id_barang = $("#barang").val();
    let id_supplier = $("#supplier").val();

    if (!id_barang) {
        $("#stok_sistem").val("0");
        return;
    }

    $.get("mutasi_get_stok.php", {
        id_barang: id_barang,
        id_supplier: id_supplier
    }, function(res){

        let data = JSON.parse(res);

        $("#stok_sistem").val(data.stok);


        updatePreview();
    });
}

// ==========================
// PILIH BARANG
// ==========================
$(document).on("change", "#barang", function () {

    let selected = $(this).find(":selected");
    let pakaiSupplier = selected.data("supplier");
    let id_barang = $(this).val();

    if (String(pakaiSupplier) === "1") {

        // tampilkan supplier
        $("#supplierBox").show();

    } else {

        // 🔥 reset supplier
        $("#supplier").val("").trigger("change");

        // sembunyikan
        $("#supplierBox").hide();
    }

    // 🔥 ambil stok ulang
    loadStok();

});
// ==========================
// PILIH SUPPLIER
// ==========================
$(document).on("change", "#supplier", function () {
    loadStok();
});

// ==========================
// MODE + / -
// ==========================
$("#btnPlus").on("click", function () {
    mode = "plus";
    updatePreview();
});

$("#btnMinus").on("click", function () {
    mode = "minus";
    updatePreview();
});

// ==========================
// INPUT JUMLAH
// ==========================
$(document).on("input", "#jumlah", function () {
    updatePreview();
});

// Load Stok Awal
function loadStok() {

    let id_barang = $("#barang").val();
    let id_supplier = $("#supplier").val();

    if (!id_barang) {
        stokAwal = 0;
        $("#stok_sistem").val("0");
        updatePreview();
        return;
    }

    $.get("mutasi_get_stok.php", {
        id_barang: id_barang,
        id_supplier: id_supplier
    }, function(res){

        let data = JSON.parse(res);

        // 🔥 INI KUNCI
        stokAwal = parseFloat(data.stok) || 0;

        $("#stok_sistem").val(stokAwal);

        // 🔥 refresh preview
        updatePreview();
    });

}

$(document).on("change", "#supplier", function () {
    loadStok();
});
// ==========================
// PREVIEW STOK
// ==========================
function updatePreview() {

    let jumlah = parseFloat($("#jumlah").val()) || 0;

    let hasil = (mode === "minus") 
        ? stokAwal - jumlah 
        : stokAwal + jumlah;

    let warna = hasil < 0 ? "text-danger" : "text-success";

    $("#previewStok")
        .removeClass("text-success text-danger text-primary")
        .addClass(warna)
        .text("Stok setelah: " + hasil.toLocaleString());
}

$(document).ready(function() {
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- Cari --',
        allowClear: true
    });
});
</script>
</body>
</html>
