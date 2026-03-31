<?php
require '../config/init.php';

$qBarang = mysqli_query($conn, "
SELECT 
  b.id_barang, 
  b.nama_barang,
  b.pakai_supplier,
  k.nama_kelompok 
FROM barang b
JOIN kelompok_barang k 
  ON k.id_kelompok = b.id_kelompok
ORDER BY k.nama_kelompok, b.nama_barang
");

$qSupplier = mysqli_query($conn,"
SELECT id_supplier, nama_supplier
FROM supplier
ORDER BY id_supplier
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Input Stok Awal</title>

<link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../dist/css/adminlte.min.css">

<link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
$page = 'stokawal';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

<section class="content-header">
<h1>Stok Awal</h1>
</section>

<section class="content">

<div class="card">
<div class="card-header">
<h3 class="card-title">Stok Awal Barang</h3>
</div>

<form method="post" action="stok_awal_simpan.php" id="formStokAwal">

<div class="card-body">

<div class="row mb-3">
<div class="col-md-4">
<label>Tanggal Stok Awal</label><br>
<div class="row align-items-end">
  <div class="col-md-4">
  <label>Bulan</label>
  <select name="bulan" class="form-control" required>
    <option value="">-- Pilih Bulan --</option>
    <option value="01">Januari</option>
    <option value="02">Februari</option>
    <option value="03">Maret</option>
    <option value="04">April</option>
    <option value="05">Mei</option>
    <option value="06">Juni</option>
    <option value="07">Juli</option>
    <option value="08">Agustus</option>
    <option value="09">September</option>
    <option value="10">Oktober</option>
    <option value="11">November</option>
    <option value="12">Desember</option>
  </select>
</div>
<div class="col-md-4">
  <label>Tahun</label>
  <select name="tahun" class="form-control" required>
    <?php
    $now = date('Y');
    for ($i = $now-1; $i <= $now+1; $i++) {
      echo "<option value='$i'>$i</option>";
    }
    ?>
  </select>
</div>
</div>

</div>
</div>

<hr>

<div class="row align-items-end">

<div class="col-md-4">
<label>Barang</label>
<select id="barangSelect" class="form-control select2bs4">
<option value="">-- Cari Barang --</option>

<?php while($b = mysqli_fetch_assoc($qBarang)) { ?>

<option 
value="<?= $b['id_barang'] ?>"
data-supplier="<?= $b['pakai_supplier'] ?>"
>

<?= $b['nama_kelompok'] ?> - <?= htmlspecialchars($b['nama_barang']) ?>

</option>

<?php } ?>

</select>
</div>

<div class="col-md-3" id="supplierBox" style="display:none;">
<label>Supplier</label>
<select id="supplierSelect" class="form-control select2bs4">
<option value="">-- Pilih Supplier --</option>

<?php while($s = mysqli_fetch_assoc($qSupplier)) { ?>

<option value="<?= $s['id_supplier'] ?>">
<?= htmlspecialchars($s['nama_supplier']) ?>
</option>

<?php } ?>

</select>
</div>

<div class="col-md-3">
<label>Jumlah Awal</label>
<input type="number" step="0.01" id="jumlahInput" class="form-control">
</div>

<div class="col-md-2">
<button type="button" class="btn btn-primary btn-block" id="btnTambah">
<i class="fa fa-plus"></i> Tambah
</button>
</div>

</div>

<hr>

<div class="table-responsive">

<table class="table table-bordered table-sm" id="tabelStokAwal">

<thead class="bg-light">
<tr>
<th style="width:50px">No</th>
<th>Barang</th>
<th class="text-right" style="width:150px">Jumlah</th>
<th style="width:80px">Aksi</th>
</tr>
</thead>

<tbody>

<tr id="rowKosong">
<td colspan="4" class="text-center text-muted">
Belum ada barang yang ditambahkan.
</td>
</tr>

</tbody>
</table>

</div>

</div>

<div class="card-footer text-right">
  
<button class="btn btn-success">
<i class="fa fa-save"></i> Simpan Stok Awal
</button>
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

<script src="../plugins/select2/js/select2.full.min.js"></script>

<script>

$(function () {

$('.select2bs4').select2({
theme: 'bootstrap4'
});

let nomor = 0;

/* ============================= */
/* SHOW SUPPLIER IF NEEDED */
/* ============================= */

$("#barangSelect").on("change", function(){

let selected = $(this).find(":selected");

if(selected.length == 0) return;

let pakaiSupplier = selected.data("supplier");

if(pakaiSupplier == 1){

$("#supplierBox").show();

}else{

$("#supplierBox").hide();
$("#supplierSelect").val(null).trigger("change");

}

});


function updateNomor() {

let no = 1;

$("#tabelStokAwal tbody tr.dataRow").each(function(){

$(this).find(".no").text(no++);

});

}


/* ============================= */
/* TAMBAH BARANG */
/* ============================= */

$("#btnTambah").click(function () {

let idBarang = $("#barangSelect").val();
let namaBarang = $("#barangSelect option:selected").text();
let jumlah = $("#jumlahInput").val();

let idSupplier = $("#supplierSelect").val();
let namaSupplier = $("#supplierSelect option:selected").text();

let pakaiSupplier = $("#barangSelect option:selected").data("supplier");


if (!idBarang) {
alert("Pilih barang dulu.");
return;
}

if (!jumlah || parseFloat(jumlah) <= 0) {
alert("Jumlah harus lebih dari 0.");
return;
}

if(pakaiSupplier == 1 && !idSupplier){
alert("Supplier harus dipilih.");
return;
}


/* CEK DUPLIKAT */

if ($("#row_" + idBarang + "_" + idSupplier).length > 0) {
alert("Barang ini sudah ditambahkan.");
return;
}


$("#rowKosong").remove();

nomor++;


/* BUAT ROW */

let row = `
<tr class="dataRow" id="row_${idBarang}_${idSupplier}">

<td class="no">${nomor}</td>

<td>
${namaBarang}
${idSupplier ? "<br><small class='text-muted'>Supplier : "+namaSupplier+"</small>" : ""}
<input type="hidden" name="id_barang[]" value="${idBarang}">
<input type="hidden" name="id_supplier[]" value="${idSupplier}">
</td>

<td class="text-right">
${parseFloat(jumlah).toFixed(2)}
<input type="hidden" name="jumlah[]" value="${jumlah}">
</td>

<td class="text-center">
<button type="button" class="btn btn-danger btn-sm btnHapus">
<i class="fa fa-trash"></i>
</button>
</td>

</tr>
`;


$("#tabelStokAwal tbody").append(row);


/* RESET INPUT */

$("#barangSelect").val(null).trigger("change");
$("#supplierSelect").val(null).trigger("change");
$("#jumlahInput").val("");


updateNomor();

});


/* ============================= */
/* HAPUS ROW */
/* ============================= */

$(document).on("click", ".btnHapus", function(){

$(this).closest("tr").remove();

updateNomor();

if ($("#tabelStokAwal tbody tr.dataRow").length == 0) {

$("#tabelStokAwal tbody").html(`
<tr id="rowKosong">
<td colspan="4" class="text-center text-muted">
Belum ada barang yang ditambahkan.
</td>
</tr>
`);

nomor = 0;

}

});


});

</script>

</body>
</html>