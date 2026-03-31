<?php
require '../config/init.php';

// ================================
// FILTER BULAN & TAHUN
// ================================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

$tglAwal  = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

// ================================
// QUERY LAPORAN
// ================================
$q = mysqli_query($conn, "
  SELECT 
    kb.id_kelompok,
    kb.nama_kelompok,
    b.id_barang,
    b.nama_barang,
    b.satuan,

    -- stok awal = saldo sebelum bulan ini
    IFNULL(SUM(
      CASE 
        WHEN m.tanggal < '$tglAwal' AND m.arah = 'MASUK' THEN md.jumlah
        WHEN m.tanggal < '$tglAwal' AND m.arah = 'KELUAR' THEN -md.jumlah
        ELSE 0
      END
    ),0) AS stok_awal,

    -- masuk bulan ini
    IFNULL(SUM(
      CASE 
        WHEN m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
         AND m.arah = 'MASUK'
        THEN md.jumlah
        ELSE 0
      END
    ),0) AS masuk,

    -- keluar bulan ini
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
// UNTUK LABEL BULAN
// ================================
$namaBulan = [
  "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
  "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
  "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

$judulBulan = ($namaBulan[$bulan] ?? $bulan) . " " . $tahun;
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kartu Stok Bulanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

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
    body { background:#f5f5f5; }
    .card { border-radius:12px; }
    table th, table td { vertical-align: middle !important; }
    .header-kelompok td {
      background: #f0f0f0 !important;
      font-weight: bold;
    }
    .angka { text-align:right; }
  </style>
</head>

<body>

<div class="container mt-4 mb-5">

  <div class="card shadow-sm">
    <div class="card-body">
<a href="../index.php" 
            class="btn btn-info btn-sm">Back</a>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h4 class="mb-0">Kartu Stok Bulanan</h4>
          <small class="text-muted">Periode: <b><?= $judulBulan ?></b></small>
        </div>
      </div>

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

    <a href="kartu_stok_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-success mr-2">
      Export Excel
    </a>

    <a href="kartu_stok_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-danger">
      Export PDF
    </a>

  </div>

</form>


      <!-- TABEL -->
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
          <thead class="table-dark">
            <tr>
              <th style="width:40px;">No</th>
              <th>Nama Barang</th>
              <th style="width:130px;" class="angka">Stok Awal</th>
              <th style="width:110px;" class="angka">Masuk</th>
              <th style="width:110px;" class="angka">Keluar</th>
              <th style="width:130px;" class="angka">Stok Akhir</th>
              <th style="width:80px;">Satuan</th>
              <th style="width:160px;">Keterangan</th>
            </tr>
          </thead>
          <tbody>

          <?php
          $no = 1;
          $kelompokSebelumnya = null;

          // total keseluruhan (opsional)
          $totalAwal = 0;
          $totalMasuk = 0;
          $totalKeluar = 0;
          $totalAkhir = 0;

          while($r = mysqli_fetch_assoc($q)){

            $stokAkhir = $r['stok_awal'] + $r['masuk'] - $r['keluar'];

            // header kelompok
            if($kelompokSebelumnya != $r['nama_kelompok']){
              echo "
                <tr class='header-kelompok'>
                  <td colspan='8'>".$r['nama_kelompok']."</td>
                </tr>
              ";
              $kelompokSebelumnya = $r['nama_kelompok'];
              $no = 1; // reset nomor per kelompok (kalau mau global, hapus ini)
            }

            // tampilkan row barang
            echo "
              <tr>
                <td>".$no++."</td>
                <td>".$r['nama_barang']."</td>
                <td class='angka'>".number_format($r['stok_awal'],2)."</td>
                <td class='angka'>".number_format($r['masuk'],2)."</td>
                <td class='angka'>".number_format($r['keluar'],2)."</td>
                <td class='angka'>".number_format($stokAkhir,2)."</td>
                <td>".$r['satuan']."</td>
                <td></td>
              </tr>
            ";

            $totalAwal  += $r['stok_awal'];
            $totalMasuk += $r['masuk'];
            $totalKeluar+= $r['keluar'];
            $totalAkhir += $stokAkhir;
          }
          ?>

          </tbody>

          <!-- TOTAL KESELURUHAN -->
          <tfoot>
            <tr class="table-secondary fw-bold">
              <td colspan="2" class="text-end">TOTAL</td>
              <td class="angka"><?= number_format($totalAwal,2) ?></td>
              <td class="angka"><?= number_format($totalMasuk,2) ?></td>
              <td class="angka"><?= number_format($totalKeluar,2) ?></td>
              <td class="angka"><?= number_format($totalAkhir,2) ?></td>
              <td colspan="2"></td>
            </tr>
          </tfoot>

        </table>
      </div>

      <div class="mt-2">
        <small class="text-muted">
          Catatan: Stok Awal dihitung dari saldo mutasi sebelum tanggal <b><?= $tglAwal ?></b>.
        </small>
      </div>

    </div>
  </div>

</div>
<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="../dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../dist/js/demo.js"></script>
</body>

</html>
