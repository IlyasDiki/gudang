<?php
require '../config/init.php';

/* =========================
   LIST BARANG (Kelompok AT)
========================= */
$qBarang = mysqli_query($conn,"
    SELECT id_barang, kode_barang, nama_barang
    FROM barang
    WHERE id_kelompok = '3'
    ORDER BY nama_barang ASC
");

/* =========================
   DATA TERAKHIR
========================= */
$qLast = mysqli_query($conn,"
    SELECT s.*, b.nama_barang 
    FROM stok_fisik_at s
    JOIN barang b ON s.id_barang = b.id_barang
    ORDER BY s.tanggal DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Produksi</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
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
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php
    $page = 'stok_fisik_at';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>  

  <!-- Content -->
  <div class="content-wrapper p-3">
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        Data produksi berhasil Ditambahkan
      </div>
    <?php endif; ?>

    <section class="content-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1>Stok Fisik AT</h1>
      </div>

      <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
        + Input Stok Fisik
      </button>
    </div>
  </section>

  <section class="content">

    <!-- FILTER BULAN -->
    <div class="card">
<div class="card-body">

<form method="POST" action="stok_fisik_at_simpan.php">

<div class="row">

    <div class="col-md-4">
        <label>Tanggal</label>
        <input type="date" name="tanggal" 
               class="form-control" 
               value="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="col-md-4">
        <label>Barang</label>
        <select name="id_barang" class="form-control" required>
            <option value="">-- Pilih Barang --</option>
            <?php while($b = mysqli_fetch_assoc($qBarang)): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= htmlspecialchars($b['kode_barang']." - ".$b['nama_barang']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label>Jumlah Fisik Habis (Kg)</label>
        <input type="number" step="0.01" 
               name="jumlah" 
               class="form-control" required>
    </div>

</div>

<div class="mt-3">
    <label>Keterangan</label>
    <textarea name="keterangan" 
              class="form-control" 
              rows="2"></textarea>
</div>

<div class="mt-4">
    <button type="submit" name="simpan" class="btn btn-success">
        <i class="fa fa-save"></i> Simpan
    </button>
    <a href="../index.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Kembali
    </a>
</div>

</form>

<hr>

<h5>Data Terakhir</h5>

<table class="table table-bordered table-sm">
<thead class="bg-light">
<tr>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Jumlah (Kg)</th>
    <th>Keterangan</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($qLast)==0): ?>
<tr>
    <td colspan="4" class="text-center">Belum ada data</td>
</tr>
<?php else: ?>
<?php while($r = mysqli_fetch_assoc($qLast)): ?>
<tr>
    <td><?= date('d-M-Y', strtotime($r['tanggal'])) ?></td>
    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= number_format($r['jumlah'],2) ?></td>
    <td><?= htmlspecialchars($r['keterangan']) ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

  </section>
</div>

<?php include "../footer.php"; ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

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
<script>
  $('#modalDetail').on('show.bs.modal', function (e) {
    var id = $(e.relatedTarget).data('id');
    $('#isiDetail').load('transaksi_detail.php?id=' + id);
  });
</script>
</body>
</html>
