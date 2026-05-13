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

$qPemakaianAT = mysqli_query($conn, "
SELECT
    IFNULL(SUM(
        COALESCE(sortir,0) +
        COALESCE(ma,0) +
        COALESCE(aa,0) +
        COALESCE(b_mentah,0) +
        COALESCE(air,0) +
        COALESCE(atp,0)
    ),0) AS total
FROM at_detail
WHERE tanggal = CURDATE()
AND status = 'aktif'
");

$pemakaianAT = mysqli_fetch_assoc($qPemakaianAT)['total'];

$qProduksiAT = mysqli_query($conn, "
SELECT
    IFNULL(SUM(pd.mixer),0) AS total
FROM produksi p
JOIN produksi_detail pd
    ON pd.id_produksi = p.id_produksi
WHERE p.tanggal = CURDATE()
");

$produksiAT = mysqli_fetch_assoc($qProduksiAT)['total'];

$qProduksiBriket = mysqli_query($conn, "
SELECT
    IFNULL(SUM(add_kg),0) AS total
FROM bkbriket_bongkar
WHERE tanggal_bongkar = CURDATE()
");

$produksiBriket = mysqli_fetch_assoc($qProduksiBriket)['total'];

$labels = [];
$dataMasuk = [];
$dataKeluar = [];

for($i = 6; $i >= 0; $i--) {

    $tgl = date('Y-m-d', strtotime("-$i days"));
    $label = date('d M', strtotime($tgl));

    $labels[] = $label;

    // MASUK
    $qMasuk = mysqli_query($conn, "
        SELECT IFNULL(SUM(md.jumlah),0) total
        FROM mutasi m
        JOIN mutasi_detail md
            ON md.id_mutasi = m.id_mutasi
        WHERE m.arah = 'MASUK'
        AND m.tanggal = '$tgl'
    ");

    $m = mysqli_fetch_assoc($qMasuk);

    $dataMasuk[] = (float)$m['total'];

    // KELUAR
    $qKeluar = mysqli_query($conn, "
        SELECT IFNULL(SUM(md.jumlah),0) total
        FROM mutasi m
        JOIN mutasi_detail md
            ON md.id_mutasi = m.id_mutasi
        WHERE m.arah = 'KELUAR'
        AND m.tanggal = '$tgl'
    ");

    $k = mysqli_fetch_assoc($qKeluar);

    $dataKeluar[] = (float)$k['total'];
}
$qMonitor = mysqli_query($conn, "

SELECT
    dm.id_monitor,
    b.nama_barang,
    s.nama_supplier,

    COALESCE(SUM(
        CASE
            WHEN m.arah = 'MASUK' THEN md.jumlah
            WHEN m.arah = 'KELUAR' THEN -md.jumlah
            ELSE 0
        END
    ),0) AS saldo

FROM dashboard_monitor dm

JOIN barang b
    ON b.id_barang = dm.id_barang

LEFT JOIN supplier s
    ON s.id_supplier = dm.id_supplier

LEFT JOIN mutasi_detail md
    ON md.id_barang = dm.id_barang
    AND (
        dm.id_supplier IS NULL
        OR md.id_supplier = dm.id_supplier
    )

LEFT JOIN mutasi m
    ON m.id_mutasi = md.id_mutasi

GROUP BY dm.id_monitor

ORDER BY saldo ASC

");

$qBarangPantauan = mysqli_query($conn, "

SELECT
    id_barang,
    nama_barang,
    pakai_supplier

FROM barang

WHERE aktif = 1

ORDER BY nama_barang

");

$qSupplierPantauan = mysqli_query($conn, "

SELECT
    id_supplier,
    nama_supplier

FROM supplier

ORDER BY nama_supplier

");
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
</head>
<style>

body{
    font-size:14px;
}

/* =========================
   CARD & BOX
========================= */

.card,
.small-box{
    border-radius:10px;
    border:none;
}

.card{
    box-shadow:0 2px 6px rgba(0,0,0,.06);
}

.card-header{
    background:#fff;
    border-bottom:1px solid #f1f1f1;
    padding:12px 16px;
}

.card-title{
    font-size:18px;
    font-weight:600;
}

/* =========================
   MINI INFO BOX
========================= */

.mini-box{
    min-height:100px;
    margin-bottom:15px;
    border-radius:10px;
    overflow:hidden;
}

.mini-box .inner{
    padding:12px 16px;
}

.mini-box h3{
    font-size:36px;
    margin:0 0 5px;
    font-weight:700;
}

.mini-box p{
    margin:0;
    font-size:14px;
}

.mini-box .icon i{
    font-size:58px !important;
    top:18px;
    opacity:.18;
}

/* =========================
   MONITORING PRODUKSI
========================= */

.monitor-card{
    min-height:360px;
}

.monitor-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 0;
    border-bottom:1px solid #f1f1f1;
}

.monitor-item:last-child{
    border-bottom:none;
}

.monitor-left{
    display:flex;
    align-items:center;
}

.monitor-icon{
    width:42px;
    height:42px;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    margin-right:12px;
    font-size:18px;
}

.monitor-title{
    font-size:14px;
    color:#666;
}

.monitor-value{
    font-size:22px;
    font-weight:700;
}

/* =========================
   WARNING STOCK
========================= */

.warning-scroll{
    max-height:305px;
    overflow-y:auto;
}

.table td,
.table th{
    vertical-align:middle;
    padding:10px 12px;
}

.table thead th{
    border-top:none;
    font-weight:600;
}

.warning-value{
    color:#dc3545;
    font-weight:700;
}

/* =========================
   AKTIVITAS
========================= */

.aktivitas-scroll{
    max-height:340px;
    overflow-y:auto;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){
    .mini-box h3{
        font-size:28px;
    }
    .monitor-value{
        font-size:18px;
    }

}

.modern-stat-card{
    background:#fff;
    border-radius:12px;
    padding:18px;
    display:flex;
    align-items:center;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
    margin-bottom:18px;
    transition:.2s;
}

.modern-stat-card:hover{
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.modern-stat-icon{
    width:70px;
    height:70px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-right:18px;
    color:#fff;
    font-size:28px;
    flex-shrink:0;
}

.modern-stat-content{
    flex:1;
}

.modern-stat-title{
    font-size:14px;
    color:#777;
    margin-bottom:5px;
}

.modern-stat-value{
    font-size:34px;
    font-weight:700;
    line-height:1.1;
    color:#222;
}

/* MOBILE */
@media(max-width:768px){

    .modern-stat-icon{
        width:60px;
        height:60px;
        font-size:22px;
    }

    .modern-stat-value{
        font-size:28px;
    }

}
</style>
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
  <section class="content">
  <div class="container-fluid">

    <!-- INFO BOX -->
<div class="row">

  <!-- TOTAL ITEM -->
  <div class="col-lg-4 col-md-6">

    <div class="modern-stat-card">

      <div class="modern-stat-icon bg-info">
        <i class="fas fa-boxes"></i>
      </div>

      <div class="modern-stat-content">
        <div class="modern-stat-title">
          Total Item
        </div>

        <div class="modern-stat-value">
          <?= number_format($total_barang) ?>
        </div>
      </div>

    </div>

  </div>

  <!-- BARANG MASUK -->
  <div class="col-lg-4 col-md-6">

    <div class="modern-stat-card">

      <div class="modern-stat-icon bg-success">
        <i class="fas fa-arrow-down"></i>
      </div>

      <div class="modern-stat-content">
        <div class="modern-stat-title">
          Barang Masuk 7 Hari
        </div>

        <div class="modern-stat-value">
          <?= number_format($masuk7Hari) ?>
        </div>
      </div>

    </div>

  </div>

  <!-- BARANG KELUAR -->
  <div class="col-lg-4 col-md-6">

    <div class="modern-stat-card">

      <div class="modern-stat-icon bg-warning">
        <i class="fas fa-arrow-up"></i>
      </div>

      <div class="modern-stat-content">
        <div class="modern-stat-title">
          Barang Keluar 7 Hari
        </div>

        <div class="modern-stat-value">
          <?= number_format($keluar7Hari) ?>
        </div>
      </div>

    </div>

  </div>

</div>

    <!-- MONITORING + WARNING -->
    <div class="row">

      <!-- MONITORING -->
      <div class="col-lg-5">

        <div class="card monitor-card">

          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-industry mr-1"></i>
              Monitoring Produksi Hari Ini
            </h3>
          </div>

          <div class="card-body py-2">
            <div class="monitor-item">
              <div class="monitor-left">
                <div class="monitor-icon bg-info">
                  <i class="fas fa-dolly-flatbed"></i>
                </div>

                <div>
                  <div class="monitor-title">
                    Pemakaian AT
                  </div>

                  <div class="monitor-value">
                    <?= number_format($pemakaianAT,2) ?> KG
                  </div>
                </div>

              </div>
            </div>

            <div class="monitor-item">
              <div class="monitor-left">
                <div class="monitor-icon bg-purple">
                  <i class="fas fa-blender"></i>
                </div>

                <div>
                  <div class="monitor-title">
                    Produksi AT
                  </div>

                  <div class="monitor-value">
                    <?= number_format($produksiAT,2) ?> KG
                  </div>
                </div>

              </div>

            </div>

            <div class="monitor-item">
              <div class="monitor-left">
                <div class="monitor-icon bg-success">
                  <i class="fas fa-cubes"></i>
                </div>

                <div>
                  <div class="monitor-title">
                    Produksi Briket
                  </div>

                  <div class="monitor-value">
                    <?= number_format($produksiBriket,2) ?> KG
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- WARNING -->
      <div class="col-lg-7">

        <div class="card card-danger card-outline monitor-card">
        <div class="card-header">

            <h3 class="card-title">
              <i class="fas fa-eye mr-1"></i>
              Stok Pantauan
            </h3>

            <div class="card-tools">
              <button
                  class="btn btn-tool"
                  data-toggle="modal"
                  data-target="#modalPantauan">
                  Kelola
              </button>
            </div>

        </div>

          <div class="card-body p-0 warning-scroll">

            <table class="table table-sm mb-0">

            <thead>
            <tr>
                <th>Barang</th>
                <th>Supplier</th>
                <th class="text-right">Stok</th>
                <th width="80">Aksi</th>
            </tr>
            </thead>

              <tbody>

              <?php if(mysqli_num_rows($qMonitor) > 0): ?>

                  <?php while($w = mysqli_fetch_assoc($qMonitor)): ?>

                  <tr>
                      <td>
                          <?= htmlspecialchars($w['nama_barang']) ?>
                      </td>
                      <td>
                          <?= $w['nama_supplier'] ?? '-' ?>
                      </td>
                      <td class="text-right warning-value">
                          <?php
                              $saldo = $w['saldo'];

                              if($saldo <= 0){
                                  echo '<span class="text-danger font-weight-bold">';
                              }
                              elseif($saldo <= 25){
                                  echo '<span class="text-warning font-weight-bold">';
                              }
                              else{
                                  echo '<span class="text-success font-weight-bold">';
                              }
                          ?>
                          <?= number_format($saldo,2) ?>
                          </span>
                      </td>
                      <td class="text-center">
                        <button
                            class="btn btn-danger btn-sm btnHapusPantauan"
                            data-id="<?= $w['id_monitor'] ?>"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                  </tr>
                  <?php endwhile ?>
              <?php else: ?>

              <tr>
                  <td colspan="3" class="text-center text-muted py-4">
                      Belum ada stok pantauan
                  </td>
              </tr>

              <?php endif ?>

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- GRAFIK -->
    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-line mr-1"></i>
          Grafik Barang Masuk vs Keluar (7 Hari)
        </h3>
      </div>

      <div class="card-body">
        <canvas id="grafikMutasi" height="140"></canvas>
      </div>

    </div>

    <!-- AKTIVITAS -->
    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-history mr-1"></i>
          Aktivitas Terakhir
        </h3>
      </div>

      <div class="card-body p-0 aktivitas-scroll">

        <?php if(mysqli_num_rows($data) > 0): ?>

        <table class="table table-hover table-sm mb-0">

          <thead>
            <tr>
              <th>Kelompok</th>
              <th>Barang</th>
              <th class="text-right">Masuk</th>
              <th class="text-right">Keluar</th>
              <th class="text-right">Selisih</th>
            </tr>
          </thead>

          <tbody>

          <?php while($r = mysqli_fetch_assoc($data)): 
            $selisih = $r['masuk'] - $r['keluar'];
          ?>
          <tr>
            <td><?= $r['nama_kelompok'] ?></td>
            <td><?= $r['nama_barang'] ?></td>
            <td class="text-right text-success">
              <?= number_format($r['masuk'],2) ?>
            </td>
            <td class="text-right text-danger">
              <?= number_format($r['keluar'],2) ?>
            </td>
            <td class="text-right font-weight-bold">
              <?= number_format($selisih,2) ?>
            </td>
          </tr>
          <?php endwhile ?>

          </tbody>

        </table>

        <?php else: ?>

        <div class="text-center text-muted py-5">
          Belum ada aktivitas dalam 7 hari terakhir
        </div>

        <?php endif ?>
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

<!-- MODAL STOK PANTAUAN -->
<div
    class="modal fade"
    id="modalPantauan"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Kelola Stok Pantauan
                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                >
                    <span>&times;</span>
                </button>

            </div>

            <form id="formPantauan" method="POST">

                <div class="modal-body">

                    <!-- BARANG -->
                    <div class="form-group">

                        <label>
                            Barang
                        </label>

                        <select
                            name="id_barang"
                            id="barangPantauan"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Barang --
                            </option>

                            <?php while($b = mysqli_fetch_assoc($qBarangPantauan)): ?>

                            <option
                                value="<?= $b['id_barang'] ?>"
                                data-supplier="<?= $b['pakai_supplier'] ?>"
                            >
                                <?= htmlspecialchars($b['nama_barang']) ?>
                            </option>

                            <?php endwhile ?>

                        </select>

                    </div>

                    <!-- SUPPLIER -->
                    <div
                        class="form-group"
                        id="supplierPantauanBox"
                        style="display:none;"
                    >

                        <label>
                            Supplier
                        </label>

                        <select
                            name="id_supplier"
                            id="supplierPantauan"
                            class="form-control"
                        >

                            <option value="">
                                -- Pilih Supplier --
                            </option>

                            <?php while($s = mysqli_fetch_assoc($qSupplierPantauan)): ?>

                            <option value="<?= $s['id_supplier'] ?>">
                                <?= htmlspecialchars($s['nama_supplier']) ?>
                            </option>

                            <?php endwhile ?>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Tutup
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Tambah Pantauan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

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
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>

const ctx = document.getElementById('grafikMutasi');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [

            {
                label: 'Barang Masuk',
                data: <?= json_encode($dataMasuk) ?>,
                borderColor: '#28a745',
                backgroundColor: 'transparent',
                borderWidth: 3,
                tension: 0.3
            },
            {
                label: 'Barang Keluar',
                data: <?= json_encode($dataKeluar) ?>,
                borderColor: '#dc3545',
                backgroundColor: 'transparent',
                borderWidth: 3,
                tension: 0.3
            }
        ]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

</script>
<script>

$(document).ready(function(){

    // SELECT2
    $('#barangPantauan').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalPantauan'),
        width: '100%'
    });

    $('#supplierPantauan').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalPantauan'),
        width: '100%'
    });

    // SHOW SUPPLIER
    $('#barangPantauan').on('change', function(){

        let supplier =
            $(this)
            .find(':selected')
            .data('supplier');

        if(supplier == 1){

            $('#supplierPantauanBox').slideDown(150);

        }
        else{

            $('#supplierPantauanBox').slideUp(150);

            $('#supplierPantauan').val('').trigger('change');

        }

    });

});
</script>
<script>

$(document).on('click', '.btnHapusPantauan', function(){

    if(!confirm('Hapus pantauan ini?')){
        return;
    }

    let id = $(this).data('id');

    $.post(
        'master/dashboard_monitor_hapus.php',
        {id:id},
        function(){
            location.reload();
        }
    );

});

</script>

  <script>

$(document).ready(function(){

    $('#formPantauan').on('submit', function(e){

        e.preventDefault();

        let id_barang = $('#barangPantauan').val();
        let id_supplier = $('#supplierPantauan').val();

        $.ajax({

            url: 'master/dashboard_monitor_simpan.php',
            type: 'POST',

            data: {
                id_barang: id_barang,
                id_supplier: id_supplier
            },

            dataType: 'json',

            beforeSend: function(){

                $('#formPantauan button[type="submit"]')
                    .prop('disabled', true)
                    .text('Menyimpan...');

            },

            success: function(response){

                console.log(response);

                if(response.success){

                    alert('Pantauan berhasil ditambahkan');

                    location.reload();

                } else {

                    alert(response.message);

                }

            },

            error: function(xhr){

                console.log(xhr.responseText);

                alert('Terjadi error');

            },

            complete: function(){

                $('#formPantauan button[type="submit"]')
                    .prop('disabled', false)
                    .text('Tambah Pantauan');

            }

        });

    });

});

</script>
</body>
</html>