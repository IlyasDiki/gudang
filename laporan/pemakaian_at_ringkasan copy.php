<?php
require '../config/init.php';

function angka($n){
    return rtrim(rtrim(number_format((float)$n,2,'.',''), '0'),'.');
}

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$tglAwal  = $tahun.'-'.$bulan.'-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));

/* =========================
   AMBIL STOK MASUK AT
   ========================= */
$qMasuk = mysqli_query($conn,"
    SELECT m.id_mutasi, m.tanggal, md.jumlah
    FROM mutasi m
    JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
    WHERE m.id_jenis = 5
    AND md.id_barang = 2
    AND DATE(m.tanggal) <= '$tglAkhir'
    ORDER BY m.tanggal ASC
");

$dataMasuk = [];
while($row = mysqli_fetch_assoc($qMasuk)){
    $row['jumlah'] = (float)$row['jumlah'];
    $dataMasuk[] = $row;
}

/* =========================
   TOTAL PEMAKAIAN AT
   ========================= */
$qPakai = mysqli_query($conn,"
    SELECT SUM(sortir) as total_sortir
    FROM at_detail
    WHERE DATE(tanggal) <= '$tglAkhir'
");

$rowPakai = mysqli_fetch_assoc($qPakai);
$totalSortir = (float)$rowPakai['total_sortir'];

/* =========================
   HITUNG FIFO SALDO
   ========================= */
$sisaPakai = $totalSortir;

$ready = [];
$habis = [];

foreach($dataMasuk as $d){

    if($sisaPakai > 0){
        if($d['jumlah'] <= $sisaPakai){
            $sisa = 0;
            $sisaPakai -= $d['jumlah'];
        } else {
            $sisa = $d['jumlah'] - $sisaPakai;
            $sisaPakai = 0;
        }
    } else {
        $sisa = $d['jumlah'];
    }

    $row = [
        'tanggal' => $d['tanggal'],
        'stok'    => $d['jumlah'],
        'sisa'    => $sisa
    ];

    if($sisa > 0){
        $ready[] = $row;
    } else {
        $habis[] = $row;
    }
}

$totalReady  = array_sum(array_column($ready,'sisa'));
$totalHabis  = array_sum(array_column($habis,'stok'));
$totalSemua  = $totalReady + $totalHabis;

$namaBulan = [
"01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
"05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
"09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Ringkasan Bahan Baku AT</title>
  <!-- kalau kamu sudah pakai bootstrap di template utama, ini bisa dihapus -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style --> 
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker --> 
  <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
<style>
body{
    font-family:Calibri, Arial;
    font-size:13px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}
.header-top{
    font-weight:bold;
}
.judul{
    background:#f4e3d7;
    font-size:18px;
    font-weight:bold;
}
.section{
    background:#d9ead3;
    font-weight:bold;
}
.total-row{
    background:#f2f2f2;
    font-weight:bold;
}
.card {
  border-radius: 10px;
}

table {
  font-size: 13px;
}

thead th {
  background: #1f2937;
  color: white;
  text-align: center;
}

tbody tr.group-row {
  background: #e9ecef;
  font-weight: bold;
}

tfoot {
  background: #d1d5db;
  font-weight: bold;
}
</style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

<div class="content-wrapper" style="margin-left:0;">
  <div class="content pt-4">
    <div class="container">

      <div class="card shadow-lg">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
  <a href="../index.php" class="btn btn-info btn-sm">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

<h4 class="mb-1"><b>Ringkasan Pemakaian AT</b></h4>
<small class="text-muted">
  Periode: <b><?= $namaBulan[$bulan] . ' ' . $tahun ?></b>
</small>

<hr>

<form method="GET" class="row g-2 mb-4">

  <div class="col-md-3">
    <label>Bulan</label>
    <select name="bulan" class="form-control">
      <?php foreach($namaBulan as $k => $v): ?>
        <option value="<?= $k ?>" <?= ($k==$bulan?'selected':'') ?>>
          <?= $v ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label>Tahun</label>
    <select name="tahun" class="form-control">
      <?php
        $thnNow = date('Y');
        for($t=$thnNow-3;$t<=$thnNow+1;$t++):
      ?>
        <option value="<?= $t ?>" <?= ($t==$tahun?'selected':'') ?>>
          <?= $t ?>
        </option>
      <?php endfor; ?>
    </select>
  </div>

  <div class="col-md-6 d-flex align-items-end gap-2">

    <button type="submit" class="btn btn-primary mr-2">
      Tampilkan
    </button>

    <a href="export_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-success mr-2">
      Export Excel
    </a>

    <a href="export_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-danger">
      Export PDF
    </a>

  </div>

</form>

<table>

<tr>
    <td colspan="6" class="judul">
        BAHAN BAKU AT
    </td>
</tr>

<tr>
    <th>NO</th>
    <th>SUPPLIER</th>
    <th>TGL TERIMA</th>
    <th>STOK (ASALAN)</th>
    <th>SISA POWDER</th>
    <th>KET</th>
</tr>

<tr class="section">
    <td colspan="6">STOK AT READY</td>
</tr>

<?php $no=1; foreach($ready as $r): ?>
<tr>
    <td><?= $no++ ?></td>
    <td>-</td>
    <td><?= $r['tanggal'] ?></td>
    <td><?= angka($r['stok']) ?> Kg</td>
    <td><?= angka($r['sisa']) ?> Kg</td>
    <td>READY</td>
</tr>
<?php endforeach; ?>

<tr class="total-row">
    <td colspan="3">JUMLAH</td>
    <td>-</td>
    <td><?= angka($totalReady) ?> Kg</td>
    <td></td>
</tr>

<tr class="section">
    <td colspan="6">STOK AT FISIK HABIS</td>
</tr>

<?php foreach($habis as $h): ?>
<tr>
    <td><?= $no++ ?></td>
    <td>-</td>
    <td><?= $h['tanggal'] ?></td>
    <td><?= angka($h['stok']) ?> Kg</td>
    <td>0 Kg</td>
    <td>HABIS</td>
</tr>
<?php endforeach; ?>

<tr class="total-row">
    <td colspan="3">JUMLAH</td>
    <td><?= angka($totalHabis) ?> Kg</td>
    <td>-</td>
    <td></td>
</tr>

<tr class="judul">
    <td colspan="3">TOTAL</td>
    <td><?= angka($totalSemua) ?> Kg</td>
    <td><?= angka($totalReady) ?> Kg</td>
    <td></td>
</tr>

</table>

      </div>
      </div>

    </div>
  </div>
</div>

</div>
</body>
</html>