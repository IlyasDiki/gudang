<?php
require '../config/init.php';

$q = mysqli_query($conn, "
SELECT 
  bk.*,
  b.nama_barang AS nama_briket
FROM bkbriket bk
JOIN barang b ON b.id_barang = bk.id_barang_briket
ORDER BY bk.tanggal DESC
");

$qBriketTambah = mysqli_query($conn, "
SELECT id_barang, nama_barang
FROM barang
WHERE id_kelompok IN (15,16)
ORDER BY nama_barang ASC
");

$qBriketEdit = mysqli_query($conn, "
SELECT id_barang, nama_barang
FROM barang
WHERE id_kelompok IN (15,16)
ORDER BY nama_barang ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Buku Kerja Briket</title>

<link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../dist/css/adminlte.min.css">

</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

<?php
$page='bkbriket';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

<?php if(isset($_GET['edit_success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
<i class="fa fa-check"></i> Data berhasil diperbarui
<button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<section class="content-header">
<h1>Buku Kerja Briket</h1>
</section>

<section class="content">
<div class="card">
<div class="card-header">
<h3 class="card-title">Daftar Produksi</h3>

<button class="btn btn-primary float-right mb-2"
data-toggle="modal"
data-target="#modalTambahBk">
+ Tambah Produksi
</button>

<a class="btn btn-success float-right mr-2 mb-2"
href="../laporan/bkbriket_laporan.php?tglAwal=<?=date('Y-m-01')?>&tglAkhir=<?=date('Y-m-t')?>">

Lihat Rincian Mutasi
</a>

</div>

<div class="card-body">

<table class="table table-bordered table-sm">

<thead class="bg-light">

<tr>
<th width="30">No</th>
<th>Tanggal</th>
<th>Jenis Briket</th>
<th>Lokasi</th>
<th>Keterangan</th>
<th>Jenis Hasil</th>
<th width="180">Aksi</th>
</tr>

</thead>

<tbody>

<?php $no=1; while($r=mysqli_fetch_assoc($q)): ?>

<tr>

<td><?=$no++?></td>
<td><?=date('d-m-Y',strtotime($r['tanggal']))?></td>
<td><?=htmlspecialchars($r['nama_briket'])?></td>
<td><?=htmlspecialchars($r['lokasi'])?></td>
<td><?=htmlspecialchars($r['keterangan'])?></td>
<td>

<?php if($r['id_kelompok']==15): ?>

<span class="badge badge-success">
Hasil Bongkar Oven
</span>

<?php elseif($r['id_kelompok']==16): ?>

<span class="badge badge-warning">
Hasil Bongkar Karantina
</span>

<?php else: ?>

<span class="badge badge-secondary">-</span>

<?php endif; ?>

</td>

<td>

<a href="bkbriket_detail.php?id_bk=<?=$r['id_bk']?>"
class="btn btn-primary btn-sm">

<i class="fa fa-edit"></i> Input
</a>

<button
class="btn btn-warning btn-sm"
data-toggle="modal"
data-target="#modalEditBk"

onclick="editBk(
'<?=$r['id_bk']?>',
'<?=$r['tanggal']?>',
'<?=$r['id_barang_briket']?>',
'<?=$r['lokasi']?>',
'<?=addslashes($r['keterangan'])?>',
'<?=$r['id_kelompok']?>'
)">>

<i class="fa fa-edit"></i> Edit

</button>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</section>
</div>
<?php include "../footer.php"; ?>
</div>



<!-- ===============================
MODAL TAMBAH
================================ -->

<div class="modal fade" id="modalTambahBk">
<div class="modal-dialog">
<form method="post" action="bkbriket_simpan.php">
<div class="modal-content">
<div class="modal-header">
<h4 class="modal-title">Tambah Produksi Briket</h4>
<button type="button" class="close" data-dismiss="modal">
&times;
</button>
</div>

<div class="modal-body">

<div class="form-group">
<label>Tanggal</label>
<input
type="date"
name="tanggal"
class="form-control"
required
value="<?=date('Y-m-d')?>">
</div>

<div class="form-group">
<label>Status</label>
<select name="id_kelompok" class="form-control" required>
<option value="">-- Pilih Jenis Hasil Bongkar --</option>
<option value="15">Hasil Bongkar Oven</option>
<option value="16">Hasil Bongkar Karantina</option>
</select>
</div>

<div class="form-group">
<label>Jenis Briket</label>
<select
name="id_barang_briket"
class="form-control"
required>
<option value="">-- Pilih Jenis Briket --</option>
<?php while($b=mysqli_fetch_assoc($qBriketTambah)): ?>
<option value="<?=$b['id_barang']?>">
<?=$b['nama_barang']?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="form-group">

<label>Lokasi</label>
<input
type="text"
name="lokasi"
class="form-control"
required
placeholder="Contoh : DCS 1">

</div>

<div class="form-group">

<label>Keterangan</label>
<input
type="text"
name="keterangan"
class="form-control"
placeholder="opsional">
</div>
</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-dismiss="modal">

Batal

</button>

<button class="btn btn-success">

<i class="fa fa-save"></i> Simpan

</button>

</div>
</div>
</form>
</div>
</div>



<!-- ===============================
MODAL EDIT
================================ -->

<div class="modal fade" id="modalEditBk">
<div class="modal-dialog">
<form method="post" action="bkbriket_edit.php">
<div class="modal-content">
<div class="modal-header">
<h4 class="modal-title">
Edit Produksi Briket
</h4>

<button type="button" class="close" data-dismiss="modal">
&times;
</button>

</div>

<div class="modal-body">
<input type="hidden" name="id_bk" id="edit_id_bk"> 

<div class="form-group">
<label>Tanggal</label>
<input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
</div>

<div class="form-group">
<label>Status</label>
<select name="id_kelompok" id="edit_id_kelompok" class="form-control" required>
<option value="">-- Pilih Jenis Hasil Bongkar --</option>
<option value="15">Hasil Bongkar Oven</option>
<option value="16">Hasil Bongkar Karantina</option>
</select>
</div>

<div class="form-group">
<label>Jenis Briket</label>
<select name="id_barang_briket" id="edit_id_barang_briket" class="form-control" required>
<option value="">-- Pilih Jenis Briket --</option>
<?php while($b=mysqli_fetch_assoc($qBriketEdit)): ?>
<option value="<?=$b['id_barang']?>">
<?=$b['nama_barang']?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="form-group">
<label>Lokasi</label>
<input type="text" name="lokasi" id="edit_lokasi" class="form-control" required>
</div>

<div class="form-group">
<label>Keterangan</label>
<input type="text" name="keterangan" id="edit_keterangan" class="form-control"> 
</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-dismiss="modal">

Batal

</button>

<button class="btn btn-success">

<i class="fa fa-save"></i> Simpan
</button>
</div>
</div>
</form>
</div>
</div>

<script>

function editBk(id,tanggal,id_barang,lokasi,keterangan,id_kelompok){
document.getElementById('edit_id_bk').value=id;
document.getElementById('edit_tanggal').value=tanggal;
document.getElementById('edit_id_barang_briket').value=id_barang;
document.getElementById('edit_lokasi').value=lokasi;
document.getElementById('edit_keterangan').value=keterangan;
document.getElementById('edit_id_kelompok').value=id_kelompok;
}
</script>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>

</body>
</html>