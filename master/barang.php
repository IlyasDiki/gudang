<?php
require_once __DIR__ . '/../config/init.php';
$supplier = mysqli_query($conn, "SELECT * FROM supplier");
$id_kelompok = $_GET['id_kelompok'] ?? '';
// Sorting logic
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

$order_by = "b.id_barang ASC"; // default
if ($sort == 'kode') {
    $order_by = "b.kode_barang $order";
} elseif ($sort == 'nama') {
    $order_by = "b.nama_barang $order";
} elseif ($sort == 'kelompok') {
    $order_by = "k.nama_kelompok $order";
}
$search = $_GET['search'] ?? '';
$where = [];

if (!empty($id_kelompok)) {
    $id_kelompok_safe = mysqli_real_escape_string($conn, $id_kelompok);
    $where[] = "b.id_kelompok = '$id_kelompok_safe'";
}

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where[] = "(
        b.nama_barang LIKE '%$search_safe%' 
        OR b.kode_barang LIKE '%$search_safe%'
    )";
}

$where_sql = "";
if (!empty($where)) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where = "WHERE 
        b.nama_barang LIKE '%$search_safe%' 
        OR b.kode_barang LIKE '%$search_safe%'
        OR k.nama_kelompok LIKE '%$search_safe%'";
}

$sql = "
    SELECT b.*, k.nama_kelompok 
    FROM barang b
    LEFT JOIN kelompok_barang k 
        ON b.id_kelompok = k.id_kelompok
    $where_sql
    ORDER BY $order_by
";

$barang = mysqli_query($conn, $sql);

$kelompok = mysqli_query($conn, "SELECT * FROM kelompok_barang");

// Function to generate sort link and icon
function getSortLink($column, $current_sort, $current_order) {
    $new_order = ($current_sort == $column && $current_order == 'ASC') ? 'desc' : 'asc';
    $icon = 'fa-sort';

    if ($current_sort == $column) {
        $icon = $current_order == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
    }

    $search = $_GET['search'] ?? '';
    $id_kelompok = $_GET['id_kelompok'] ?? '';

    $params = "sort=$column&order=$new_order";

    if ($search) {
        $params .= "&search=" . urlencode($search);
    }

    if ($id_kelompok) {
        $params .= "&id_kelompok=" . urlencode($id_kelompok);
    }

    return "<a href=\"?$params\" class=\"sort-link\" style=\"text-decoration: none; color: inherit;\">
            <i class=\"fa $icon\"></i>
            </a>";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Barang</title>
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
    $page = 'barang';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>

  <!-- Content -->
  <div class="content-wrapper p-3">

    <section class="content-header">
      <h1>Master</h1>
    </section>

    <section class="content">

      <div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Barang</h3>
      <button class="btn btn-primary mb-2 float-right" data-toggle="modal" data-target="#modalBarang">
      + Tambah Barang
    </button>
  </div>
  <div class="card-body">
<form method="get" class="form-inline mb-3">

  <!-- FILTER KELOMPOK -->
  <select name="id_kelompok" class="form-control mr-2">
    <option value="">-- Semua Kelompok --</option>
    <?php while($k=mysqli_fetch_assoc($kelompok)): ?>
      <option value="<?= $k['id_kelompok'] ?>"
        <?= $id_kelompok==$k['id_kelompok']?'selected':'' ?>>
        <?= $k['nama_kelompok'] ?>
      </option>
    <?php endwhile ?>
  </select>

  <!-- SEARCH -->
  <input 
    type="text" 
    name="search" 
    class="form-control mr-2" 
    placeholder="Cari barang..." 
    value="<?= htmlspecialchars($search) ?>"
  >

  <!-- SIMPAN SORT -->
  <input type="hidden" name="sort" value="<?= $sort ?>">
  <input type="hidden" name="order" value="<?= strtolower($order) ?>">

  <button class="btn btn-primary">Tampilkan</button>
  <a href="barang.php" class="btn btn-secondary ml-2">Reset</a>

</form>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Kode <?php echo getSortLink('kode', $sort, $order); ?></th>
        <th>Nama Barang <?php echo getSortLink('nama', $sort, $order); ?></th>
        <th>Kelompok <?php echo getSortLink('kelompok', $sort, $order); ?></th>
        <th>Satuan</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($b = mysqli_fetch_assoc($barang)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $b['kode_barang'] ?></td>
        <td><?= $b['nama_barang'] ?></td>
        <td><?= $b['nama_kelompok'] ?></td>
        <td><?= $b['satuan'] ?></td>
        <td>
          <?= $b['aktif'] ? 
            '<span class="badge badge-success">Aktif</span>' :
            '<span class="badge badge-danger">Nonaktif</span>' ?>
        </td>
        <td>
          <a href="kartu_stok.php?id_barang=<?= $b['id_barang'] ?>"
            class="btn btn-sm btn-info">
            Kartu Stok
          </a>
          <button class="btn btn-warning btn-sm"
            data-toggle="modal"
            data-target="#modalEditBarang"
            data-id="<?= $b['id_barang'] ?>"
            data-kode="<?= $b['kode_barang'] ?>"
            data-nama="<?= $b['nama_barang'] ?>"
            data-kelompok="<?= $b['id_kelompok'] ?>"
            data-satuan="<?= $b['satuan'] ?>">
            <i class="fa fa-edit"></i> Edit
          </button>
          <a href="barang_hapus.php?id=<?= $b['id_barang'] ?>" 
            class="btn btn-danger btn-sm"
            onclick="return confirm('Hapus barang?')">Hapus</a>
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
<div class="modal fade" id="modalBarang">
  <div class="modal-dialog">
    <form method="post" action="barang_simpan.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Tambah Barang</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Kode Barang</label>
            <input type="text" name="kode_barang" class="form-control">
          </div>

          <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Kelompok</label>
            <select name="id_kelompok" class="form-control">
              <?php while($k = mysqli_fetch_assoc($kelompok)): ?>
                <option value="<?= $k['id_kelompok'] ?>">
                  <?= $k['nama_kelompok'] ?>
                </option>
              <?php endwhile ?>
            </select>
          </div>            

          <div class="form-group">
            <label>Satuan</label>
            <select name="satuan" class="form-control" required>
              <option value="">-- Pilih Tipe --</option>
              <option value="Kg">Kg</option>
              <option value="Pcs">Pcs</option>
              <option value="Pack">Pack</option>
              <option value="MB">MB</option>
            </select>
          </div>

          <div class="form-group">
            <label>Stok Minimum (Opsional)</label>
            <input type="text" name="stok_minimum" value="0" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-success">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT BARANG -->
<div class="modal fade" id="modalEditBarang">
  <div class="modal-dialog">
    <form method="post" action="barang_simpan_update.php">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Edit Barang</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_barang" id="edit_id">

          <div class="form-group">
            <label>Kode Barang</label>
            <input type="text" name="kode_barang" id="edit_kode" class="form-control">
          </div>

          <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" id="edit_nama" class="form-control">
          </div>

          <div class="form-group">
            <label>Kelompok</label>
            <select name="id_kelompok" id="edit_kelompok" class="form-control">
              <?php
              mysqli_data_seek($kelompok, 0);
              while($k = mysqli_fetch_assoc($kelompok)):
              ?>
                <option value="<?= $k['id_kelompok'] ?>">
                  <?= $k['nama_kelompok'] ?>
                </option>
              <?php endwhile ?>
            </select>
          </div>

          <div class="form-group">
            <label>Satuan</label>
            <select name="satuan" id="edit_satuan" class="form-control" required>
              <option value="">-- Pilih Tipe --</option>
              <option value="Kg">Kg</option>
              <option value="Pcs">Pcs</option>
              <option value="Pack">Pack</option>
              <option value="MB">MB</option>
            </select>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button class="btn btn-success">
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
$('#modalEditBarang').on('show.bs.modal', function (event) {
  const button = $(event.relatedTarget)

  $('#edit_id').val(button.data('id'))
  $('#edit_kode').val(button.data('kode'))
  $('#edit_nama').val(button.data('nama'))
  $('#edit_kelompok').val(button.data('kelompok'))
  $('#edit_satuan').val(button.data('satuan'))
})
</script>
</body>
</html>
