<?php
require '../config/init.php';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_BK_Briket_{$tahun}_{$bulan}.xls");

$q = mysqli_query($conn, "
  SELECT
    b.id_bk,
    b.tanggal,
    b.lokasi,
    b.keterangan,

    COALESCE(SUM(bo.krg),0) AS bongkar_krg,
    COALESCE(SUM(bo.add_kg),0) AS bongkar_add,

    COALESCE(SUM(CASE WHEN m.jenis='PACKING' THEN m.krg ELSE 0 END),0) AS packing_krg,
    COALESCE(SUM(CASE WHEN m.jenis='PACKING' THEN m.add_kg ELSE 0 END),0) AS packing_add,

    COALESCE(SUM(CASE WHEN m.jenis='REPRO' THEN m.krg ELSE 0 END),0) AS repro_krg,
    COALESCE(SUM(CASE WHEN m.jenis='REPRO' THEN m.add_kg ELSE 0 END),0) AS repro_add,

    COALESCE(SUM(CASE WHEN m.jenis='JUAL' THEN m.krg ELSE 0 END),0) AS jual_krg,
    COALESCE(SUM(CASE WHEN m.jenis='JUAL' THEN m.add_kg ELSE 0 END),0) AS jual_add

  FROM bkbriket b
  LEFT JOIN bkbriket_bongkar bo ON bo.id_bk = b.id_bk
  LEFT JOIN bkbriket_mutasi m ON m.id_bk = b.id_bk

  WHERE b.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY b.id_bk
  ORDER BY b.tanggal ASC
");

if(!$q){
  die("Query error: " . mysqli_error($conn));
}

echo "
<html>
<head>
<meta charset='utf-8'>
<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #000; padding: 4px; font-size: 11px; }
  th { text-align: center; vertical-align: middle; font-weight: bold; background: #f2f2f2; }
  td { vertical-align: middle; }
  .text-end { text-align: right; }
  .text-center { text-align: center; }
</style>
</head>
<body>
";

echo "<h3>BK Briket Bulan $bulan - $tahun</h3>";

echo "<table>";

// ===== HEADER BARIS 1 =====
echo "
<tr>
  <th rowspan='3'>NO</th>
  <th rowspan='3'>PRODUKSI</th>

  <th colspan='2'>BONGKAR OVEN</th>
  <th colspan='3'>RINCIAN KARUNG</th>

  <th rowspan='3'>JML KG</th>
  <th rowspan='3'>LOKASI</th>

  <th colspan='10'>MUTASI</th>

  <th rowspan='3'>KETERANGAN</th>
</tr>
";

// ===== HEADER BARIS 2 =====
echo "
<tr>
  <th rowspan='2'>KRG</th>
  <th rowspan='2'>ADD</th>

  <th rowspan='2'>JML</th>
  <th rowspan='2'>KG</th>
  <th rowspan='2'>ADD</th>

  <th rowspan='2'>TGL</th>

  <th colspan='3'>PACKING</th>
  <th colspan='3'>REPRO</th>
  <th colspan='3'>JUAL</th>
  <th colspan='3'>SALDO</th>
</tr>
";

// ===== HEADER BARIS 3 =====
echo "
<tr>
  <th>KRG</th>
  <th>ADD</th>
  <th>JML</th>

  <th>KRG</th>
  <th>ADD</th>
  <th>JML</th>

  <th>KRG</th>
  <th>ADD</th>
  <th>JML</th>

  <th>KRG</th>
  <th>ADD</th>
  <th>JML</th>
</tr>
";

$no = 1;

while($r = mysqli_fetch_assoc($q)){

  // Bongkar
  $bongkar_krg = (float)$r['bongkar_krg'];
  $bongkar_add = (float)$r['bongkar_add'];
  $bongkar_jml = ($bongkar_krg * 25) + $bongkar_add;

  // Mutasi
  $packing_krg = (float)$r['packing_krg'];
  $packing_add = (float)$r['packing_add'];
  $packing_jml = ($packing_krg * 25) + $packing_add;

  $repro_krg = (float)$r['repro_krg'];
  $repro_add = (float)$r['repro_add'];
  $repro_jml = ($repro_krg * 25) + $repro_add;

  $jual_krg = (float)$r['jual_krg'];
  $jual_add = (float)$r['jual_add'];
  $jual_jml = ($jual_krg * 25) + $jual_add;

  // Saldo
  $saldo_jml = $bongkar_jml - ($packing_jml + $repro_jml + $jual_jml);

  // Saldo dibagi jadi KRG + ADD (opsional, biar mirip excel)
  $saldo_krg = floor($saldo_jml / 25);
  $saldo_add = $saldo_jml - ($saldo_krg * 25);

  echo "<tr>";

  echo "<td class='text-center'>".$no++."</td>";
  echo "<td class='text-center'>".date('d-m-Y', strtotime($r['tanggal']))."</td>";

  // Bongkar Oven
  echo "<td class='text-end'>".$bongkar_krg."</td>";
  echo "<td class='text-end'>".$bongkar_add."</td>";

  // Rincian Karung
  echo "<td class='text-end'>".$bongkar_krg."</td>";
  echo "<td class='text-center'>25</td>";
  echo "<td class='text-end'>".$bongkar_add."</td>";

  // JML KG
  echo "<td class='text-end'>".number_format($bongkar_jml,2)."</td>";

  // Lokasi
  echo "<td class='text-center'>".htmlspecialchars($r['lokasi'] ?? '')."</td>";

  // Mutasi: Tgl
  echo "<td class='text-center'>".date('d-m-Y', strtotime($r['tanggal']))."</td>";

  // Packing
  echo "<td class='text-end'>".$packing_krg."</td>";
  echo "<td class='text-end'>".$packing_add."</td>";
  echo "<td class='text-end'>".number_format($packing_jml,2)."</td>";

  // Repro
  echo "<td class='text-end'>".$repro_krg."</td>";
  echo "<td class='text-end'>".$repro_add."</td>";
  echo "<td class='text-end'>".number_format($repro_jml,2)."</td>";

  // Jual
  echo "<td class='text-end'>".$jual_krg."</td>";
  echo "<td class='text-end'>".$jual_add."</td>";
  echo "<td class='text-end'>".number_format($jual_jml,2)."</td>";

  // Saldo
  echo "<td class='text-end'>".$saldo_krg."</td>";
  echo "<td class='text-end'>".number_format($saldo_add,2)."</td>";
  echo "<td class='text-end'>".number_format($saldo_jml,2)."</td>";

  // Keterangan
  echo "<td>".htmlspecialchars($r['keterangan'] ?? '')."</td>";

  echo "</tr>";
}

echo "</table>";
echo "</body></html>";