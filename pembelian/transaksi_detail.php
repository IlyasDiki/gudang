<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../config/init.php';

$id = $_GET['id'] ?? null;
if (!$id) die('ID tidak ditemukan');


// LIST BARANG
$barang = mysqli_query($conn, "
SELECT 
    b.id_barang,
    b.nama_barang,
    b.id_kelompok,
    b.pakai_supplier,
    k.nama_kelompok,
    k.parent_id
FROM barang b
JOIN kelompok_barang k 
ON k.id_kelompok = b.id_kelompok
ORDER BY k.nama_kelompok, b.nama_barang
");

$supplier = mysqli_query($conn,"
SELECT 
  id_supplier,
  nama_supplier
FROM supplier
ORDER BY nama_supplier
");


// DETAIL TRANSAKSI
$detail = mysqli_query($conn, "
SELECT 
  d.id_detail,
  d.jumlah,
  b.nama_barang,
  s.nama_supplier
FROM transaksi_detail d
JOIN barang b 
  ON b.id_barang = d.id_barang
LEFT JOIN supplier s
  ON s.id_supplier = d.id_supplier
WHERE d.id_transaksi = '$id'
ORDER BY d.id_detail DESC
");


// HEADER TRANSAKSI
$trx = mysqli_query($conn, "
SELECT 
  t.tanggal_terima,
  t.id_kelompok,
  kb.nama_kelompok,
  jt.nama_jenis AS jenis_transaksi
FROM transaksi t
JOIN jenis_transaksi jt 
  ON jt.id_jenist = t.jenis_transaksi
JOIN kelompok_barang kb
  ON kb.id_kelompok = t.id_kelompok
WHERE t.id_transaksi = '$id'
");

$trx = mysqli_fetch_assoc($trx);

$idKelompokUtama = $trx['id_kelompok'];

$subKelompok = mysqli_query($conn, "
SELECT *
FROM kelompok_barang
WHERE parent_id = '$idKelompokUtama'
ORDER BY nama_kelompok
");
$adaSub = mysqli_num_rows($subKelompok);
?>
<div class="alert alert-info mb-3">
  <strong>Jenis Transaksi:</strong> <?= $trx['jenis_transaksi'] ?><br>
  <strong>Kelompok Barang:</strong> <?= $trx['nama_kelompok'] ?><br>
  <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($trx['tanggal_terima'])) ?>
</div>

<form method="post" action="transaksi_detail_simpan.php">
  <input type="hidden" name="id_transaksi" value="<?= $id ?>">

  <div class="row mb-3">
  <?php if($adaSub > 0): ?>

  <div class="col-md-4">
  <label class="form-label">Jenis Barang</label>

  <select id="sub_kelompok" class="form-control">
  <option value="">-- Pilih Jenis --</option>

  <?php while($s = mysqli_fetch_assoc($subKelompok)): ?>

  <option value="<?= $s['id_kelompok'] ?>">
  <?= $s['nama_kelompok'] ?>
  </option>

  <?php endwhile ?>

  </select>
  </div>

  <?php endif; ?>

  <div class="row mb-3">
    <div class="col-md-4">
      <label for="id_barang" class="form-label">Barang</label>
      <select name="id_barang" id="barang" class="form-control" required>
      <option value="">-- Pilih Barang --</option>
      <small id="infoBarang" class="text-danger" style="display:none;">
      Tidak ada barang pada kelompok ini
      </small>
      <?php while($b = mysqli_fetch_assoc($barang)): ?>

<option 
value="<?= $b['id_barang'] ?>"
data-kelompok="<?= $b['id_kelompok'] ?>"
data-supplier="<?= $b['pakai_supplier'] ?>"
>
<?= $b['nama_barang'] ?>
</option>

      <?php endwhile ?>

      </select>
    </div>

    <div class="col-md-3" id="supplierBox" style="display: none;">
      <label for="supplier" class="form-label">Supplier</label>
      <select id="supplier" name="id_supplier" class="form-control">
        <option value="">-- Pilih Supplier --</option>
        <?php while($s = mysqli_fetch_assoc($supplier)): ?>
          <option value="<?= $s['id_supplier'] ?>">
            <?= $s['nama_supplier'] ?>
          </option>
        <?php endwhile ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="jumlah" class="form-label">Jumlah</label>
      <input type="number" name="jumlah" id="jumlah" class="form-control" required>
    </div>

    <div class="col-md-2 d-flex align-items-end">
      <button class="btn btn-success w-100">Tambah</button>
    </div>
  </div>
</form>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>Barang</th>
      <th>Supplier</th>
      <th>Jumlah</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while($d = mysqli_fetch_assoc($detail)): ?>
    <tr>
      <td><?= $d['nama_barang'] ?></td>
      <td><?= $d['nama_supplier'] ?? '-' ?></td>
      <td><?= $d['jumlah'] ?></td>
      <td>
        <a href="transaksi_detail_hapus.php?id=<?= $d['id_detail'] ?>" class="btn btn-danger btn-sm">
          Hapus
        </a>  
      </td>
    </tr>
    <?php endwhile ?>
  </tbody>
</table>

<script>
document.getElementById("sub_kelompok").addEventListener("change", function(){

let kelompok = this.value;
let barangSelect = document.getElementById("barang");
let info = document.getElementById("infoBarang");

let options = barangSelect.querySelectorAll("option");

let jumlah = 0;

options.forEach(function(opt){

let idKelompok = opt.getAttribute("data-kelompok");

if(!idKelompok){
opt.style.display = "block";
return;
}

if(idKelompok == kelompok){

opt.style.display = "block";
jumlah++;

}else{

opt.style.display = "none";

}

});

barangSelect.value="";

if(jumlah == 0){

info.style.display = "block";

}else{

info.style.display = "none";

}

});
</script>

<script>

document.getElementById('barang').addEventListener('change', function(){

let supplierBox = document.getElementById('supplierBox');

let selected = this.options[this.selectedIndex];

let pakaiSupplier = selected.getAttribute("data-supplier");

if(pakaiSupplier == "1"){

supplierBox.style.display = "block";

}else{

supplierBox.style.display = "none";

}

});

</script>