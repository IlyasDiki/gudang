<?php
require '../config/init.php';

$id_barang_briket = $_GET['id_barang_briket'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

$status = $_GET['status'] ?? '';
$whereBarang = "WHERE id_kelompok IN (15,16)";
/* =========================
   LIST BARANG DROPDOWN
========================= */

if ($status == 'LOLOS') {
  $whereBarang = "WHERE id_kelompok = 15";
} elseif ($status == 'KARANTINA') {
  $whereBarang = "WHERE id_kelompok = 16";
}

/* =========================
   DEFAULT BARANG
========================= */
if ($id_barang_briket == '') {
  $qDef = mysqli_query($conn, "
    SELECT b2.id_barang
    FROM barang b2
    JOIN bkbriket b ON b.id_barang_briket = b2.id_barang
    GROUP BY b2.id_barang
    HAVING
      " . ($status == 'LOLOS' ? "
          SUM(b.status = 'LOLOS') > 0
          AND SUM(b.status = 'KARANTINA') = 0
      " : ($status == 'KARANTINA' ? "
          SUM(b.status = 'KARANTINA') > 0
          AND SUM(b.status = 'LOLOS') = 0
      " : "1=1")) . "
    LIMIT 1
  ");

  if ($qDef && mysqli_num_rows($qDef) > 0) {
    $id_barang_briket = mysqli_fetch_assoc($qDef)['id_barang'];
  }
}

/* =========================
   NAMA + KODE BARANG
========================= */
$namaBarang = '-';
$kodeBarang = '-';

$qBarang = mysqli_query($conn, "
  SELECT id_barang, nama_barang, kode_barang
  FROM barang
  WHERE id_barang = '$id_barang_briket'
  LIMIT 1
");
if ($qBarang && mysqli_num_rows($qBarang) > 0) {
  $rb = mysqli_fetch_assoc($qBarang);
  $namaBarang = $rb['nama_barang'] ?? '-';
  $kodeBarang = $rb['kode_barang'] ?? '-';
}

$qListBarang = mysqli_query($conn, "
  SELECT DISTINCT 
    br.id_barang,
    br.nama_barang,
    br.kode_barang
  FROM barang br
  JOIN bkbriket bk ON bk.id_barang_briket = br.id_barang
  " . ($status ? "WHERE bk.status = '$status'" : "") . "
  ORDER BY br.nama_barang ASC
");

$dataBarang = [];
while ($row = mysqli_fetch_assoc($qListBarang)) {
  $dataBarang[] = $row;
}

if ($id_barang_briket && count($dataBarang) == 0) {
  $id_barang_briket = '';
}
/* =========================
   BUILD STATUS FILTER
========================= */
$statusFilter = '';
if ($status) {
  $statusFilter = "AND b.status = '$status'";
}


/* =========================
   QUERY RINGKASAN (SAMA DENGAN LAPORAN)
========================= */
$q = mysqli_query($conn, "
  SELECT
    b.status,
    b.id_bk,
    b.tanggal AS tgl_produksi,

    -- tanggal bongkar oven pertama
    (SELECT MIN(tanggal_bongkar)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS tgl_bongkar,

    -- total bongkar (MASUK)
    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS bongkar_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS bongkar_add,

    -- PACKING
    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING'
    ) AS packing_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING'
    ) AS packing_add,

    -- REPRO
    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO'
    ) AS repro_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO'
    ) AS repro_add,

    -- JUAL
    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL'
    ) AS jual_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL'
    ) AS jual_add

  FROM bkbriket b
  WHERE
    b.id_barang_briket = '$id_barang_briket'
    AND b.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
    $statusFilter
  ORDER BY b.tanggal ASC
");

$statusLabel = $status ?: "LOLOS";

$qStatus = mysqli_query($conn, "
    SELECT status 
    FROM bkbriket
    WHERE id_barang_briket = '$id_barang_briket'
    AND tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
    LIMIT 1
");

if (!$q) {
  die("Query error: " . mysqli_error($conn));
}

/* =========================
   FORMAT BIAR 0 JADI "-"
========================= */
$f = function($v){
  if ($v === null) return "-";
  if ($v === "" ) return "-";
  if ($v == 0 || $v === "0" || $v === "0.00") return "-";
  return $v;
};

$grandTotalSaldo = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ringkasan Produksi Briket</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">

  <style>
    body { background: #f4f6f9; }

    .report-wrapper{
      max-width: 1100px;
      margin: 20px auto;
    }

    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #000; padding: 6px 6px; font-size: 12px; }
    th { text-align: center; vertical-align: middle; font-weight: bold; }
    td { vertical-align: middle; }

    .text-end { text-align: right; }
    .text-center { text-align: center; }

    .header-title{
      font-size: 28px;
      font-weight: 900;
      letter-spacing: 1px;
      margin-bottom: 0;
    }

    .per-label{
      font-size: 14px;
      font-style: italic;
      margin-top: -5px;
    }

    .kode-big{
      font-size: 28px;
      font-weight: 800;
      margin-top: 10px;
    }

    .lolos-box{
      background: #8BC34A;
      color: #000;
      font-weight: 900;
      font-size: 20px;
      padding: 10px 14px;
      border: 2px solid #000;
      width: 360px;
      margin-top: 15px;
      margin-bottom: 0;
    }

    .table-wrap{
      border: 2px solid #000;
      border-top: 0;
    }

    .table-wrap table{
      border: 0;
    }

    .table-wrap th, .table-wrap td{
      border: 1px solid #000;
    }

    .total-box{
      width: 100%;
      margin-top: 10px;
      border: 2px solid #000;
      display: flex;
      height: 50px;
      background: #fff;
    }

    .total-left{
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 20px;
      border-right: 2px solid #000;
    }

    .total-right{
      width: 260px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      font-size: 20px;
    }
  </style>
</head>

<body>
<div class="report-wrapper">

  <div class="card shadow-sm">
    <div class="card-body">

      <a href="../index.php" class="btn btn-info btn-sm mb-2">
        <i class="fa fa-arrow-left"></i> Back
      </a>
      <!-- HEADER -->
      <div>
        <div class="header-title">RINGKASAN</div>
        <div class="per-label">PER : <?= date('F Y', strtotime($tglAwal)) ?></div>
        <div class="kode-big"><?= htmlspecialchars($namaBarang) ?></div>
      </div>

      <!-- Filter -->
      <form method="get" class="mb-3">
        <div class="row">
          <div class="col-md-2">
            <label class="font-weight-bold">Status</label>
            <select name="status" class="form-control form-control-sm">
              <option value="">-- Semua Status --</option>
              <option value="LOLOS" <?= $status=='LOLOS'?'selected':'' ?>>LOLOS</option>
              <option value="KARANTINA" <?= $status=='KARANTINA'?'selected':'' ?>>KARANTINA</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="font-weight-bold">Barang</label>
            <select name="id_barang_briket" class="form-control form-control-sm" required>
              <?php foreach($dataBarang as $b): ?>
                <option value="<?= $b['id_barang'] ?>"
                  <?= ($id_barang_briket == $b['id_barang']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($b['nama_barang'] ?? '-') ?>
                </option>
                <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2">
            <label class="font-weight-bold">Bulan</label>
            <select name="bulan" class="form-control form-control-sm">
              <?php for($bln=1;$bln<=12;$bln++):
                $v = sprintf("%02d",$bln);
              ?>
                <option value="<?= $v ?>" <?= $bulan==$v?'selected':'' ?>>
                  <?= date('F', mktime(0,0,0,$bln,1)) ?>
                </option>
              <?php endfor ?>
            </select>
          </div>

          <div class="col-md-2">
            <label class="font-weight-bold">Tahun</label>
            <select name="tahun" class="form-control form-control-sm">
              <?php for($t=date('Y')-1;$t<=date('Y')+1;$t++): ?>
                <option value="<?= $t ?>" <?= $tahun==$t?'selected':'' ?>>
                  <?= $t ?>
                </option>
              <?php endfor ?>
            </select>
          </div>


          <div class="col-md-4">
            <label class="font-weight-bold">&nbsp;</label>
            <div class="d-flex gap-2">
              <button class="btn btn-primary btn-sm flex-grow-1">Tampilkan</button>
              <a class="btn btn-success btn-sm flex-grow-1"
                  href="bkbriket_ringkasan_excel.php?id_barang_briket=<?= $id_barang_briket ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&status=<?= $status ?>">
                  Export Excel
                </a>
              <a class="btn btn-danger btn-sm flex-grow-1"
                  href="bkbriket_ringkasan_pdf.php?id_barang_briket=<?= $id_barang_briket ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&status=<?= $status ?>"
                  target="_blank">
                  Export PDF
                </a>
            </div>
          </div>
        </div>
      </form>


      <div class="lolos-box 
    <?= ($statusLabel == 'KARANTINA') ? 'bg-danger text-white' : '' ?>">
    <?= htmlspecialchars($statusLabel) ?>
</div>

      <!-- TABLE -->
      <div class="table-wrap">
        <table>
          <tr>
            <th rowspan="2" style="width: 140px;">PRODUKSI</th>
            <th rowspan="2" style="width: 140px;">BONGKAR<br>OVEN</th>
            <th colspan="3">RINCIAN KARUNG</th>
            <th rowspan="2" style="width: 160px;">SALDO (KG)</th>
            <th rowspan="2">KET</th>
          </tr>
          <tr>
            <th style="width: 80px;">KRG</th>
            <th style="width: 80px;">KG</th>
            <th style="width: 80px;">ADD</th>
          </tr>

          <?php if(mysqli_num_rows($q) == 0): ?>
            <tr>
              <td colspan="7" class="text-center">Tidak ada data pada periode ini.</td>
            </tr>
          <?php endif; ?>

          <?php while($r = mysqli_fetch_assoc($q)): ?>
            
            <?php 
              // ======================
              // HITUNG BONGKAR (MASUK)
              // ======================
              $bongkar_krg = (float)$r['bongkar_krg'];
              $bongkar_add = (float)$r['bongkar_add'];
              $bongkar_jml = ($bongkar_krg * 25) + $bongkar_add;

              // ======================
              // HITUNG MUTASI (KELUAR)
              // ======================
              $packing_krg = (float)$r['packing_krg'];
              $packing_add = (float)$r['packing_add'];
              $packing_jml = ($packing_krg * 25) + $packing_add;

              $repro_krg = (float)$r['repro_krg'];
              $repro_add = (float)$r['repro_add'];
              $repro_jml = ($repro_krg * 25) + $repro_add;

              $jual_krg = (float)$r['jual_krg'];
              $jual_add = (float)$r['jual_add'];
              $jual_jml = ($jual_krg * 25) + $jual_add;

              // ======================
              // SALDO
              // ======================
              $saldo_jml = $bongkar_jml - ($packing_jml + $repro_jml + $jual_jml);

              // ubah saldo kg jadi krg + add (rumus laporan)
              $saldo_krg = floor($saldo_jml / 25);
              $saldo_add = $saldo_jml - ($saldo_krg * 25);

              // total bawah
              $grandTotalSaldo += $saldo_jml;

              $tglProduksi = $r['tgl_produksi'] ? date('d-M-y', strtotime($r['tgl_produksi'])) : '-';
              $tglBongkar = $r['tgl_bongkar'] ? date('d-M-y', strtotime($r['tgl_bongkar'])) : '-';

              // biar saldo minus tetap aman tampil
              $saldo_add_show = number_format($saldo_add,2);
              $saldo_jml_show = number_format($saldo_jml,2);
            ?>
            <tr>
              <td class="text-center"><?= $tglProduksi ?></td>
              <td class="text-center"><?= $tglBongkar ?></td>

              <!-- INI YANG DITAMPILKAN ADALAH SALDO -->
              <td class="text-center"><?= $f($saldo_krg) ?></td>
              <td class="text-center">25</td>
              <td class="text-center"><?= $f($saldo_add==0 ? "-" : $saldo_add_show) ?></td>

              <td class="text-end"><?= $f($saldo_jml==0 ? "-" : $saldo_jml_show) ?></td>

              <td class="text-center">0</td>
            </tr>
          <?php endwhile; ?>
        </table>
      </div>

      <!-- TOTAL -->
      <div class="total-box">
      <div class="total-left">TOTAL SALDO</div>
      <div class="total-right"><?= number_format($grandTotalSaldo,2) ?></div>
      </div>

    </div>
  </div>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.min.js"></script>
<script>
const form = document.querySelector('form');

// AUTO reload saat STATUS berubah
document.querySelector('select[name="status"]').addEventListener('change', function(){
    form.submit();
});

// AUTO reload saat BARANG berubah
document.querySelector('select[name="id_barang_briket"]').addEventListener('change', function(){
    form.submit();
});
</script>

</body>
</html>