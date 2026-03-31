<?php
require_once __DIR__ . '/../config/init.php';
$supplier = mysqli_query($conn, "SELECT * FROM supplier");
$barang   = mysqli_query($conn, "SELECT * FROM barang");
$data = mysqli_query($conn, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Supplier</title>
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
    $page = 'supplier';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>

  <!-- Content -->
  <div class="content-wrapper p-3">
    <?php if (isset($_GET['edit'])): ?>
    <div class="alert alert-success">
      Data supplier berhasil diperbarui
    </div>
    <?php endif; ?>
    <section class="content-header">
      <h1>Master</h1>
    </section>

    <section class="content">

      <div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Supplier</h3>
      <button class="btn btn-primary mb-2 float-right" data-toggle="modal" data-target="#modalSupplier">
      + Tambah Supplier
    </button>
  </div>
  <div class="card-body">
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Supplier</th>
      <th>Alamat</th>
      <th>Telepon</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php $no=1; while($row = mysqli_fetch_assoc($supplier)): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= $row['nama_supplier'] ?></td>
      <td><?= $row['alamat'] ?></td>
      <td><?= $row['telepon'] ?></td>
      <td>
        <button class="btn btn-warning btn-sm"
          data-toggle="modal"
          data-target="#modalEditSupplier"
          data-id="<?= $row['id_supplier'] ?>"
          data-nama="<?= htmlspecialchars($row['nama_supplier']) ?>"
          data-alamat="<?= htmlspecialchars($row['alamat']) ?>"
          data-telepon="<?= htmlspecialchars($row['telepon']) ?>">
          <i class="fa fa-edit"></i> Edit
        </button>
        <a href="supplier_hapus.php?id=<?= $row['id_supplier'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Hapus data?')">Hapus</a>
      </td>
    </tr>
    <?php endwhile ?>
  </tbody>
</table>
  </div>
</div>
    </section>
  </div>

</div>
<!-- MODAL INPUT PEMBELIAN -->
<div class="modal fade" id="modalSupplier">
  <div class="modal-dialog">
    <form method="post" action="supplier_simpan.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Tambah Supplier</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Nama Supplier</label>
            <input type="text" name="nama_supplier" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Telepon</label>
            <input type="number" name="telepon" class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>

      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT Supplier -->  
<div class="modal fade" id="modalEditSupplier">
  <div class="modal-dialog">
    <form method="post" action="supplier_update.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Edit Supplier</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <input type="hidden" name="id_supplier" id="edit_id">

          <div class="form-group">
            <label>Nama Supplier</label>
            <input type="text" name="nama_supplier" id="edit_nama"
              class="form-control" required>
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" id="edit_alamat"
              class="form-control"></textarea>
          </div>

          <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon" id="edit_telepon"
              class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> Simpan
          </button>
        </div>

      </div>
    </form>
  </div>
</div>
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
$('#modalEditSupplier').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget)

  $('#edit_id').val(button.data('id'))
  $('#edit_nama').val(button.data('nama'))
  $('#edit_alamat').val(button.data('alamat'))
  $('#edit_telepon').val(button.data('telepon'))
})
</script>
</body>
</html>
