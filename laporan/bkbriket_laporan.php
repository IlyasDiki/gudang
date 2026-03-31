<?php
require '../config/init.php';

function f_angka($v, $dec=2){
  if($v == 0 || $v === "0" || $v === "0.00") return "-";
  return number_format((float)$v, $dec, '.', '');
}

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$id_barang_briket = $_GET['id_barang_briket'] ?? '';

$bulan = sprintf("%02d", (int)$bulan);
$tahun = (int)$tahun;
$id_barang_briket = (int)$id_barang_briket;

$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

/* ===============================
   AMBIL LIST JENIS BRIKET
   =============================== */
$qBarang = mysqli_query($conn, "
  SELECT id_barang, kode_barang, nama_barang
  FROM barang
  WHERE nama_barang LIKE '%briket%'
  ORDER BY kode_barang ASC
");

/* ===============================
   AMBIL NAMA BRIKET TERPILIH
   =============================== */
$namaBriket = "";
if($id_barang_briket > 0){
  $qb = mysqli_query($conn, "
    SELECT kode_barang, nama_barang
    FROM barang
    WHERE id_barang = $id_barang_briket
    LIMIT 1
  ");
  if($rb = mysqli_fetch_assoc($qb)){
    $namaBriket = $rb['kode_barang']." - ".$rb['nama_barang'];
  }
}

/* ===============================
   QUERY LAPORAN (PER BRIKET)
   =============================== */
$data = [];
if($id_barang_briket > 0){

$q = mysqli_query($conn, "
  SELECT
    b.id_bk,
    b.tanggal,
    b.lokasi,
    b.keterangan,

    COALESCE(bo.bongkar_krg,0) AS bongkar_krg,
    COALESCE(bo.bongkar_add,0) AS bongkar_add,

    COALESCE(mu.packing_krg,0) AS packing_krg,
    COALESCE(mu.packing_add,0) AS packing_add,

    COALESCE(mu.repro_krg,0) AS repro_krg,
    COALESCE(mu.repro_add,0) AS repro_add,

    COALESCE(mu.jual_krg,0) AS jual_krg,
    COALESCE(mu.jual_add,0) AS jual_add

  FROM bkbriket b

  LEFT JOIN (
    SELECT 
      id_bk,
      SUM(krg) AS bongkar_krg,
      SUM(add_kg) AS bongkar_add
    FROM bkbriket_bongkar
    GROUP BY id_bk
  ) bo ON bo.id_bk = b.id_bk

  LEFT JOIN (
    SELECT
      id_bk,

      SUM(CASE WHEN jenis='PACKING' THEN krg ELSE 0 END) AS packing_krg,
      SUM(CASE WHEN jenis='PACKING' THEN add_kg ELSE 0 END) AS packing_add,

      SUM(CASE WHEN jenis='REPRO' THEN krg ELSE 0 END) AS repro_krg,
      SUM(CASE WHEN jenis='REPRO' THEN add_kg ELSE 0 END) AS repro_add,

      SUM(CASE WHEN jenis='JUAL' THEN krg ELSE 0 END) AS jual_krg,
      SUM(CASE WHEN jenis='JUAL' THEN add_kg ELSE 0 END) AS jual_add

    FROM bkbriket_mutasi
    GROUP BY id_bk
  ) mu ON mu.id_bk = b.id_bk

  WHERE b.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
    AND b.id_barang_briket = $id_barang_briket

  ORDER BY b.tanggal ASC
");

  if(!$q){
    die("Query error: " . mysqli_error($conn));
  }

  while($r = mysqli_fetch_assoc($q)){
    $data[] = $r;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Laporan Produksi Briket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">

  <style>
    body { background: #f4f6f9; }
    .report-wrapper{ max-width: 1400px; margin: 20px auto; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #000; padding: 4px; font-size: 12px; }
    th { text-align: center; vertical-align: middle; font-weight: bold; background: #e9d9c7; }
    td { vertical-align: middle; }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .btn-export { min-width: 120px; }
    .table-responsive { overflow-x: auto; }
  </style>
</head>

<body>

<div class="report-wrapper">
  <div class="card shadow-sm">
    <div class="card-body">

      <a href="../index.php" class="btn btn-info btn-sm mb-2">
        <i class="fa fa-arrow-left"></i> Back
      </a>

      <h4 class="mb-0">Mutasi Produksi Briket</h4>
      <small class="text-muted">
        <?= ($namaBriket ? "Jenis: <b>".htmlspecialchars($namaBriket)."</b> | " : "") ?>
        Periode: <b><?= date('F Y', strtotime($tglAwal)) ?></b>
      </small>

      <hr>

      <!-- FILTER -->
      <form method="get" class="form-inline mb-3">

        <label class="mr-2 font-weight-bold">Jenis Briket</label>
        <select name="id_barang_briket" class="form-control mr-4" required>
          <option value="">-- Pilih Briket --</option>
          <?php while($br = mysqli_fetch_assoc($qBarang)): ?>
            <option value="<?= $br['id_barang'] ?>"
              <?= ($id_barang_briket == $br['id_barang']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($br['kode_barang']." - ".$br['nama_barang']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <label class="mr-2 font-weight-bold">Bulan</label>
        <select name="bulan" class="form-control mr-4">
          <?php for($b=1;$b<=12;$b++):
            $v = sprintf("%02d",$b);
          ?>
            <option value="<?= $v ?>" <?= $bulan==$v?'selected':'' ?>>
              <?= date('F', mktime(0,0,0,$b,1)) ?>
            </option>
          <?php endfor ?>
        </select>

        <label class="mr-2 font-weight-bold">Tahun</label>
        <select name="tahun" class="form-control mr-4">
          <?php for($t=date('Y')-5;$t<=date('Y')+1;$t++): ?>
            <option value="<?= $t ?>" <?= $tahun==$t?'selected':'' ?>>
              <?= $t ?>
            </option>
          <?php endfor ?>
        </select>

        <button class="btn btn-primary btn-export mr-2">
          Tampilkan
        </button>

        <?php if($id_barang_briket > 0): ?>
          <a class="btn btn-success btn-export mr-2"
            href="bkbriket_export_excel.php?id_barang_briket=<?= $id_barang_briket ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
            Export Excel
          </a>

          <a class="btn btn-danger btn-export"
            href="bkbriket_export_pdf.php?id_barang_briket=<?= $id_barang_briket ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" target="_blank">
            Export PDF
          </a>
        <?php endif; ?>

      </form>

      <?php if($id_barang_briket <= 0): ?>
        <div class="alert alert-warning">
          Silakan pilih jenis briket dulu untuk melihat ringkasan.
        </div>
      <?php else: ?>

      <!-- TABEL -->
      <div class="table-responsive">
        <table>
          <tr>
            <th rowspan="3">NO</th>
            <th rowspan="3">PRODUKSI</th>

            <th colspan="2">BONGKAR OVEN</th>
            <th colspan="3">RINCIAN KARUNG</th>

            <th rowspan="3">JML KG</th>
            <th rowspan="3">LOKASI</th>

            <th colspan="10">MUTASI</th>

            <th rowspan="3">KETERANGAN</th>
            <th colspan="3">SALDO</th>
          </tr>

          <tr>
            <th rowspan="2">KRG</th>
            <th rowspan="2">ADD</th>

            <th rowspan="2">JML</th>
            <th rowspan="2">KG</th>
            <th rowspan="2">ADD</th>

            <th rowspan="2">TGL</th>

            <th colspan="3">PACKING</th>
            <th colspan="3">REPRO</th>
            <th colspan="3">JUAL</th>

            <th rowspan="2">KRG</th>
            <th rowspan="2">ADD</th>
            <th rowspan="2">JML</th>
          </tr>

          <tr>
            <th>KRG</th><th>ADD</th><th>JML</th>
            <th>KRG</th><th>ADD</th><th>JML</th>
            <th>KRG</th><th>ADD</th><th>JML</th>
          </tr>

          <?php
          $no=1;
          foreach($data as $r):

            $bongkar_krg = (float)$r['bongkar_krg'];
            $bongkar_add = (float)$r['bongkar_add'];
            $bongkar_jml = ($bongkar_krg * 25) + $bongkar_add;

            $packing_krg = (float)$r['packing_krg'];
            $packing_add = (float)$r['packing_add'];
            $packing_jml = ($packing_krg * 25) + $packing_add;

            $repro_krg = (float)$r['repro_krg'];
            $repro_add = (float)$r['repro_add'];
            $repro_jml = ($repro_krg * 25) + $repro_add;

            $jual_krg = (float)$r['jual_krg'];
            $jual_add = (float)$r['jual_add'];
            $jual_jml = ($jual_krg * 25) + $jual_add;

            $saldo_jml = $bongkar_jml - $packing_jml - $repro_jml - $jual_jml;
            $saldo_krg = floor($saldo_jml / 25);
            $saldo_add = $saldo_jml - ($saldo_krg * 25);
          ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td class="text-center"><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>

            <td class="text-end"><?= f_angka($bongkar_krg,0) ?></td>
            <td class="text-end"><?= f_angka($bongkar_add,2) ?></td>

            <td class="text-end"><?= f_angka($bongkar_krg,0) ?></td>
            <td class="text-center">25</td>
            <td class="text-end"><?= f_angka($bongkar_add,2) ?></td>

            <td class="text-end"><?= f_angka($bongkar_jml,2) ?></td>
            <td class="text-center"><?= htmlspecialchars($r['lokasi'] ?? '-') ?></td>

            <td class="text-center"><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>

            <td class="text-end"><?= f_angka($packing_krg,0) ?></td>
            <td class="text-end"><?= f_angka($packing_add,2) ?></td>
            <td class="text-end"><?= f_angka($packing_jml,2) ?></td>

            <td class="text-end"><?= f_angka($repro_krg,0) ?></td>
            <td class="text-end"><?= f_angka($repro_add,2) ?></td>
            <td class="text-end"><?= f_angka($repro_jml,2) ?></td>

            <td class="text-end"><?= f_angka($jual_krg,0) ?></td>
            <td class="text-end"><?= f_angka($jual_add,2) ?></td>
            <td class="text-end"><?= f_angka($jual_jml,2) ?></td>

            <!-- KETERANGAN DULU -->
            <td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>

            <!-- SALDO -->
            <td class="text-end"><?= f_angka($saldo_krg,0) ?></td>
            <td class="text-end"><?= f_angka($saldo_add,2) ?></td>
            <td class="text-end"><?= f_angka($saldo_jml,2) ?></td>
          </tr>
          <?php endforeach; ?>

          <?php if(count($data) == 0): ?>
            <tr>
              <td colspan="24" class="text-center">
                Tidak ada data pada periode ini.
              </td>
            </tr>
          <?php endif; ?>
        </table>
      </div>

      <div class="mt-3 text-muted" style="font-size: 12px;">
        Catatan: JML = (KRG × 25) + ADD.
      </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
</body>
</html>
