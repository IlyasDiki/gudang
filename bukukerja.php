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
      <h1>Master Data Barang</h1>
    </section>

    <section class="content">

      <div class="card">
        <div class="card-header">
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
            <i class="fas fa-plus"></i> Tambah Barang
          </button>
        </div>

        <div class="card-body table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th rowspan="3">NO</th>
                <th rowspan="3">TGL<br>TERIMA</th>
                <th colspan="14">PEMAKAIAN AT</th>
                <th colspan="4">PEMAKAIAN PRODUKSI</th>
              </tr>

              <!-- BARIS HEADER 2 -->
              <tr>
                <th rowspan="2">JUMLAH</th>
                <th rowspan="2">TGL</th>
                <th rowspan="2">SORTIR</th>
                <th rowspan="2">SALDO</th>
                <th rowspan="2">MA</th>
                <th rowspan="2">AA</th>
                <th rowspan="2">B. MENTAH</th>
                <th rowspan="2">AIR</th>
                <th rowspan="2">ATP</th>
                <th rowspan="2">KAND.<br>MA</th>
                <th rowspan="2">KAND.<br>AA</th>
                <th rowspan="2">KAND.<br>BM</th>
                <th rowspan="2">SUSUT<br>TIMB.</th>
                <th rowspan="2">TOTAL<br>SUSUT</th>

                <th rowspan="2">JUMLAH</th>
                <th rowspan="2">TGL</th>
                <th rowspan="2">MIXER</th>
                <th rowspan="2">SALDO</th>
              </tr>
            </thead>
            <tbody>
<?php
$no = 1;

foreach($data as $tglTerima => $rows){

  $rowspan = count($rows);

  // hitung total
  $totalMA = $totalAA = $totalBM = $totalAir = $totalATP = 0;

  foreach($rows as $r){
    $totalMA += $r['ma'];
    $totalAA += $r['aa'];
    $totalBM += $r['bm'];
    $totalAir += $r['air'];
    $totalATP += $r['atp'];
  }

  $first = true;

  foreach($rows as $r){
    echo "<tr>";

    if($first){
      echo "<td rowspan='$rowspan'>$no</td>";
      echo "<td rowspan='$rowspan'>$tglTerima</td>";
      echo "<td rowspan='$rowspan'>500</td>"; // JUMLAH
    }

    echo "<td>{$r['tgl']}</td>";
    echo "<td></td>"; // sortir
    echo "<td>{$r['saldo']}</td>";
    echo "<td>{$r['ma']}</td>";
    echo "<td>{$r['aa']}</td>";
    echo "<td>{$r['bm']}</td>";
    echo "<td>{$r['air']}</td>";
    echo "<td>{$r['atp']}</td>";

    if($first){
      echo "<td rowspan='$rowspan'>0%</td>";
      echo "<td rowspan='$rowspan'>2%</td>";
      echo "<td rowspan='$rowspan'>1%</td>";
      echo "<td rowspan='$rowspan'>100%</td>";
      echo "<td rowspan='$rowspan'>99%</td>";
      $first = false;
    }

    echo "</tr>";
  }

  // BARIS TOTAL (SEPERTI DI EXCEL)
  echo "<tr style='font-weight:bold;background:#f9fafb'>";
  echo "<td colspan='6'>TOTAL</td>";
  echo "<td>$totalMA</td>";
  echo "<td>$totalAA</td>";
  echo "<td>$totalBM</td>";
  echo "<td>$totalAir</td>";
  echo "<td>$totalATP</td>";
  echo "<td colspan='5'></td>";
  echo "</tr>";

  $no++;
}
?>

            </tbody>
          </table>
        </div>

      </div>

    </section>
  </div>

</div>

<!-- MODAL TAMBAH BARANG -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="barang_simpan.php">

        <div class="modal-header">
          <h5 class="modal-title">Tambah Barang</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Kode Barang</label>
            <input type="text" name="kode_barang" class="form-control">
          </div>

          <div class="form-group">
            <label>Kelompok Bahan</label>
            <select name="kelompok" class="form-control">
              <option>Bahan Baku</option>
              <option>Produk Jadi</option>
              <option>Bahan Penunjang</option>
            </select>
          </div>

          <div class="form-group">
            <label>Satuan Utama</label>
            <select name="satuan" class="form-control">
              <option>Kg</option>
              <option>Ton</option>
              <option>Zak</option>
            </select>
          </div>

          <hr>

          <h6>Konversi (Opsional)</h6>
          <div class="row">
            <div class="col-md-6">
              <input type="number" step="0.01" name="faktor_konversi" class="form-control" placeholder="Faktor Konversi">
            </div>
            <div class="col-md-6">
              <input type="text" name="satuan_konversi" class="form-control" placeholder="Satuan Konversi">
            </div>
          </div>

          <div class="form-group mt-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control"></textarea>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>

      </form>
    </div>
  </div>
  </div>

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
