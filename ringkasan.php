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
  <style>
table{
  border-collapse:collapse;
  width:100%;
  font-size:12px;
}
th,td{
  border:1px solid #000;
  padding:4px;
  text-align:center;
}
.header{
  background:#fde9d9;
  font-weight:bold;
}
.section-ready{
  background:#d9ead3;
  font-weight:bold;
}
.section-habis{
  background:#fce4d6;
  font-weight:bold;
}
.total{
  background:#ddebf7;
  font-weight:bold;
}
.left{text-align:left}
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php
  include "navbar.php";
  include "sidebar.php";
  ?>

  <!-- Content -->
  <h3>RINGKASAN</h3>
<p>PER : <?= date('d-M') ?></p>

<table>
<tr class="header">
  <th>NO</th>
  <th>SUPPLIER</th>
  <th>TGL TERIMA</th>
  <th>STOK (ASALAN)</th>
  <th>SISA POWDER</th>
  <th>KET</th>
</tr>

<!-- STOK AT READY -->
<tr class="section-ready">
  <td colspan="6">STOK AT READY</td>
</tr>

<?php
$no=1;
$totalReadyStok=0;
$totalReadySisa=0;
while($r = $ready->fetch_assoc()):
$totalReadyStok += $r['stok_asalan'];
$totalReadySisa += $r['sisa_powder'];
?>
<tr>
  <td><?= $no++ ?></td>
  <td class="left"><?= $r['supplier'] ?></td>
  <td><?= $r['tgl_terima'] ?></td>
  <td><?= number_format($r['stok_asalan'],2) ?> Kg</td>
  <td><?= number_format($r['sisa_powder'],2) ?> Kg</td>
  <td><?= $r['ket'] ?></td>
</tr>
<?php endwhile; ?>

<tr class="section-ready">
  <td colspan="3">JUMLAH</td>
  <td><?= number_format($totalReadyStok,2) ?> Kg</td>
  <td><?= number_format($totalReadySisa,2) ?> Kg</td>
  <td></td>
</tr>

<!-- STOK AT FISIK HABIS -->
<tr class="section-habis">
  <td colspan="6">STOK AT FISIK HABIS</td>
</tr>

<?php
$no=1;
$totalHabisStok=0;
$totalHabisSisa=0;
while($r = $habis->fetch_assoc()):
$totalHabisStok += $r['stok_asalan'];
$totalHabisSisa += $r['sisa_powder'];
?>
<tr>
  <td><?= $no++ ?></td>
  <td class="left"><?= $r['supplier'] ?></td>
  <td><?= $r['tgl_terima'] ?></td>
  <td><?= number_format($r['stok_asalan'],2) ?> Kg</td>
  <td><?= number_format($r['sisa_powder'],2) ?> Kg</td>
  <td><?= $r['ket'] ?></td>
</tr>
<?php endwhile; ?>

<tr class="section-habis">
  <td colspan="3">JUMLAH</td>
  <td><?= number_format($totalHabisStok,2) ?> Kg</td>
  <td><?= number_format($totalHabisSisa,2) ?> Kg</td>
  <td></td>
</tr>

<!-- TOTAL -->
<tr class="total">
  <td colspan="3">TOTAL</td>
  <td><?= number_format($totalReadyStok+$totalHabisStok,2) ?> Kg</td>
  <td><?= number_format($totalReadySisa+$totalHabisSisa,2) ?> Kg</td>
  <td></td>
</tr>

<!-- TAMBAHAN -->
<tr>
  <td>A</td>
  <td colspan="3" class="left">TAPIOKA (SPM)</td>
  <td>0 Kg</td>
  <td></td>
</tr>
<tr>
  <td>B</td>
  <td colspan="3" class="left">SAGU AREN</td>
  <td>0 Kg</td>
  <td></td>
</tr>
<tr>
  <td>C</td>
  <td colspan="3" class="left">Gas 12 Kg</td>
  <td>0 Tab</td>
  <td></td>
</tr>

</table>
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
