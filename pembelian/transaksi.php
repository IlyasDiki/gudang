<?php
require_once __DIR__ . '/../config/init.php';
$jenisTransaksi = mysqli_query($conn, "SELECT * FROM jenis_transaksi");
$namaKelompok    = mysqli_query($conn, "SELECT * FROM kelompok_barang");
$nama_kelompok = mysqli_fetch_all($namaKelompok, MYSQLI_ASSOC);
$barang         = mysqli_query($conn, "SELECT * FROM barang");

$q = mysqli_query($conn, "
SELECT 
  t.id_transaksi,
  t.tanggal_terima,
  jt.nama_jenis AS jenis_transaksi,
  kb.nama_kelompok AS nama_kelompok,
  u.nama AS dibuat_oleh
FROM transaksi t
LEFT JOIN kelompok_barang kb ON t.id_kelompok = kb.id_kelompok
LEFT JOIN jenis_transaksi jt ON t.jenis_transaksi = jt.id_jenist
LEFT JOIN users u ON t.dibuat_oleh = u.id_pengguna
ORDER BY t.tanggal_terima DESC, t.id_transaksi DESC
");

$kelompok = mysqli_query($conn, "
SELECT * 
FROM kelompok_barang
WHERE parent_id IS NULL
ORDER BY nama_kelompok
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Transaksi</title>
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
    $page = 'transaksi';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>  

  <!-- Content -->
  <div class="content-wrapper p-3">
    <?php if (isset($_GET['edit'])): ?>
      <div class="alert alert-success">
        Data transaksi berhasil Ditambahkan
      </div>
    <?php endif; ?>
    <section class="content-header">
      <h1>Transaksi</h1>
    </section>

    <section class="content">

      <div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Transaksi</h3>
    <button class="btn btn-primary float-right" data-toggle="modal" data-target="#modalTransaksi">
      + Tambah Transaksi
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Jenis Transaksi</th>
              <th>Kelompok Barang</th>
              <th>Dibuat Oleh</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; while($row=mysqli_fetch_assoc($q)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= $row['tanggal_terima'] ?></td>
              <td><?= htmlspecialchars($row['jenis_transaksi']) ?></td>
              <td><?= htmlspecialchars($row['nama_kelompok']) ?></td>
              <td><?= htmlspecialchars($row['dibuat_oleh']) ?></td>
              <td>
                <button class="btn btn-info btn-sm"
                        data-id="<?= $row['id_transaksi'] ?>"
                        data-toggle="modal"
                        data-target="#modalDetail">
                  Detail
                </button>
                <a href="transaksi_hapus.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data?')">Hapus</a>
              </td>
            </tr>
            <?php endwhile ?>
          </tbody>
        </table>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Data Kelompok Barang</h3>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Kelompok</th>
          <th>Nama Kelompok</th>
          <th>Tipe Kelompok</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; foreach($nama_kelompok as $k): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($k['kode_kelompok']) ?></td>
          <td><?= htmlspecialchars($k['nama_kelompok']) ?></td>
          <td><?= htmlspecialchars($k['tipe_kelompok'] ?? '-') ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
    </section>
  </div>

</div>

<!-- MODAL INPUT Transaksi -->
<div class="modal fade" id="modalTransaksi">
  <div class="modal-dialog modal-lg">
    <form method="post" action="transaksi_simpan.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Input Transaksi</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="row">

            <div class="col-md-4">
              <label>Tanggal</label>
              <input type="date" name="tanggal_terima" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label>Jenis Transaksi</label>
              <select name="jenis_transaksi" class="form-control" required>
                <option value="">-- Pilih --</option>
                <?php while($jt = mysqli_fetch_assoc($jenisTransaksi)): ?>
                  <option value="<?= $jt['id_jenist'] ?>">
                    <?= $jt['nama_jenis'] ?>
                  </option>
                <?php endwhile ?>
              </select>
            </div>

            <div class="col-md-4">
              <label>Kelompok Barang</label>
              <select id="kelompok" name="id_kelompok" class="form-control" required>
              <option value="">-- Pilih --</option>
              <?php while($k = mysqli_fetch_assoc($kelompok)): ?>
              <option value="<?= $k['id_kelompok'] ?>">
              <?= $k['nama_kelompok'] ?>
              </option>
              <?php endwhile ?>
              </select>
            </div>
                <br>
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

<div class="modal fade" id="modalDetail">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detail Transaksi</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="isiDetail">
        <div class="text-center">
          <i class="fa fa-spinner fa-spin"></i> Loading...
        </div>
      </div>

    </div>
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
  $('#modalDetail').on('show.bs.modal', function (e) {
    var id = $(e.relatedTarget).data('id');
    $('#isiDetail').load('transaksi_detail.php?id=' + id);
  });
</script>
<script>
  $("#kelompok").change(function(){

var id = $(this).val();

$("#barang").load("ajax_barang.php?id_kelompok="+id);

});

$("#barang").change(function(){

var id = $(this).val();

$("#supplier").load("ajax_supplier.php?id_barang="+id);

});
</script>
</body>
</html>
