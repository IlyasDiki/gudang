<?php
require_once __DIR__ . '/../config/init.php';

$data = mysqli_query($conn, "
  SELECT kb.*, p.nama_kelompok AS nama_parent
  FROM kelompok_barang kb
  LEFT JOIN kelompok_barang p ON kb.parent_id = p.id_kelompok
  ORDER BY kb.kode_kelompok ASC
");

$parent = mysqli_query($conn, "
  SELECT * FROM kelompok_barang
  ORDER BY kode_kelompok
");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Kelompok Barang</title>
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
    $page = 'kelompok_barang';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>

  <!-- Content -->
  <div class="content-wrapper p-3">
        <section class="content-header">
      <h1>Master</h1>
    </section>
    <?php if (isset($_GET['edit'])): ?>
    <div class="alert alert-success">
      Data Kelompok berhasil diperbarui
    </div>
    <?php endif; ?>
    <section class="content">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Kelompok Barang</h3>
      <button class="btn btn-primary float-right" data-toggle="modal" data-target="#modalTambah">
        + Tambah Kelompok
      </button>
    </div>

    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Kelompok</th>
            <th>Tipe Kelompok</th>
            <th>Parent</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['kode_kelompok']; ?></td>
            <td><?= $row['nama_kelompok']; ?></td>
            <td><?= $row['tipe_kelompok'] ? str_replace('_',' ', $row['tipe_kelompok']) : '-'; ?></td>
            <td><?= $row['nama_parent'] ?? '-' ?></td>
            <td>
              <button class="btn btn-warning btn-sm"
                data-toggle="modal"
                data-target="#modalEditKelompok"
                data-id="<?= $row['id_kelompok'] ?>"
                data-kode="<?= htmlspecialchars($row['kode_kelompok']) ?>"
                data-nama="<?= htmlspecialchars($row['nama_kelompok']) ?>"
                data-tipe="<?= htmlspecialchars($row['tipe_kelompok']) ?>"
                data-parent="<?= htmlspecialchars($row['parent_id']) ?>">
                <i class="fa fa-edit"></i> Edit
              </button>
              <a href="kelompok_barang_hapus.php?id=<?= $row['id_kelompok']; ?>"
                 onclick="return confirm('Hapus data ini?')"
                 class="btn btn-danger btn-sm">
                Hapus
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
  </div>

</div>
<!-- MODAL INPUT Kelompok Barang -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <form method="post" action="kelompok_barang_simpan.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Tambah Kelompok Barang</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Kode Kelompok</label>
            <input type="text" name="kode_kelompok" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Nama Kelompok</label>
            <input type="text" name="nama_kelompok" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Tipe Kelompok</label>
            <select name="tipe_kelompok" class="form-control" required>
              <option value="">-- Pilih Tipe --</option>
              <option value="RAW_MATERIAL">Raw Material</option>
              <option value="LOGISTIK">Logistik</option>
              <option value="PRODUK_JADI">Produk Jadi</option>
            </select>
          </div>

          <div class="form-group">
            <label>Parent Kelompok (Opsional)</label>
            <select name="parent_id" class="form-control">
              <option value="">-- Tidak Ada (Kelompok Utama) --</option>
              <?php while($p = mysqli_fetch_assoc($parent)): ?>
                <option value="<?= $p['id_kelompok'] ?>">
                  <?= $p['kode_kelompok'] ?> - <?= $p['nama_kelompok'] ?>
                </option>
              <?php endwhile ?>
            </select>
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

<!-- MODAL EDIT KELOMPOK BARANG -->  
<div class="modal fade" id="modalEditKelompok">
  <div class="modal-dialog">
    <form method="post" action="kelompok_barang_update.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Edit Kelompok</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <input type="hidden" name="id_kelompok" id="edit_id">

          <div class="form-group">
            <label>Kode Kelompok</label>
            <input type="text" name="kode_kelompok" id="edit_kode"
              class="form-control" required>
          </div>

          <div class="form-group">
            <label>Nama Kelompok</label>
            <input name="nama_kelompok" id="edit_nama"
              class="form-control" required>
          </div>

          <div class="form-group">
            <label>Tipe Kelompok</label>
            <select name="tipe_kelompok" id="edit_tipe" class="form-control" required>
              <option value="">-- Pilih Tipe --</option>
              <option value="RAW_MATERIAL">Raw Material</option>
              <option value="LOGISTIK">Logistik</option>
              <option value="PRODUK_JADI">Produk Jadi</option>
            </select>
          </div>

          <div class="form-group">
            <label>Parent</label>
            <select name="parent_id" id="edit_parent"
              class="form-control"> 
              <option value="">-- Tanpa Parent --</option>
              <?php
              $parent_edit = mysqli_query($conn, "SELECT * FROM kelompok_barang ORDER BY kode_kelompok");
              while($p = mysqli_fetch_assoc($parent_edit)):
              ?>
                <option value="<?= $p['id_kelompok'] ?>">
                  <?= $p['kode_kelompok'] ?> - <?= $p['nama_kelompok'] ?>
                </option>
              <?php endwhile ?>
            </select>
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
$('#modalEditKelompok').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget)

  $('#edit_id').val(button.data('id'))
  $('#edit_kode').val(button.data('kode'))
  $('#edit_nama').val(button.data('nama'))
  $('#edit_tipe').val(button.data('tipe'))
  $('#edit_parent').val(button.data('parent'))
})
</script>
</body>
</html>
