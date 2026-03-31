<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;

function angka($nilai) {
  return rtrim(rtrim(number_format((float)$nilai, 2, '.', ''), '0'), '.');
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$tglAwal = sprintf("%04d-%02d-01", $tahun, $bulan);
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

/* =========================
   DATA PEMAKAIAN AT
========================= */
$q = mysqli_query($conn, "
  SELECT
    d.tanggal,
    b.nama_barang,
    k.nama_kelompok,
    d.sortir,
    d.ma,
    d.aa,
    d.b_mentah,
    d.air,
    d.atp
  FROM at_detail d
  JOIN barang b ON b.id_barang = d.id_barang
  JOIN kelompok_barang k ON k.id_kelompok = b.id_kelompok
  WHERE d.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  ORDER BY d.tanggal ASC
");

$dataAT = [];
$total = ['sortir'=>0,'ma'=>0,'aa'=>0,'b_mentah'=>0,'air'=>0,'atp'=>0];

while ($row = mysqli_fetch_assoc($q)) {
  $row['sortir'] = (float)$row['sortir'];
  $row['ma'] = (float)$row['ma'];
  $row['aa'] = (float)$row['aa'];
  $row['b_mentah'] = (float)$row['b_mentah'];
  $row['air'] = (float)$row['air'];
  $row['atp'] = (float)$row['atp'];

  $dataAT[] = $row;

  foreach ($total as $k => $v) {
    $total[$k] += $row[$k];
  }
}

/* =========================
   DATA PRODUKSI
========================= */
$qProd = mysqli_query($conn, "
  SELECT
    p.tanggal,
    pd.mixer
  FROM produksi p
  JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
  WHERE p.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  ORDER BY p.tanggal ASC
");

$dataProd = [];
while ($row = mysqli_fetch_assoc($qProd)) {
  $row['mixer'] = (float)$row['mixer'];
  $dataProd[] = $row;
}

/* =========================
   LOGIKA SALDO
========================= */
$jumlah_awal = 2000;

$total_pakai = array_sum($total);
$susut = $jumlah_awal - $total_pakai;

$kand_ma = $jumlah_awal > 0 ? ($total['ma'] / $jumlah_awal) * 100 : 0;
$kand_aa = $jumlah_awal > 0 ? ($total['aa'] / $jumlah_awal) * 100 : 0;
$kand_bm = $jumlah_awal > 0 ? ($total['b_mentah'] / $jumlah_awal) * 100 : 0;
$total_susut = $jumlah_awal > 0 ? ($susut / $jumlah_awal) * 100 : 0;

$saldoAT = $jumlah_awal;
$saldoProd = $total['atp'];

/* gabungan row */
$rowsAT = count($dataAT);
$rowsProd = count($dataProd);
$maxRows = max($rowsAT, $rowsProd);
if ($maxRows < 1) $maxRows = 1;

/* =========================
   HTML PDF
========================= */
$bulanNama = [
  1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

$html = '
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; font-size: 10px; }
    .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
    .subtitle { font-size: 11px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 3px 4px; text-align: center; vertical-align: middle; }
    th { font-weight: bold; }
  </style>
</head>
<body>
  <div class="title">Laporan Pemakaian AT</div>
  <div class="subtitle">Periode: '.$bulanNama[$bulan].' '.$tahun.'</div>

  <table>
    <tr>
      <th rowspan="2">NO</th>
      <th rowspan="2">TGL TERIMA</th>
      <th rowspan="2">JUMLAH</th>
      <th colspan="13">PEMAKAIAN AT</th>
      <th colspan="4">PEMAKAIAN PRODUKSI</th>
    </tr>
    <tr>
      <th>TGL</th>
      <th>SORTIR</th>
      <th>SALDO</th>
      <th>MA</th>
      <th>AA</th>
      <th>B.MENTAH</th>
      <th>AIR</th>
      <th>ATP</th>
      <th>Kand. MA</th>
      <th>Kand. AA</th>
      <th>Kand. BM</th>
      <th>Susut Timb.</th>
      <th>Total Pakai</th>
      <th>Total Susut</th>

      <th>JUMLAH</th>
      <th>TGL</th>
      <th>MIXER</th>
      <th>SALDO</th>
    </tr>
';

$no = 1;
for ($i=0; $i<$maxRows; $i++) {

  $at = $dataAT[$i] ?? null;
  $prod = $dataProd[$i] ?? null;

  $html .= '<tr>';

  if ($at) {
    $pakai_row = $at['sortir'] + $at['ma'] + $at['aa'] + $at['b_mentah'] + $at['air'] + $at['atp'];
    $saldoAT -= $pakai_row;

    $html .= '
      <td>'.$no.'</td>
      <td>'.$at['tanggal'].'</td>
      <td>'.angka($jumlah_awal).'</td>

      <td>'.$at['tanggal'].'</td>
      <td>'.angka($at['sortir']).'</td>
      <td>'.angka($saldoAT).'</td>
      <td>'.angka($at['ma']).'</td>
      <td>'.angka($at['aa']).'</td>
      <td>'.angka($at['b_mentah']).'</td>
      <td>'.angka($at['air']).'</td>
      <td>'.angka($at['atp']).'</td>
    ';

    if ($i == 0) {
      $html .= '
        <td>'.round($kand_ma,2).'%</td>
        <td>'.round($kand_aa,2).'%</td>
        <td>'.round($kand_bm,2).'%</td>
        <td>'.angka($susut).'</td>
        <td>'.angka($pakai_row).'</td>
        <td>'.round($total_susut,2).'%</td>
      ';
    } else {
      $html .= '
        <td></td><td></td><td></td><td></td>
        <td>'.angka($pakai_row).'</td>
        <td></td>
      ';
    }

    $no++;
  } else {
    // kosong AT
    $html .= '<td></td><td></td><td></td>';
    $html .= str_repeat('<td></td>', 14);
  }

  if ($prod) {
    $saldoProd -= $prod['mixer'];

    $html .= '
      <td>'.angka($total['atp']).'</td>
      <td>'.$prod['tanggal'].'</td>
      <td>'.angka($prod['mixer']).'</td>
      <td>'.angka($saldoProd).'</td>
    ';
  } else {
    $html .= '<td></td><td></td><td></td><td></td>';
  }

  $html .= '</tr>';
}

/* total row */
$html .= '
  <tr>
    <th colspan="4">TOTAL</th>
    <th>'.angka($total['sortir']).'</th>
    <th>-</th>
    <th>'.angka($total['ma']).'</th>
    <th>'.angka($total['aa']).'</th>
    <th>'.angka($total['b_mentah']).'</th>
    <th>'.angka($total['air']).'</th>
    <th>'.angka($total['atp']).'</th>
    <th>'.round($kand_ma,2).'%</th>
    <th>'.round($kand_aa,2).'%</th>
    <th>'.round($kand_bm,2).'%</th>
    <th>'.angka($susut).'</th>
    <th>'.angka($total_pakai).'</th>
    <th>'.round($total_susut,2).'%</th>
    <th colspan="4"></th>
  </tr>
</table>

</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html);
$dompdf->render();

$namaFile = "Laporan_Pemakaian_AT_{$tahun}-" . sprintf("%02d",$bulan) . ".pdf";
$dompdf->stream($namaFile, ["Attachment" => true]);
exit;
