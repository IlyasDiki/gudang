<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id_barang_briket = $_GET['id_barang_briket'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tahun = (int)$tahun;

$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

if ($id_barang_briket == '') {
  die("Barang belum dipilih.");
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

/* =========================
   FORMAT
========================= */
function fCell($v){
  if ($v === null) return "-";
  if ($v === "" ) return "-";
  if ($v == 0 || $v === "0" || $v === "0.00") return "-";
  return $v;
}

$rows = [];
$grandTotalSaldo = 0;

while($r = mysqli_fetch_assoc($q)){

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

  $rows[] = [
    'tgl_produksi' => $r['tgl_produksi'] ? date('d-M-y', strtotime($r['tgl_produksi'])) : '-',
    'tgl_bongkar'  => $r['tgl_bongkar'] ? date('d-M-y', strtotime($r['tgl_bongkar'])) : '-',
    'saldo_krg'    => ($saldo_krg == 0 ? "-" : $saldo_krg),
    'saldo_add'    => ($saldo_add == 0 ? "-" : number_format($saldo_add,2)),
    'saldo_jml'    => ($saldo_jml == 0 ? "-" : number_format($saldo_jml,2)),
  ];
}

/* =========================
   HTML UNTUK PDF
========================= */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { size: A4 landscape; margin: 10mm; }
  body { font-family: Arial, sans-serif; font-size: 12px; }
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #000; padding: 6px; font-size: 11px; }
  th { text-align: center; font-weight: bold; background: #e9d9c7; }
  .text-center { text-align: center; }
  .text-end { text-align: right; }
  .header-title { font-size: 26px; font-weight: 900; margin: 0; }
  .sub { font-size: 14px; font-style: italic; margin: 0; }
  .kode { font-size: 22px; font-weight: 800; margin-top: 10px; }
  .box { background: #8BC34A; border: 2px solid #000; padding: 8px 14px; font-weight: 900; width: 200px; margin-top: 10px; text-align:center; }
  .total { margin-top: 10px; border: 2px solid #000; height: 45px; width: 100%; }
  .total td { border: 2px solid #000; font-weight: 900; font-size: 18px; }
</style>
</head>
<body>

<div class="header-title">RINGKASAN</div>
<div class="sub">PER : '.date('F Y', strtotime($tglAwal)).'</div>
<div class="kode">'.htmlspecialchars($namaBarang).'</div>
<div class="box">LOLOS</div>

<br>

<table>
  <tr>
    <th rowspan="2" style="width:140px;">PRODUKSI</th>
    <th rowspan="2" style="width:140px;">BONGKAR<br>OVEN</th>
    <th colspan="3">RINCIAN KARUNG</th>
    <th rowspan="2" style="width:160px;">SALDO (KG)</th>
    <th rowspan="2">KET</th>
  </tr>
  <tr>
    <th style="width:80px;">KRG</th>
    <th style="width:80px;">KG</th>
    <th style="width:80px;">ADD</th>
  </tr>
';

if(count($rows) == 0){
  $html .= '<tr><td colspan="7" class="text-center">Tidak ada data pada periode ini.</td></tr>';
} else {
  foreach($rows as $row){
    $html .= '
      <tr>
        <td class="text-center">'.$row['tgl_produksi'].'</td>
        <td class="text-center">'.$row['tgl_bongkar'].'</td>
        <td class="text-center">'.$row['saldo_krg'].'</td>
        <td class="text-center">25</td>
        <td class="text-center">'.$row['saldo_add'].'</td>
        <td class="text-end">'.$row['saldo_jml'].'</td>
        <td class="text-center">0</td>
      </tr>
    ';
  }
}

$html .= '
</table>

<table class="total" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width:70%; text-align:center;">TOTAL SALDO</td>
    <td style="width:30%; text-align:center;">'.number_format($grandTotalSaldo,2).'</td>
  </tr>
</table>

</body>
</html>
';

/* =========================
   DOMPDF RENDER
========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "RINGKASAN_{$kodeBarang}_{$bulan}_{$tahun}.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
