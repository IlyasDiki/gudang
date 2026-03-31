<?php
require '../config/init.php';

$id_barang_briket = $_GET['id_barang_briket'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tahun = (int)$tahun;

$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

/* =========================
   DEFAULT BARANG
========================= */
if ($id_barang_briket == '') {
  $qDef = mysqli_query($conn, "
    SELECT id_barang
    FROM barang
    WHERE id_kelompok = '6'
    ORDER BY nama_barang ASC
    LIMIT 1
  ");
  if ($qDef && mysqli_num_rows($qDef) > 0) {
    $id_barang_briket = mysqli_fetch_assoc($qDef)['id_barang'];
  }
}

$id_barang_briket = (int)$id_barang_briket;

/* =========================
   NAMA BARANG
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

/* =========================
   QUERY RINGKASAN (SALDO)
========================= */
$q = mysqli_query($conn, "
  SELECT
    b.id_bk,
    b.tanggal AS tgl_produksi,

    (SELECT MIN(tanggal_bongkar)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS tgl_bongkar,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS bongkar_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk
    ) AS bongkar_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING'
    ) AS packing_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING'
    ) AS packing_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO'
    ) AS repro_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO'
    ) AS repro_add,

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
  ORDER BY b.tanggal ASC
");

if(!$q){
  die("Query error: " . mysqli_error($conn));
}

$f = function($v){
  if ($v === null) return "-";
  if ($v === "" ) return "-";
  if ($v == 0 || $v === "0" || $v === "0.00") return "-";
  return $v;
};

$grandTotalSaldo = 0;

/* =========================
   HEADER EXCEL
========================= */
$filename = "RINGKASAN_{$kodeBarang}_{$bulan}_{$tahun}.xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html>
<head>
<meta charset="utf-8">
<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
  th { text-align: center; font-weight: bold; background: #e9d9c7; }
  .text-center { text-align: center; }
  .text-end { text-align: right; }
</style>
</head>
<body>

<h2>RINGKASAN</h2>
<div>PER : <?= date('F Y', strtotime($tglAwal)) ?></div>
<div><b><?= htmlspecialchars($namaBarang) ?></b></div>
<br>

<table>
  <tr>
    <th rowspan="2">PRODUKSI</th>
    <th rowspan="2">BONGKAR OVEN</th>
    <th colspan="3">RINCIAN KARUNG</th>
    <th rowspan="2">SALDO (KG)</th>
    <th rowspan="2">KET</th>
  </tr>
  <tr>
    <th>KRG</th>
    <th>KG</th>
    <th>ADD</th>
  </tr>

<?php if(mysqli_num_rows($q) == 0): ?>
  <tr>
    <td colspan="7" class="text-center">Tidak ada data pada periode ini.</td>
  </tr>
<?php endif; ?>

<?php while($r = mysqli_fetch_assoc($q)): ?>
  <?php
    $bongkar_krg = (float)$r['bongkar_krg'];
    $bongkar_add = (float)$r['bongkar_add'];
    $bongkar_jml = ($bongkar_krg * 25) + $bongkar_add;

    $packing_jml = ((float)$r['packing_krg'] * 25) + (float)$r['packing_add'];
    $repro_jml   = ((float)$r['repro_krg'] * 25) + (float)$r['repro_add'];
    $jual_jml    = ((float)$r['jual_krg'] * 25) + (float)$r['jual_add'];

    $saldo_jml = $bongkar_jml - ($packing_jml + $repro_jml + $jual_jml);

    $saldo_krg = floor($saldo_jml / 25);
    $saldo_add = $saldo_jml - ($saldo_krg * 25);

    $grandTotalSaldo += $saldo_jml;

    $tglProduksi = $r['tgl_produksi'] ? date('d-M-y', strtotime($r['tgl_produksi'])) : '-';
    $tglBongkar  = $r['tgl_bongkar'] ? date('d-M-y', strtotime($r['tgl_bongkar'])) : '-';
  ?>
  <tr>
    <td class="text-center"><?= $tglProduksi ?></td>
    <td class="text-center"><?= $tglBongkar ?></td>

    <td class="text-center"><?= $f($saldo_krg) ?></td>
    <td class="text-center">25</td>
    <td class="text-center"><?= $f($saldo_add==0 ? "-" : number_format($saldo_add,2)) ?></td>

    <td class="text-end"><?= $f($saldo_jml==0 ? "-" : number_format($saldo_jml,2)) ?></td>
    <td class="text-center">0</td>
  </tr>
<?php endwhile; ?>
</table>

<br>

<table>
  <tr>
    <th style="width: 200px;">TOTAL SALDO</th>
    <th class="text-end"><?= number_format($grandTotalSaldo,2) ?></th>
  </tr>
</table>

</body>
</html>
