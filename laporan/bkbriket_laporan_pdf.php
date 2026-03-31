<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";
use Dompdf\Dompdf;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

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

$html = "
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<style>
  body { font-family: Arial, sans-serif; font-size: 10px; }
  h3 { margin: 0 0 10px 0; }
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #000; padding: 3px; }
  th { text-align: center; vertical-align: middle; font-weight: bold; background: #e9d9c7; }
  td { vertical-align: middle; }
  .text-end { text-align: right; }
  .text-center { text-align: center; }
</style>
</head>
<body>

<h3>Laporan BK Briket Bulan $bulan - $tahun</h3>

<table>
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

<tr>
  <th>KRG</th><th>ADD</th><th>JML</th>
  <th>KRG</th><th>ADD</th><th>JML</th>
  <th>KRG</th><th>ADD</th><th>JML</th>
  <th>KRG</th><th>ADD</th><th>JML</th>
</tr>
";

$no=1;
while($r=mysqli_fetch_assoc($q)){

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

  $saldo_jml = $bongkar_jml - ($packing_jml + $repro_jml + $jual_jml);
  $saldo_krg = floor($saldo_jml / 25);
  $saldo_add = $saldo_jml - ($saldo_krg * 25);

  $html .= "
  <tr>
    <td class='text-center'>".$no++."</td>
    <td class='text-center'>".date('d-m-Y', strtotime($r['tanggal_produksi']))."</td>

    <td class='text-end'>".$bongkar_krg."</td>
    <td class='text-end'>".number_format($bongkar_add,2)."</td>

    <td class='text-end'>".$bongkar_krg."</td>
    <td class='text-center'>25</td>
    <td class='text-end'>".number_format($bongkar_add,2)."</td>

    <td class='text-end'>".number_format($bongkar_jml,2)."</td>
    <td class='text-center'>".htmlspecialchars($r['lokasi'] ?? '')."</td>

    <td class='text-center'>".date('d-m-Y', strtotime($r['tanggal_produksi']))."</td>

    <td class='text-end'>".$packing_krg."</td>
    <td class='text-end'>".number_format($packing_add,2)."</td>
    <td class='text-end'>".number_format($packing_jml,2)."</td>

    <td class='text-end'>".$repro_krg."</td>
    <td class='text-end'>".number_format($repro_add,2)."</td>
    <td class='text-end'>".number_format($repro_jml,2)."</td>

    <td class='text-end'>".$jual_krg."</td>
    <td class='text-end'>".number_format($jual_add,2)."</td>
    <td class='text-end'>".number_format($jual_jml,2)."</td>

    <td class='text-end'>".$saldo_krg."</td>
    <td class='text-end'>".number_format($saldo_add,2)."</td>
    <td class='text-end'>".number_format($saldo_jml,2)."</td>

    <td>".htmlspecialchars($r['keterangan'] ?? '')."</td>
  </tr>
  ";
}

$html .= "</table></body></html>";

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("BK_BRIKET_{$tahun}_{$bulan}.pdf", ["Attachment" => false]);
exit;