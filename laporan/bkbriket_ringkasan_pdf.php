<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id_barang_briket = $_GET['id_barang_briket'] ?? '';
$status = $_GET['status'] ?? '';
$statusLabel = $status ? strtoupper($status) : 'LOLOS';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

$whereBarangFilter = '';
if ($id_barang_briket != '') {
  $whereBarangFilter = "AND b.id_barang_briket = '$id_barang_briket'";
}

$statusFilter = '';
if ($status) {
  $statusFilter = "AND b.status = '$status'";
}

/* =========================
   QUERY (SAMA EXCEL)
========================= */
$q = mysqli_query($conn, "
  SELECT
    b.id_bk,
    b.tanggal,
    br.nama_barang,

    (SELECT MIN(tanggal_bongkar)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS tgl_bongkar,

    (SELECT GROUP_CONCAT(DISTINCT ket SEPARATOR ', ')
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS ket,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS bongkar_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS bongkar_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING') AS packing_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING') AS packing_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO') AS repro_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO') AS repro_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL') AS jual_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL') AS jual_add

  FROM bkbriket b
  JOIN barang br ON br.id_barang = b.id_barang_briket

  WHERE
    b.tanggal <= '$tglAkhir'
    $statusFilter
    $whereBarangFilter

  ORDER BY br.nama_barang, b.tanggal ASC
");

if(!$q){
  die("Query error: " . mysqli_error($conn));
}

/* =========================
   GROUP DATA (FIX)
========================= */
$dataGroup = [];
$grandTotal = 0;

while($r = mysqli_fetch_assoc($q)){

  $bongkar = ($r['bongkar_krg'] * 25) + $r['bongkar_add'];
  $packing = ($r['packing_krg'] * 25) + $r['packing_add'];
  $repro   = ($r['repro_krg'] * 25) + $r['repro_add'];
  $jual    = ($r['jual_krg'] * 25) + $r['jual_add'];

  $saldo = $bongkar - ($packing + $repro + $jual);

  if ($saldo <= 0) continue;

  $dataGroup[$r['nama_barang']][] = [
    'tgl' => $r['tanggal'],
    'bongkar' => $r['tgl_bongkar'],
    'saldo' => $saldo,
    'krg' => floor($saldo/25),
    'add' => $saldo - (floor($saldo/25)*25),
    'ket' => $r['ket']
  ];
}
$statusLabel = $status ? strtoupper($status) : 'LOLOS';

$statusClass = ($statusLabel == 'KARANTINA') 
  ? 'status-karantina' 
  : 'status-lolos';
/* =========================
   HTML
========================= */
$html = '
<style>
@page { size: A4 portrait; margin: 10mm; }

body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
.status-lolos { color: green; font-weight: bold; }
.status-karantina { color: red; font-weight: bold; }
table { border-collapse: collapse; width: 100%; table-layout: fixed; }

th, td { border: 1px solid #000; padding: 4px; }
th { background: #eaeaea; text-align: center; }

td { text-align: center; }
.td-ket { text-align: left; }
.td-total { text-align: right; font-weight: bold; }

.barang-row td {
  background: #d9d9d9;
  font-weight: bold;
  border: none;
  text-align: left;
}

.judul { text-align: center; font-size: 18px; font-weight: bold; }
.subjudul { text-align: center; font-size: 14px; }
.status { color: green; font-weight: bold; }
</style>

<div class="judul">RINGKASAN BRIKET <span class="'.$statusClass.'">'.$statusLabel.'</span></div>
<div class="subjudul">PT DIAN CIPTA SEJAHTERA</div>
<br>

<table>
<tr>
  <th rowspan="2">PRODUKSI</th>
  <th rowspan="2">BONGKAR</th>
  <th colspan="3">RINCIAN KARUNG</th>
  <th rowspan="2">JUMLAH</th>
  <th rowspan="2">TOTAL</th>
  <th rowspan="2">KETERANGAN</th>
</tr>
<tr>
  <th>KRG</th>
  <th>KG</th>
  <th>ADD</th>
</tr>
';

/* =========================
   HANDLE DATA KOSONG
========================= */
if (empty($dataGroup)) {
  $html .= '<tr><td colspan="8" style="text-align:center;">Tidak ada data</td></tr>';
}

/* =========================
   LOOP DATA
========================= */
foreach($dataGroup as $barang => $rows){

  $rowspan = count($rows);

  $totalBarang = 0;
  foreach($rows as $x){
    $totalBarang += $x['saldo'];
  }

  $html .= '
  <tr class="barang-row">
    <td colspan="8">'.$barang.'</td>
  </tr>';

  $first = true;

  foreach($rows as $r){

    $html .= '<tr>
      <td>'.date('j-M-Y', strtotime($r['tgl'])).'</td>
      <td>'.($r['bongkar'] ? date('j-M-Y', strtotime($r['bongkar'])) : '-').'</td>
      <td>'.$r['krg'].'</td>
      <td>25</td>
      <td>'.number_format($r['add'],2).'</td>
      <td class="td-total">'.number_format($r['saldo'],2).'</td>';

    if($first){
      $html .= '
        <td rowspan="'.$rowspan.'" class="td-total">'.number_format($totalBarang,2).'</td>
        <td rowspan="'.$rowspan.'" class="td-ket">'.$rows[0]['ket'].'</td>
      ';
    }

    $html .= '</tr>';
    $first = false;
  }

  $grandTotal += $totalBarang;
}

$html .= '
</table>

<br>

<table style="width:100%;">
<tr>
  <td style="border:0; font-weight:bold; font-size:18px;">TOTAL</td>
  <td style="border:0; text-align:right; font-weight:bold; font-size:18px;">
    '.number_format($grandTotal,2).'
  </td>
</tr>
</table>
';

/* =========================
   RENDER PDF
========================= */
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

/* PENTING: portrait */
$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

/* 🔥 INI YANG BIKIN PREVIEW (BUKAN DOWNLOAD) */
$dompdf->stream("ringkasan.pdf", ["Attachment" => false]);
exit;