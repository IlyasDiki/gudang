<?php
require '../config/init.php';

require_once "../dompdf/autoload.inc.php";
use Dompdf\Dompdf;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

$tglAwal  = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

$namaBulan = [
  "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
  "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
  "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];
$judulBulan = ($namaBulan[$bulan] ?? $bulan) . " " . $tahun;

$q = mysqli_query($conn, "
  SELECT 
    kb.nama_kelompok,
    b.nama_barang,
    b.satuan,

    IFNULL(SUM(
      CASE 
        WHEN m.tanggal < '$tglAwal' AND m.arah = 'MASUK' THEN md.jumlah
        WHEN m.tanggal < '$tglAwal' AND m.arah = 'KELUAR' THEN -md.jumlah
        ELSE 0
      END
    ),0) AS stok_awal,

    IFNULL(SUM(
      CASE 
        WHEN m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
         AND m.arah = 'MASUK'
        THEN md.jumlah
        ELSE 0
      END
    ),0) AS masuk,

    IFNULL(SUM(
      CASE 
        WHEN m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
         AND m.arah = 'KELUAR'
        THEN md.jumlah
        ELSE 0
      END
    ),0) AS keluar

  FROM barang b
  JOIN kelompok_barang kb ON kb.id_kelompok = b.id_kelompok
  LEFT JOIN mutasi_detail md ON md.id_barang = b.id_barang
  LEFT JOIN mutasi m ON m.id_mutasi = md.id_mutasi

  GROUP BY b.id_barang
  ORDER BY kb.nama_kelompok ASC, b.nama_barang ASC
");

// ================================
// BUAT HTML UNTUK PDF
// ================================
$html = "
<html>
<head>
<style>
  body { font-family: Arial, sans-serif; font-size: 11px; }
  h2 { text-align:center; margin:0; }
  .periode { text-align:center; margin-bottom:10px; }
  table { width:100%; border-collapse: collapse; }
  th, td { border:1px solid #000; padding:4px; }
  th { background:#eee; }
  .angka { text-align:right; }
</style>
</head>
<body>

<h2>KARTU STOK BULANAN</h2>
<div class='periode'>Periode: <b>$judulBulan</b></div>

<table>
<thead>
<tr>
  <th style='width:30px;'>No</th>
  <th>Kelompok</th>
  <th>Nama Barang</th>
  <th>Stok Awal</th>
  <th>Masuk</th>
  <th>Keluar</th>
  <th>Stok Akhir</th>
  <th>Satuan</th>
</tr>
</thead>
<tbody>
";

$no = 1;
$totalAwal = 0;
$totalMasuk = 0;
$totalKeluar = 0;
$totalAkhir = 0;

while($r = mysqli_fetch_assoc($q)){
  $stokAkhir = $r['stok_awal'] + $r['masuk'] - $r['keluar'];

  $html .= "
  <tr>
    <td>".$no++."</td>
    <td>".$r['nama_kelompok']."</td>
    <td>".$r['nama_barang']."</td>
    <td class='angka'>".number_format($r['stok_awal'],2)."</td>
    <td class='angka'>".number_format($r['masuk'],2)."</td>
    <td class='angka'>".number_format($r['keluar'],2)."</td>
    <td class='angka'>".number_format($stokAkhir,2)."</td>
    <td>".$r['satuan']."</td>
  </tr>
  ";

  $totalAwal += $r['stok_awal'];
  $totalMasuk += $r['masuk'];
  $totalKeluar += $r['keluar'];
  $totalAkhir += $stokAkhir;
}

$html .= "
</tbody>

<tfoot>
<tr>
  <th colspan='3'>TOTAL</th>
  <th class='angka'>".number_format($totalAwal,2)."</th>
  <th class='angka'>".number_format($totalMasuk,2)."</th>
  <th class='angka'>".number_format($totalKeluar,2)."</th>
  <th class='angka'>".number_format($totalAkhir,2)."</th>
  <th></th>
</tr>
</tfoot>

</table>
</body>
</html>
";

// ================================
// GENERATE PDF
// ================================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "Kartu_Stok_$bulan-$tahun.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;