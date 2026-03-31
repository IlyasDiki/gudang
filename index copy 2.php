<?php
require_once __DIR__ . '/config/init.php';

$bulan = $_GET['bulan'] ?? date('Y-m');
$awal  = $bulan . '-01';
$akhir = date('Y-m-t', strtotime($awal));

$q_total = mysqli_query($conn, "
  SELECT COUNT(*) AS total 
  FROM barang 
  WHERE aktif = 1
");
$total_barang = mysqli_fetch_assoc($q_total)['total'];

$qMasuk7Hari = mysqli_query($conn, "
  SELECT IFNULL(SUM(md.jumlah),0) AS total
  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  WHERE m.arah = 'MASUK'
  AND m.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$masuk7Hari = mysqli_fetch_assoc($qMasuk7Hari)['total'];

$qKeluar7Hari = mysqli_query($conn, "
  SELECT IFNULL(SUM(md.jumlah),0) AS total
  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  WHERE m.arah = 'KELUAR'
  AND m.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$keluar7Hari = mysqli_fetch_assoc($qKeluar7Hari)['total'];

$qStokKritis = mysqli_query($conn, "
SELECT COUNT(*) total FROM (
    SELECT 
        b.id_barang,
        COALESCE(SUM(
            CASE 
                WHEN jm.nama_jenis = 'MASUK' THEN md.jumlah
                WHEN jm.nama_jenis = 'KELUAR' THEN -md.jumlah
                ELSE 0
            END
        ),0) AS saldo,
        b.stok_minimum
    FROM barang b
    LEFT JOIN mutasi_detail md 
        ON md.id_barang = b.id_barang
    LEFT JOIN mutasi m 
        ON m.id_mutasi = md.id_mutasi
    LEFT JOIN jenis_mutasi jm 
        ON jm.id_jenis = m.id_jenis
    GROUP BY b.id_barang, b.stok_minimum
    HAVING saldo <= b.stok_minimum
) x
");
$stokKritis = mysqli_fetch_assoc($qStokKritis)['total'];

$sql = "
SELECT 
  kb.nama_kelompok,
  b.nama_barang,

  SUM(CASE WHEN m.arah = 'MASUK' THEN md.jumlah ELSE 0 END) AS masuk,
  SUM(CASE WHEN m.arah = 'KELUAR' THEN md.jumlah ELSE 0 END) AS keluar,
  MAX(m.tanggal) AS terakhir

FROM mutasi m
JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
JOIN barang b ON b.id_barang = md.id_barang
JOIN kelompok_barang kb ON kb.id_kelompok = b.id_kelompok

WHERE m.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

GROUP BY b.id_barang
ORDER BY terakhir DESC
LIMIT 10
";
$data = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Dashboard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style --> 
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker --> 
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php
  include "layout/navbar.php";
  include "layout/sidebar.php";
  ?>

  <?php
  $hari = date('l');
  $tanggal = date('d F Y');

  $hari_id = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
  ];
  ?>
  <!-- CONTENT -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <h1>Dashboard</h1>
        <small class="text-muted">
          <?= $hari_id[$hari] ?>, <?= $tanggal ?>
        </small>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <!-- INFO BOX -->
        <div class="row">

          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= number_format($total_barang) ?></h3>
                <p>Total Item</p>
              </div>
              <div class="icon">
                <i class="fas fa-boxes"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($masuk7Hari) ?></h3>
                <p>Barang Masuk 7 Hari Terakhir</p>
              </div>
              <div class="icon">
                <i class="fas fa-arrow-down"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= number_format($keluar7Hari) ?></h3>
                <p>Barang Keluar 7 Hari Terakhir</p>
              </div>
              <div class="icon">
                <i class="fas fa-arrow-up"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3><?= number_format($stokKritis) ?></h3>
                <p>Stok Hampir Habis</p>
              </div>
              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
            </div>
          </div>

        </div>

        <!-- TABLE -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Aktivitas Terakhir</h3>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="bg-light">
              <tr>
                <th>Kelompok</th>
                <th>Barang</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Selisih</th>
              </tr>
              </thead>
                  <tbody>
                  <?php while($r = mysqli_fetch_assoc($data)): 
                    $selisih = $r['masuk'] - $r['keluar'];
                  ?>
                  <tr>
                    <td><?= $r['nama_kelompok'] ?></td>
                    <td><?= $r['nama_barang'] ?></td>
                    <td class="text-right"><?= number_format($r['masuk'],2) ?></td>
                    <td class="text-right"><?= number_format($r['keluar'],2) ?></td>
                    <td class="text-right font-weight-bold">
                      <?= number_format($selisih,2) ?>
                    </td>
                  </tr>
                  <?php endwhile ?>
                  </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php
  include "footer.php";
  ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
</body>
</html>
