<?php
require '../config/init.php';

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

// ================================
// QUERY LAPORAN
// ================================
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
// HEADER EXCEL
// ================================
$filename = "Kartu_Stok_$bulan-$tahun.xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // biar utf-8 aman di excel
?>

<table border="1">
  <tr>
    <th colspan="8" style="font-size:16px;">KARTU STOK BULANAN</th>
  </tr>
  <tr>
    <th colspan="8">Periode: <?= $judulBulan ?></th>
  </tr>

  <tr>
    <th>No</th>
    <th>Kelompok</th>
    <th>Nama Barang</th>
    <th>Stok Awal</th>
    <th>Masuk</th>
    <th>Keluar</th>
    <th>Stok Akhir</th>
    <th>Satuan</th>
  </tr>

  <?php
  $no = 1;
  $totalAwal = 0;
  $totalMasuk = 0;
  $totalKeluar = 0;
  $totalAkhir = 0;

  while($r = mysqli_fetch_assoc($q)){
    $stokAkhir = $r['stok_awal'] + $r['masuk'] - $r['keluar'];

    echo "<tr>";
    echo "<td>".$no++."</td>";
    echo "<td>".$r['nama_kelompok']."</td>";
    echo "<td>".$r['nama_barang']."</td>";
    echo "<td>".number_format($r['stok_awal'],2,'.','')."</td>";
    echo "<td>".number_format($r['masuk'],2,'.','')."</td>";
    echo "<td>".number_format($r['keluar'],2,'.','')."</td>";
    echo "<td>".number_format($stokAkhir,2,'.','')."</td>";
    echo "<td>".$r['satuan']."</td>";
    echo "</tr>";

    $totalAwal += $r['stok_awal'];
    $totalMasuk += $r['masuk'];
    $totalKeluar += $r['keluar'];
    $totalAkhir += $stokAkhir;
  }
  ?>

  <tr>
    <th colspan="3">TOTAL</th>
    <th><?= number_format($totalAwal,2,'.','') ?></th>
    <th><?= number_format($totalMasuk,2,'.','') ?></th>
    <th><?= number_format($totalKeluar,2,'.','') ?></th>
    <th><?= number_format($totalAkhir,2,'.','') ?></th>
    <th></th>
  </tr>
</table>