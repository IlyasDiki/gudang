<?php
require '../config/init.php';

function angka($nilai) {
  return rtrim(rtrim(number_format((float)$nilai, 2, '.', ''), '0'), '.');
}
$id_supplier = $_GET['id_supplier'] ?? '';
$id_barang_arang = 13; // arang tempurung
$id_barang_powder = 14; // powder

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$tglAwal = $tahun . '-' . $bulan . '-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));
/* ==========================
   AMBIL DATA PEMAKAIAN AT
   ========================== */
$q = mysqli_query($conn, "
  SELECT
    d.tanggal,
    d.id_mutasi_detail,
    md.jumlah AS stok_sumber,
    b.nama_barang,
    d.sortir,
    d.ma,
    d.aa,
    d.b_mentah,
    d.air,
    d.atp,
    k.nama_kelompok
  FROM at_detail d
  JOIN barang b ON b.id_barang = d.id_barang
  JOIN kelompok_barang k ON k.id_kelompok = b.id_kelompok

  LEFT JOIN mutasi_detail md 
    ON md.id_detail = d.id_mutasi_detail

  WHERE DATE(d.tanggal) BETWEEN '$tglAwal' AND '$tglAkhir'
  AND d.id_barang = '$id_barang_arang'
  ". (!empty($id_supplier) ? " AND d.id_supplier = '$id_supplier'" : "") . "
  ORDER BY d.id_mutasi_detail, d.tanggal ASC
");

$qListBarang = mysqli_query($conn, "
  SELECT id_barang, nama_barang, kode_barang
  FROM barang
  WHERE id_kelompok = '3'
  ORDER BY nama_barang ASC
");

$qBarang = mysqli_query($conn, "
  SELECT id_barang, nama_barang, kode_barang
  FROM barang
  WHERE id_barang = '$id_barang_arang'
  LIMIT 1
");

$qSupplier = mysqli_query($conn, "
  SELECT id_supplier, nama_supplier
  FROM supplier
  ORDER BY nama_supplier ASC
");
$namaSupplierDipilih = '';

if (!empty($id_supplier)) {
  $qNama = mysqli_query($conn, "
    SELECT nama_supplier FROM supplier 
    WHERE id_supplier = '$id_supplier' LIMIT 1
  ");
  $namaSupplierDipilih = mysqli_fetch_assoc($qNama)['nama_supplier'] ?? '';
}


$data = [];
$total = ['sortir'=>0,'ma'=>0,'aa'=>0,'b_mentah'=>0,'air'=>0,'atp'=>0];

while ($row = mysqli_fetch_assoc($q)) {
  foreach ($total as $k => $v) {
    $row[$k] = (float)$row[$k];
    $total[$k] += $row[$k];
  }
  $data[] = $row;
}

/*========================
   DATA PRODUKSI
========================== */
$qProd = mysqli_query($conn, "
  SELECT p.tanggal, pd.mixer
  FROM produksi p
  JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
  WHERE p.id_barang_atp = '$id_barang_powder'
  ". (!empty($id_supplier) ? " AND p.id_supplier = '$id_supplier'" : "") . "
  ORDER BY p.tanggal ASC
");

$dataProd = [];
while ($row = mysqli_fetch_assoc($qProd)) {
  $row['mixer'] = (float)$row['mixer'];
  $dataProd[] = $row;
}

/* ==========================
   RINGKASAN (tetap pakai global)
========================== */
$jumlah_awal = !empty($data) ? (float)$data[0]['stok_sumber'] : 0;

$total_pakai = array_sum($total);

$susut = $jumlah_awal > 0
  ? ($jumlah_awal - $total['atp']) / $jumlah_awal
  : 0;

$kand_ma = $jumlah_awal > 0 ? ($total['ma'] / $jumlah_awal) * 100 : 0;
$kand_aa = $jumlah_awal > 0 ? ($total['aa'] / $jumlah_awal) * 100 : 0;
$kand_bm = $jumlah_awal > 0 ? ($total['b_mentah'] / $jumlah_awal) * 100 : 0;

$total_susut = $susut * 100;

/* ==========================
   LOOP PREP
========================== */
$rowsAT = count($data);
$rowsProd = count($dataProd);
$maxRows = max($rowsAT, $rowsProd);
if ($maxRows < 1) $maxRows = 1;

/* saldo */
$saldoAT = 0;
$last_mutasi = null;

$saldoProd = 0;
$initProd = false;

/* tanggal */
$tgl_terima = $rowsAT > 0 ? $data[0]['tanggal'] : '-';

$namaBulan = [
  "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
  "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
  "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

$judulBulan = ($namaBulan[$bulan] ?? $bulan) . " " . $tahun;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laporan Pemakaian AT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- kalau kamu sudah pakai bootstrap di template utama, ini bisa dihapus -->
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
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 30px;
    }
    .card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,.08);
      overflow-x: auto;
      max-width: 100% !important;
      width: 100% !important;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }
    .btn {
      display: inline-block;
      padding: 8px 14px;
      border-radius: 6px;
      color: white;
      text-decoration: none;
      font-size: 14px;
    }
    .btn-back { background: #17a2b8; }
    .btn-excel { background: #28a745; }
    .btn-pdf { background: #dc3545; }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    th, td {
      border: 1px solid #000;
      padding: 4px 6px;
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
    }
    th { font-weight: bold; }
    .bg-head { background: #f2f2f2; }
    .bg-subhead { background: #fafafa; }
  </style>
</head>
<body>

<div class="report-wrapper">

  <div class="card shadow-sm">
    <div class="card-body">
      <a href="../transaksi/pemakaian_at.php" class="btn btn-info btn-sm">Back</a>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h4 class="mb-0">AT & Powder Bulanan</h4>
          <h4 class="mb-0"><b><?= $namaSupplierDipilih ?: 'Pilih Supplier' ?></b></h4>
          <small class="text-muted">Periode: <b><?= $judulBulan ?></b></small>
        </div>
      </div>

      <hr>

      <!-- FILTER -->
      <form method="GET" class="row g-2 mb-3">

      <div class="col-md-3">
        <label class="mr-2 font-weight-bold">Supplier</label>
        <select name="id_supplier" class="form-control mr-4" required>
          <option value="">-- Pilih Supplier --</option>

          <?php while($s = mysqli_fetch_assoc($qSupplier)): ?>
            <option value="<?= $s['id_supplier'] ?>"
              <?= ($id_supplier == $s['id_supplier']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['nama_supplier']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

        <div class="col-md-3">
          <label class="mr-2 font-weight-bold">Bulan</label>
          <select name="bulan" class="form-control mr-4" required>
            <?php foreach($namaBulan as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($k==$bulan?'selected':'') ?>>
                <?= $v ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="mr-2 font-weight-bold">Tahun</label>
          <select name="tahun" class="form-control mr-4" required>
            <?php
              $thnSekarang = date('Y');
              for($t = $thnSekarang-2; $t <= $thnSekarang+1; $t++):
            ?>
              <option value="<?= $t ?>" <?= ($t==$tahun?'selected':'') ?>>
                <?= $t ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <button class="btn btn-primary mr-2" type="submit">Tampilkan</button>

        <button class="btn btn-success mr-2"
            href="pemakaian_at_laporan_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
            Export Excel
        </button>

        <button class="btn btn-danger"
            href="pemakaian_at_laporan_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
            Export PDF
        </button>
      </form>
  <table>
    <tr class="bg-head">
      <th rowspan="2">NO</th>
      <th rowspan="2">TGL TERIMA</th>
      <th rowspan="2">JUMLAH</th>
      <th colspan="13">PEMAKAIAN AT</th>
      <th colspan="4">PEMAKAIAN PRODUKSI</th>
    </tr>

    <tr class="bg-subhead">
      <th>TGL</th>
      <th>SORTIR</th>
      <th>SALDO</th>
      <th>MA</th>
      <th>AA</th>
      <th>B.MENTAH</th>
      <th>AIR</th>
      <th>ATP</th>
      <th>Kand. MA</th>
      <th>Kand. AA</th>
      <th>Kand. BM</th>
      <th>Susut Timb.</th>
      <th>Total Susut</th>

      <th>JUMLAH</th>
      <th>TGL</th>
      <th>MIXER</th>
      <th>SALDO</th>
    </tr>

    <?php for ($i=0;$i<$maxRows;$i++): 

      $dAT = $data[$i] ?? null;
      $dP  = $dataProd[$i] ?? null;

      /* SALDO AT */
      if ($dAT) {
        if ($last_mutasi != $dAT['id_mutasi_detail']) {
          $saldoAT = (float)$dAT['stok_sumber'];
          $last_mutasi = $dAT['id_mutasi_detail'];
        }
        $saldoAT -= $dAT['sortir'];
      }

      /* SALDO PRODUKSI */
      if (!$initProd) {
        $saldoProd = (float)$total['atp'];
        $initProd = true;
      }

      if ($dP) {
        $saldoProd -= $dP['mixer'];
      }
    ?>

      <tr>

        <!-- NO / TGL TERIMA / JUMLAH => ROWSPAN 1 BLOK -->
        <?php if ($i == 0): ?>
          <td rowspan="<?= $maxRows ?>">1</td>
          <td rowspan="<?= $maxRows ?>"><?= $tgl_terima ?></td>
          <td rowspan="<?= $maxRows ?>"><?= $rowsAT > 0 ? angka($data[0]['stok_sumber']) : 0 ?></td>
        <?php endif; ?>

        <!-- PEMAKAIAN AT -->
        <td><?= $dAT ? $dAT['tanggal'] : '' ?></td>
        <td><?= $dAT ? angka($dAT['sortir']) : '' ?></td>
        <td><?= $dAT ? angka($saldoAT) : '' ?></td>
        <td><?= $dAT ? angka($dAT['ma']) : '' ?></td>
        <td><?= $dAT ? angka($dAT['aa']) : '' ?></td>
        <td><?= $dAT ? angka($dAT['b_mentah']) : '' ?></td>
        <td><?= $dAT ? angka($dAT['air']) : '' ?></td>
        <td><?= $dAT ? angka($dAT['atp']) : '' ?></td>

        <!-- KANDUNGAN + SUSUT => ROWSPAN 1 BLOK (BIAR TIDAK ADA KOTAK KOSONG BERGARIS) -->
        <?php if ($i == 0): ?>
          <td rowspan="<?= $maxRows ?>"><?= round($kand_ma,2) ?>%</td>
          <td rowspan="<?= $maxRows ?>"><?= round($kand_aa,2) ?>%</td>
          <td rowspan="<?= $maxRows ?>"><?= round($kand_bm,2) ?>%</td>
          <td rowspan="<?= $maxRows ?>"><?= angka($susut) ?>%</td>
          <td rowspan="<?= $maxRows ?>"><?= round($total_susut,2) ?>%</td>
        <?php endif; ?>

        <!-- PEMAKAIAN PRODUKSI -->
        <td><?= $dP ? angka($total['atp']) : '' ?></td>
        <td><?= $dP ? $dP['tanggal'] : '' ?></td>
        <td><?= $dP ? $dP['mixer'] : '' ?></td>
        <td><?= $dP ? angka($saldoProd) : '' ?></td>

      </tr>
    <?php endfor; ?>
    <!-- TOTAL ROW -->
    <tr class="bg-head">
      <th colspan="4">TOTAL</th>
      <th><?= angka($total['sortir']) ?></th>
      <th>-</th>
      <th><?= angka($total['ma']) ?></th>
      <th><?= angka($total['aa']) ?></th>
      <th><?= angka($total['b_mentah']) ?></th>
      <th><?= angka($total['air']) ?></th>
      <th><?= angka($total['atp']) ?></th>

      <th><?= round($kand_ma,2) ?>%</th>
      <th><?= round($kand_aa,2) ?>%</th>
      <th><?= round($kand_bm,2) ?>%</th>
      <th><?= angka($susut) ?></th>
      <th><?= round($total_susut  ,2) ?>%</th>

      <th colspan="4"></th>
    </tr>

  </table>
      <div class="mt-2">
        <small class="text-muted">
          Catatan: Stok Awal atau jumlah dihitung dari saldo mutasi sebelum tanggal <b><?= $tglAwal ?></b>.
        </small>
      </div>

    </div>
  </div>

</div>

</body>
</html>
