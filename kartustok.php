<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 3 | Dashboard</title>
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
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php
  include "navbar.php";
  include "sidebar.php";
  ?>

     <!-- Content -->
  <div class="content-wrapper p-3">

    <section class="content-header">
      <h1>Laporan Kartu Stok</h1>
      <small>Riwayat pergerakan stok barang</small>
    </section>

    <section class="content">

      <!-- FILTER -->
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title">Filter Data</h3>
        </div>

        <form method="get">
          <div class="card-body">

            <div class="row">
              <div class="col-md-4">
                <label>Barang</label>
                <select name="barang_id" class="form-control">
                  <option value="">-- Semua Barang --</option>
                  <option value="1">AT Powder</option>
                  <option value="2">Briket C26</option>
                </select>
              </div>

              <div class="col-md-3">
                <label>Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control">
              </div>

              <div class="col-md-3">
                <label>Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control">
              </div>

              <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-info w-100">
                  <i class="fas fa-search"></i> Tampilkan
                </button>
              </div>
            </div>

          </div>
        </form>
      </div>

      <!-- TABEL KARTU STOK -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Kartu Stok</h3>
        </div>

        <div class="card-body table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Saldo</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <!-- Contoh data -->
              <tr>
                <td>2026-01-01</td>
                <td>AT Powder</td>
                <td>500</td>
                <td>0</td>
                <td>500</td>
                <td>Stok Awal</td>
              </tr>
              <tr>
                <td>2026-01-02</td>
                <td>AT Powder</td>
                <td>0</td>
                <td>200</td>
                <td>300</td>
                <td>Produksi</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-footer">
          <button class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
          </button>
        </div>

      </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.0.5
    </div>
  </footer>

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
