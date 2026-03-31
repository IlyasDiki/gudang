<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_GET['id_barang'])) {
  die('ID barang tidak ditemukan');
}

$id_barang = $_GET['id_barang'];
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$q = mysqli_query($conn, "
(
  SELECT 
    m.tanggal AS tanggal,
    jm.nama_jenis AS uraian,
    m.arah AS arah,
    md.jumlah AS jumlah
  FROM mutasi_detail md
  JOIN mutasi m ON m.id_mutasi = md.id_mutasi
  JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis
  WHERE md.id_barang='$id_barang'
  AND MONTH(m.tanggal)='$bulan'
  AND YEAR(m.tanggal)='$tahun'
)
UNION ALL
(
  SELECT
    t.tanggal_terima AS tanggal,
    jt.nama_jenis AS uraian,
    jt.kode_jenis AS arah,
    td.jumlah AS jumlah
  FROM transaksi_detail td
  JOIN transaksi t ON t.id_transaksi = td.id_transaksi
  JOIN jenis_transaksi jt ON jt.id_jenist = t.jenis_transaksi
  WHERE td.id_barang='$id_barang'
  AND MONTH(t.tanggal_terima)='$bulan'
  AND YEAR(t.tanggal_terima)='$tahun'
)
ORDER BY tanggal ASC
");


$tglAwalBulan = $tahun . '-' . $bulan . '-01';

$qSaldoAwal = mysqli_query($conn, "
SELECT 
  (
    SELECT COALESCE(SUM(
      CASE 
        WHEN m.arah='MASUK' THEN md.jumlah
        WHEN m.arah='KELUAR' THEN -md.jumlah
        ELSE 0
      END
    ),0)
    FROM mutasi_detail md
    JOIN mutasi m ON m.id_mutasi = md.id_mutasi
    WHERE md.id_barang = '$id_barang'
    AND DATE(m.tanggal) < '$tahun-$bulan-01'
  )
  +
  (
    SELECT COALESCE(SUM(
      CASE
        WHEN jt.kode_jenis='MASUK' THEN td.jumlah
        ELSE -td.jumlah
      END
    ),0)
    FROM transaksi_detail td
    JOIN transaksi t ON t.id_transaksi = td.id_transaksi
    JOIN jenis_transaksi jt ON jt.id_jenist = t.jenis_transaksi
    WHERE td.id_barang='$id_barang'
    AND DATE(t.tanggal_terima) < '$tahun-$bulan-01'
  )
AS saldo_awal
");

$saldo = mysqli_fetch_assoc($qSaldoAwal)['saldo_awal'] ?? 0;

$qBarang = mysqli_query($conn, "
  SELECT b.nama_barang, k.nama_kelompok
  FROM barang b
  LEFT JOIN kelompok_barang k ON k.id_kelompok = b.id_kelompok
  WHERE b.id_barang='$id_barang'
  LIMIT 1
");
$barangInfo = mysqli_fetch_assoc($qBarang);

$namaBarang = $barangInfo['nama_barang'] ?? '-';
$namaKelompok = $barangInfo['nama_kelompok'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Kartu Stok</title>
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
    $page = 'kartustok';
    include "../layout/navbar.php";
    include "../layout/sidebar.php";
    ?>  

  <!-- Content -->
  <div class="content-wrapper p-3">

    <section class="content-header">
      <h1>Kartu Stok</h1>
      <p class="text-muted mb-0">
        Barang: <b><?= htmlspecialchars($namaBarang) ?></b>
        <?php if($namaKelompok): ?>
          (<?= htmlspecialchars($namaKelompok) ?>)
        <?php endif; ?>
      </p>
    </section>

    <section class="content">

      <div class="card">
  <div class="card-header">
    <h3 class="card-title">Data Kartu Stok</h3>
  </div>

  <div class="card-body">

  <form method="get" class="form-inline mb-3">
    <input type="hidden" name="id_barang" value="<?= $id_barang ?>">

    <select name="bulan" class="form-control mr-2">
      <?php for($b=1;$b<=12;$b++): ?>
        <option value="<?= sprintf('%02d',$b) ?>"
          <?= $bulan==sprintf('%02d',$b)?'selected':'' ?>>
          <?= date('F', mktime(0,0,0,$b,1)) ?>
        </option>
      <?php endfor ?>
    </select>

    <select name="tahun" class="form-control mr-2">
      <?php for($t=date('Y')-5;$t<=date('Y');$t++): ?>
        <option value="<?= $t ?>" <?= $tahun==$t?'selected':'' ?>>
          <?= $t ?>
        </option>
      <?php endfor ?>
    </select>

    <button class="btn btn-primary">Tampilkan</button>
  </form>

    <table class="table table-bordered table-sm">
  <thead class="bg-light">
    <tr class="bg-light font-weight-bold">
      <td colspan="4">Saldo Awal</td>
      <td class="text-right"><?= number_format($saldo,2) ?></td>
    </tr>

    <tr>
      <th>Tanggal</th>
      <th>Uraian</th>
      <th class="text-right">Masuk</th>
      <th class="text-right">Keluar</th>
      <th class="text-right">Saldo</th>
    </tr>
  </thead>
  <tbody>

<?php while($row=mysqli_fetch_assoc($q)): ?>
<?php
  $masuk = 0;
  $keluar = 0;

  if ($row['arah'] == 'MASUK') {
    $masuk = $row['jumlah'];
    $saldo += $masuk;
  } else {
    $keluar = $row['jumlah'];
    $saldo -= $keluar;
  }
?>
<tr>
  <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
  <td><?= htmlspecialchars($row['uraian']) ?></td>
  <td class="text-right"><?= $masuk ? number_format($masuk,2) : '' ?></td>
  <td class="text-right"><?= $keluar ? number_format($keluar,2) : '' ?></td>
  <td class="text-right font-weight-bold"><?= number_format($saldo,2) ?></td>
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
<div class="modal fade" id="modalPembelian">
  <div class="modal-dialog modal-lg">
    <form method="post" action="pembelian_simpan.php">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title">Input Pembelian</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="col-md-4">
              <label>Tanggal Terima</label>
              <input type="date" name="tanggal_terima" class="form-control" required>
            </div>

            <div class="col-md-4">
              <label>Supplier</label>
              <select name="id_supplier" class="form-control" required>
                <option value="">-- Pilih Supplier --</option>
                <?php while($s=mysqli_fetch_assoc($supplier)): ?>
                  <option value="<?= $s['id_supplier'] ?>">
                    <?= $s['nama_supplier'] ?>
                  </option>
                <?php endwhile ?>
              </select>
            </div>

            <div class="col-md-4">
              <label>Jenis Pembelian</label>
              <select name="jenis_pembelian" class="form-control" required>
                <option value="BAHAN_BAKU">Bahan Baku</option>
                <option value="PENDUKUNG">Pendukung</option>
                <option value="PACKAGING">Packaging</option>
              </select>
            </div>
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
</body>
</html>
