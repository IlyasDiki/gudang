<?php
  require '../config/init.php';
  
  $where = [];

  $tgl_awal  = $_GET['tgl_awal'] ?? date('Y-m-01');
  $tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');
  $status    = $_GET['status'] ?? '';
  $id_barang = $_GET['id_barang'] ?? '';


  if($status != ''){
    $where[] = "bk.id_kelompok='$status'";
  }

  if($id_barang != ''){
    $where[] = "bk.id_barang_briket='$id_barang'";
  }

  $stok = $_GET['stok'] ?? 'ada';

  if($stok == 'ada'){
    $where[] = "(COALESCE(bb.total_bongkar,0) - COALESCE(bm.total_mutasi,0)) > 0";
  }

  if($stok == 'habis'){
    $where[] = "(COALESCE(bb.total_bongkar,0) - COALESCE(bm.total_mutasi,0)) <= 0";
  }

  $where_sql = '';
  if(count($where) > 0){
    $where_sql = "WHERE " . implode(" AND ", $where);
  }

  $q = mysqli_query($conn,"
  SELECT 
    bk.*,
    b.nama_barang AS nama_briket,

    COALESCE(bb.total_bongkar,0) AS total_bongkar,
    COALESCE(bm.total_mutasi,0) AS total_mutasi,

    COALESCE(bb.total_bongkar,0) - COALESCE(bm.total_mutasi,0) AS sisa

  FROM bkbriket bk

  LEFT JOIN barang b ON b.id_barang = bk.id_barang_briket

  LEFT JOIN (
    SELECT id_bk,
      SUM((krg * 25) + add_kg) AS total_bongkar
    FROM bkbriket_bongkar
    GROUP BY id_bk
  ) bb ON bb.id_bk = bk.id_bk

  LEFT JOIN (
    SELECT id_bk,
      SUM((krg * 25) + add_kg) AS total_mutasi
    FROM bkbriket_mutasi
    GROUP BY id_bk
  ) bm ON bm.id_bk = bk.id_bk

  $where_sql

  ORDER BY bk.tanggal DESC
  ");

  $qFilter = mysqli_query($conn,"
  SELECT id_barang, nama_barang, id_kelompok 
  FROM barang 
  WHERE id_kelompok IN(15,16)
  ");

  $qBriketTambah = mysqli_query($conn, "
  SELECT id_barang, nama_barang, id_kelompok
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

<section class="content-header">
  <h1>Buku Kerja Briket</h1>
</section>

<section class="content">
<div class="card">

<div class="card-header">
  <h3 class="card-title">Daftar Produksi</h3>

  <button class="btn btn-primary float-right ml-2"
    data-toggle="modal"
    data-target="#modalTambahBk">
    + Tambah Produksi
  </button>

  <a class="btn btn-success float-right"
    href="../laporan/bkbriket_laporan.php?tglAwal=<?=date('Y-m-01')?>&tglAkhir=<?=date('Y-m-t')?>">
    Lihat Rincian Mutasi
  </a>
  
</div>
<form method="get" class="form-inline mb-2">

<select name="status" id="filterStatus" class="form-control mr-2">
  <option value="">Semua Status</option>
  <option value="15" <?=(@$_GET['status']=='15'?'selected':'')?>>
    Oven
  </option>
  <option value="16" <?=(@$_GET['status']=='16'?'selected':'')?>>
    Karantina
  </option>
</select>

<select name="stok" class="form-control">
<option value="ada">Masih Ada</option>
<option value="habis">Sudah Habis</option>
<option value="semua" <?=($stok=='semua'?'selected':'')?>>
  Tampilkan Semua
</option>
</select>

<select name="id_barang" id="filterBriket" class="form-control mr-2">
  <option value="">Semua Briket</option>
  <?php

  while($b=mysqli_fetch_assoc($qFilter)):
  ?>
  <option value="<?=$b['id_barang']?>" 
    data-kelompok="<?=$b['id_kelompok']?>"
    <?=(@$_GET['id_barang']==$b['id_barang']?'selected':'')?>>
    <?=$b['nama_barang']?>
  </option>
  <?php endwhile; ?>
</select>

<button class="btn btn-primary mr-1">Filter</button>
<a href="bkbriket.php" class="btn btn-secondary">Reset</a>

</form>
<div class="card-body">
<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
  <th>No</th>
  <th>Tanggal</th>
  <th>Jenis Briket</th>
  <th>Sisa (Kg)</th>
  <th>Lokasi</th>
  <th>Keterangan</th>
  <th>Status</th>
  <th width="260">Aksi</th>
</tr>
</thead>

<tbody>

<?php $no=1; while($r=mysqli_fetch_assoc($q)): ?>

  <?php
$id = $r['id_bk'];

/* =========================
   TOTAL BONGKAR
========================= */
$qTotalBongkar = mysqli_query($conn,"
  SELECT 
    COALESCE(SUM((krg * 25) + add_kg),0) AS total_bongkar
  FROM bkbriket_bongkar
  WHERE id_bk = '$id'
");
$dBongkar = mysqli_fetch_assoc($qTotalBongkar);
$total_bongkar = (float)$dBongkar['total_bongkar'];

/* =========================
   TOTAL MUTASI
========================= */
$qTotalMutasi = mysqli_query($conn,"
  SELECT 
    COALESCE(SUM((krg * 25) + add_kg),0) AS total_mutasi
  FROM bkbriket_mutasi
  WHERE id_bk = '$id'
");
$dMutasi = mysqli_fetch_assoc($qTotalMutasi);
$total_mutasi = (float)$dMutasi['total_mutasi'];

/* =========================
   SISA
========================= */
$sisa = $total_bongkar - $total_mutasi;
?>

<tr>
<td><?=$no++?></td>
<td><?=date('d-m-Y',strtotime($r['tanggal']))?></td>
<td><?=$r['nama_briket']?></td>
<td>
  <?php if(round($sisa,2) <= 0): ?>
    <span class="badge badge-secondary">Habis</span>
  <?php else: ?>
    <b><?= number_format($sisa,2) ?></b>
  <?php endif; ?>
</td>
<td><?=$r['lokasi']?></td>
<td><?=$r['keterangan']?></td>
<td>
  <?php if($r['id_kelompok']==15): ?>
    <span class="badge badge-success">Hasil Bongkar Oven</span>
  <?php else: ?>
    <span class="badge badge-warning">Hasil Bongkar Karantina</span>
  <?php endif; ?>
</td>

<td>
<div class="btn-group btn-group-sm">

<button class="btn btn-success mr-1"
  title="Lihat Data Bongkar"
  onclick="toggleBongkar('<?=$r['id_bk']?>', this)">
  Lihat
  <i class="fa fa-eye-slash"></i>
</button>

<a href="bkbriket_detail.php?id_bk=<?=$r['id_bk']?>"
  class="btn btn-primary mr-1">
  Input Mutasi
</a>

<button class="btn btn-warning"
  data-toggle="modal"
  data-target="#modalEditBk"
  onclick="editBk(
    '<?=$r['id_bk']?>',
    '<?=$r['tanggal']?>',
    '<?=$r['id_barang_briket']?>',
    '<?=$r['lokasi']?>',
    '<?=addslashes($r['keterangan'])?>',
    '<?=$r['id_kelompok']?>')">
  Edit
</button>

</div>
</td>
</tr>

<!-- DETAIL BONGKAR -->
<tr id="bongkar_<?=$r['id_bk']?>" style="display:none;">
<td colspan="8">

<div class="p-2">

<div class="d-flex justify-content-between">
  <b>Data Bongkar</b>

  <button class="btn btn-sm btn-primary"
    onclick="tambahRowBongkar(<?=$r['id_bk']?>)">
    + Tambah
  </button>
</div>

<table class="table table-sm table-bordered mt-2">
<thead>
<tr>
  <th>Tanggal</th>
  <th>KRG</th>
  <th>ADD</th>
  <th>Total</th>
  <th>Keterangan</th>
  <th>Hapus</th>
</tr>
</thead>

<tbody id="tbody_<?=$r['id_bk']?>">

<?php
$qb = mysqli_query($conn,"
SELECT * FROM bkbriket_bongkar
WHERE id_bk='{$r['id_bk']}'
ORDER BY tanggal_bongkar ASC
");

while($b=mysqli_fetch_assoc($qb)):
?>

<tr>
<td>
  <input type="date" class="form-control tgl"
    value="<?=$b['tanggal_bongkar']?>">
  <input type="hidden" class="id_bongkar"
    value="<?=$b['id_bongkar']?>">
</td>

<td><input type="number" class="form-control krg" value="<?=$b['krg']?>"></td>
<td><input type="number" class="form-control add" value="<?=$b['add_kg']?>"></td>
<td><input type="text" class="form-control jml" readonly></td>
<td><input type="text" class="form-control ket" value="<?=$b['ket']?>"></td>

<td>
<button class="btn btn-danger btn-sm btnHapusRow">X</button>
</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>

<div class="text-right">
<button class="btn btn-success btn-sm"
  onclick="simpanBongkar(<?=$r['id_bk']?>)">
  Simpan Bongkar
</button>
</div>

</div>

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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambahBk">
<div class="modal-dialog">
<form method="post" action="bkbriket_simpan.php">
<div class="modal-content">

<div class="modal-header">
<h4 class="modal-title">Tambah Produksi Briket</h4>
<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

            <div class="modal-body">
              <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required value="<?=date('Y-m-d')?>">
              </div>
              <div class="form-group">
                <label>Status</label>
                <select name="id_kelompok" id="statusTambah" class="form-control" required>
                  <option value="">-- Pilih Jenis Hasil Bongkar --</option>
                  <option value="15">Hasil Bongkar Oven</option>
                  <option value="16">Hasil Bongkar Karantina</option>
                </select>
              </div>
              <div class="form-group">
                <label>Jenis Briket</label>
                <select name="id_barang_briket" id="briketTambah" class="form-control" required>
                  <option value="">-- Pilih Jenis Briket --</option>
                  <?php while($b=mysqli_fetch_assoc($qBriketTambah)): ?>
                  <option value="<?=$b['id_barang']?>" data-kelompok="<?=$b['id_kelompok']?>"><?=$b['nama_barang']?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="lokasi" class="form-control" required placeholder="Contoh : DCS 1">
              </div>
              <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-control" placeholder="opsional">
              </div>
            </div>

<div class="modal-footer">
<button class="btn btn-success">Simpan</button>
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
              <h4 class="modal-title">Edit Produksi Briket</h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                  <option value="<?=$b['id_barang']?>"><?=$b['nama_barang']?></option>
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
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
<!-- SCRIPT -->
<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
<script>
function filterBriketByStatus(){
  let status = document.getElementById("filterStatus").value;
  let options = document.querySelectorAll("#filterBriket option");

  options.forEach(opt => {
    let kelompok = opt.getAttribute("data-kelompok");

    if(!kelompok){
      opt.style.display = "block";
    }else if(status === ""){
      opt.style.display = "block";
    }else if(kelompok === status){
      opt.style.display = "block";
    }else{
      opt.style.display = "none";
    }
  });

  let selected = document.getElementById("filterBriket").value;
  let selectedOpt = document.querySelector(`#filterBriket option[value="${selected}"]`);

  if(selectedOpt && selectedOpt.style.display === "none"){
    document.getElementById("filterBriket").value = "";
  }
}

document.getElementById("filterStatus").addEventListener("change", filterBriketByStatus);
window.addEventListener("load", filterBriketByStatus);
</script>
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
<script>
      $('#statusTambah').on('change', function() {
      
          let selected = $(this).val();
      
          $('#briketTambah option').each(function() {
      
              let kelompok = $(this).data('kelompok');
      
              if (!kelompok) {
                  $(this).show(); // option pertama
              } else if (kelompok == selected) {
                  $(this).show();
              } else {
                  $(this).hide();
              }
      
          });
      
          $('#briketTambah').val('');
      });
</script>
<script>
function toggleBongkar(id, btn){
  let row = document.getElementById("bongkar_" + id);
  let icon = btn.querySelector("i");

  if(row.style.display === "none"){
    row.style.display = "table-row";

    // mata terbuka
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");

  }else{
    row.style.display = "none";

    // mata tertutup
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  }
}

function tambahRowBongkar(id){
  let tbody=document.getElementById("tbody_"+id);

  let tr=document.createElement("tr");

  tr.innerHTML=`
  <td>
    <input type="date" class="form-control tgl">
    <input type="hidden" class="id_bongkar" value="">
  </td>
  <td><input type="number" class="form-control krg" value="0"></td>
  <td><input type="number" class="form-control add" value="0"></td>
  <td><input type="text" class="form-control jml" readonly></td>
  <td><input type="text" class="form-control ket"></td>
  <td><button class="btn btn-danger btn-sm btnHapusRow">X</button></td>
  `;

  tbody.appendChild(tr);
}

document.addEventListener("input", function(e){
if(e.target.classList.contains("krg") || e.target.classList.contains("add")){
  let tr=e.target.closest("tr");
  let krg=parseFloat(tr.querySelector(".krg").value)||0;
  let add=parseFloat(tr.querySelector(".add").value)||0;
  tr.querySelector(".jml").value=(krg*25+add).toFixed(2);
}
});

document.addEventListener("click", function(e){
if(e.target.classList.contains("btnHapusRow")){
  e.target.closest("tr").remove();
}
});

function simpanBongkar(id){
  let rows=document.querySelectorAll("#tbody_"+id+" tr");

  let data=[];

  rows.forEach(tr=>{
    data.push({
      id_bongkar:tr.querySelector(".id_bongkar").value,
      tgl:tr.querySelector(".tgl").value,
      krg:tr.querySelector(".krg").value,
      add:tr.querySelector(".add").value,
      ket:tr.querySelector(".ket").value
    });
  });

  fetch("bkbriket_bongkar_update.php",{
    method:"POST",
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id_bk:id,data:data})
  })
  .then(res => res.text())
  .then(res => {
    if(res !== "OK"){
      alert("Gagal: " + res);
    } else {
      alert("Tersimpan");
      location.reload();
    }
  });
}
</script>

</body>
</html>