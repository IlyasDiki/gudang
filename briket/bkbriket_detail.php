<?php
require '../config/init.php';

if(!isset($_GET['id_bk'])) die("ID produksi tidak ditemukan");
$id_bk = mysqli_real_escape_string($conn, $_GET['id_bk']);

/* =========================
   AMBIL HEADER BKBRIKET
========================= */
$qHeader = mysqli_query($conn, "
  SELECT 
    bk.*,
    b.nama_barang AS nama_briket
  FROM bkbriket bk
  JOIN barang b ON b.id_barang = bk.id_barang_briket
  WHERE bk.id_bk = '$id_bk'
  LIMIT 1
");
$header = mysqli_fetch_assoc($qHeader);
if(!$header) die("Data produksi tidak ditemukan");

/* =========================
   AMBIL DATA BONGKAR OVEN
========================= */
$qBongkar = mysqli_query($conn, "
  SELECT *
  FROM bkbriket_bongkar
  WHERE id_bk = '$id_bk'
  ORDER BY tanggal_bongkar ASC, id_bongkar ASC
");

$dataBongkar = [];
while($row = mysqli_fetch_assoc($qBongkar)){
  $dataBongkar[] = $row;
}

/* =========================
   AMBIL DATA MUTASI
========================= */
$qMutasi = mysqli_query($conn, "
  SELECT *
  FROM bkbriket_mutasi
  WHERE id_bk = '$id_bk'
  ORDER BY tanggal ASC, id_mutasi ASC
");

$dataMutasi = [];
while($row = mysqli_fetch_assoc($qMutasi)){
  $dataMutasi[] = $row;
}

/* =========================
   HITUNG TOTAL BONGKAR
========================= */
$total_bongkar = 0;
foreach($dataBongkar as $b){
  $krg = (float)$b['krg'];
  $add = (float)$b['add_kg'];
  $total_bongkar += ($krg * 25) + $add;
}

/* =========================
   HITUNG SALDO PREVIEW
========================= */
$saldo = $total_bongkar;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Detail Produksi Briket</title>
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">

  <style>
    .table td, .table th { vertical-align: middle; }
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php
$page = 'bkbriket';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

  <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
      <i class="fa fa-check"></i> Data berhasil disimpan.
    </div>
  <?php endif; ?>

  <section class="content-header">
    <h1>Detail Produksi Briket</h1>
  </section>

  <section class="content">
    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Jenis Briket: <b><?= htmlspecialchars($header['nama_briket']) ?></b> |
          Tanggal Produksi: <b><?= date('d-m-Y', strtotime($header['tanggal'])) ?></b> |
          Lokasi: <b><?= htmlspecialchars($header['lokasi']) ?></b> |
          Status: <b><?= htmlspecialchars($header['status'] ?? '') ?></b>
        </h3>
      </div>

      <form method="post" action="bkbriket_detail_simpan.php">
        <input type="hidden" name="id_bk" value="<?= $id_bk ?>">

        <div class="card-body">

          <!-- =========================
               BONGKAR OVEN
          ========================= -->
          <div class="mb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><b>Bongkar Oven</b></h5>
            <button type="button" class="btn btn-info btn-sm" onclick="tambahBongkar()">
              <i class="fa fa-plus"></i> Tambah Bongkar
            </button>
          </div>

          <table class="table table-bordered table-sm" id="tblBongkar">
            <thead class="bg-light">
              <tr>
                <th width="160">Tanggal Bongkar</th>
                <th width="110">KRG</th>
                <th width="130">ADD (Kg)</th>
                <th width="130">JML (Kg)</th>
                <th width="80">Hapus</th>
              </tr>
            </thead>
            <tbody>

              <?php if(count($dataBongkar) > 0): ?>
                <?php foreach($dataBongkar as $b): 
                  $jml = ((float)$b['krg'] * 25) + (float)$b['add_kg'];
                ?>
                  <tr data-id="<?= $b['id_bongkar'] ?>">
                    <td>
                      <input type="date" name="b_tanggal[]" class="form-control"
                        value="<?= htmlspecialchars($b['tanggal_bongkar']) ?>">
                      <input type="hidden" name="b_id[]" value="<?= $b['id_bongkar'] ?>">
                    </td>
                    <td>
                      <input type="number" step="0.01" name="b_krg[]" class="form-control bongkar-krg"
                        value="<?= htmlspecialchars($b['krg']) ?>">
                    </td>
                    <td>
                      <input type="number" step="0.01" name="b_add[]" class="form-control bongkar-add"
                        value="<?= htmlspecialchars($b['add_kg']) ?>">
                    </td>
                    <td>
                      <input type="text" class="form-control bongkar-jml" readonly
                        value="<?= number_format($jml,2) ?>">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-danger btn-sm btnHapusBongkar">
                        Hapus
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>

            </tbody>
          </table>

          <input type="hidden" name="deleted_bongkar" id="deleted_bongkar" value="">

          <hr>

          <!-- =========================
               MUTASI
          ========================= -->
          <div class="mb-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><b>Mutasi</b></h5>
            <button type="button" class="btn btn-info btn-sm" onclick="tambahMutasi()">
              <i class="fa fa-plus"></i> Tambah Mutasi
            </button>
          </div>

          <table class="table table-bordered table-sm" id="tblMutasi">
            <thead class="bg-light">
              <tr>
                <th width="150">Tanggal</th>
                <th width="150">Jenis</th>
                <th width="110">KRG</th>
                <th width="130">ADD (Kg)</th>
                <th width="130">JML (Kg)</th>
                <th>Keterangan</th>
                <th width="80">Hapus</th>
              </tr>
            </thead>
            <tbody>

              <?php if(count($dataMutasi) > 0): ?>
                <?php foreach($dataMutasi as $m): 
                  $jml = ((float)$m['krg'] * 25) + (float)$m['add_kg'];
                ?>
                  <tr data-id="<?= $m['id_mutasi'] ?>">
                    <td>
                      <input type="date" name="tanggal[]" class="form-control"
                        value="<?= htmlspecialchars($m['tanggal']) ?>">
                      <input type="hidden" name="id_mutasi[]" value="<?= $m['id_mutasi'] ?>">
                    </td>
                    <td>
                      <select name="jenis[]" class="form-control">
                        <option value="PACKING" <?= $m['jenis']=='PACKING'?'selected':'' ?>>PACKING</option>
                        <option value="REPRO" <?= $m['jenis']=='REPRO'?'selected':'' ?>>REPRO</option>
                        <option value="JUAL" <?= $m['jenis']=='JUAL'?'selected':'' ?>>JUAL</option>
                      </select>
                    </td>
                    <td>
                      <input type="number" step="0.01" name="krg[]" class="form-control mutasi-krg"
                        value="<?= htmlspecialchars($m['krg']) ?>">
                    </td>
                    <td>
                      <input type="number" step="0.01" name="add_kg[]" class="form-control mutasi-add"
                        value="<?= htmlspecialchars($m['add_kg']) ?>">
                    </td>
                    <td>
                      <input type="text" class="form-control mutasi-jml" readonly
                        value="<?= number_format($jml,2) ?>">
                    </td>
                    <td>
                      <input type="text" name="keterangan[]" class="form-control"
                        value="<?= htmlspecialchars($m['keterangan'] ?? '') ?>">
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-danger btn-sm btnHapusMutasi">
                        Hapus
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>

            </tbody>
          </table>

          <input type="hidden" name="deleted_mutasi" id="deleted_mutasi" value="">

          <hr>

          <!-- =========================
               PREVIEW SALDO
          ========================= -->
          <h5><b>Saldo (Preview)</b></h5>

          <table class="table table-bordered table-sm">
            <thead class="bg-light">
              <tr>
                <th width="160">Tanggal</th>
                <th>Jenis</th>
                <th class="text-right" width="160">Keluar (Kg)</th>
                <th class="text-right" width="160">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= date('d-m-Y', strtotime($header['tanggal'])) ?></td>
                <td><b>TOTAL BONGKAR OVEN</b></td>
                <td class="text-right">-</td>
                <td class="text-right"><b><?= number_format($saldo,2) ?></b></td>
              </tr>

              <?php
              // hitung saldo dari mutasi urut tanggal
              usort($dataMutasi, function($a,$b){
                return strcmp($a['tanggal'], $b['tanggal']);
              });

              foreach($dataMutasi as $m):
                $jml = ((float)$m['krg'] * 25) + (float)$m['add_kg'];
                $saldo -= $jml;
              ?>
                <tr>
                  <td><?= date('d-m-Y', strtotime($m['tanggal'])) ?></td>
                  <td><?= htmlspecialchars($m['jenis']) ?></td>
                  <td class="text-right"><?= number_format($jml,2) ?></td>
                  <td class="text-right"><b><?= number_format($saldo,2) ?></b></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

        </div>

        <div class="card-footer text-right">
          <a href="bkbriket.php" class="btn btn-secondary">Kembali</a>
          <button class="btn btn-success">
            <i class="fa fa-save"></i> Simpan Semua
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

<script>
// ======================
// hitung otomatis JML
// ======================
function hitungRowJml(tr, mode){
  let krg = 0, add = 0;

  if(mode === 'bongkar'){
    krg = parseFloat(tr.querySelector(".bongkar-krg")?.value || 0);
    add = parseFloat(tr.querySelector(".bongkar-add")?.value || 0);
    let jml = (krg * 25) + add;
    tr.querySelector(".bongkar-jml").value = jml.toFixed(2);
  }

  if(mode === 'mutasi'){
    krg = parseFloat(tr.querySelector(".mutasi-krg")?.value || 0);
    add = parseFloat(tr.querySelector(".mutasi-add")?.value || 0);
    let jml = (krg * 25) + add;
    tr.querySelector(".mutasi-jml").value = jml.toFixed(2);
  }
}

// ======================
// tambah bongkar
// ======================
function tambahBongkar(){
  let tbody = document.querySelector("#tblBongkar tbody");
  let tr = document.createElement("tr");
  tr.setAttribute("data-id","");

  tr.innerHTML = `
    <td>
      <input type="date" name="b_tanggal[]" class="form-control" value="<?= date('Y-m-d') ?>">
      <input type="hidden" name="b_id[]" value="">
    </td>
    <td><input type="number" step="0.01" name="b_krg[]" class="form-control bongkar-krg" value="0"></td>
    <td><input type="number" step="0.01" name="b_add[]" class="form-control bongkar-add" value="0"></td>
    <td><input type="text" class="form-control bongkar-jml" readonly value="0.00"></td>
    <td><input type="text" name="b_ket[]" class="form-control"></td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm btnHapusBongkar">Hapus</button>
    </td>
  `;
  tbody.appendChild(tr);
}

// ======================
// tambah mutasi
// ======================
function tambahMutasi(){
  let tbody = document.querySelector("#tblMutasi tbody");
  let tr = document.createElement("tr");
  tr.setAttribute("data-id","");

  tr.innerHTML = `
    <td>
      <input type="date" name="tanggal[]" class="form-control" value="<?= date('Y-m-d') ?>">
      <input type="hidden" name="id_mutasi[]" value="">
    </td>
    <td>
      <select name="jenis[]" class="form-control">
        <option value="PACKING">PACKING</option>
        <option value="REPRO">REPRO</option>
        <option value="JUAL">JUAL</option>
      </select>
    </td>
    <td><input type="number" step="0.01" name="krg[]" class="form-control mutasi-krg" value="0"></td>
    <td><input type="number" step="0.01" name="add_kg[]" class="form-control mutasi-add" value="0"></td>
    <td><input type="text" class="form-control mutasi-jml" readonly value="0.00"></td>
    <td><input type="text" name="keterangan[]" class="form-control"></td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm btnHapusMutasi">Hapus</button>
    </td>
  `;
  tbody.appendChild(tr);
}

// ======================
// event: hitung otomatis
// ======================
document.addEventListener("input", function(e){
  if(e.target.classList.contains("bongkar-krg") || e.target.classList.contains("bongkar-add")){
    let tr = e.target.closest("tr");
    hitungRowJml(tr, "bongkar");
  }

  if(e.target.classList.contains("mutasi-krg") || e.target.classList.contains("mutasi-add")){
    let tr = e.target.closest("tr");
    hitungRowJml(tr, "mutasi");
  }
});

// ======================
// event: hapus row
// ======================
document.addEventListener("click", function(e){

  // hapus bongkar
  if(e.target.classList.contains("btnHapusBongkar")){
    let tr = e.target.closest("tr");
    let id = tr.getAttribute("data-id");

    if(confirm("Yakin hapus bongkar oven ini?")){
      if(id){
        let input = document.getElementById("deleted_bongkar");
        let arr = input.value ? input.value.split(",") : [];
        arr.push(id);
        input.value = arr.join(",");
      }
      tr.remove();
    }
  }

  // hapus mutasi
  if(e.target.classList.contains("btnHapusMutasi")){
    let tr = e.target.closest("tr");
    let id = tr.getAttribute("data-id");

    if(confirm("Yakin hapus mutasi ini?")){
      if(id){
        let input = document.getElementById("deleted_mutasi");
        let arr = input.value ? input.value.split(",") : [];
        arr.push(id);
        input.value = arr.join(",");
      }
      tr.remove();
    }
  }

});
</script>

</body>
</html>