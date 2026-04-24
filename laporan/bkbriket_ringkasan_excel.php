<?php
require '../config/init.php';

$id_barang_briket = $_GET['id_barang_briket'] ?? '';
$status = $_GET['status'] ?? 'LOLOS';
$warnaStatus = ($status == 'KARANTINA') ? 'red' : 'green';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = sprintf("%02d", (int)$bulan);
$tglAwal = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=RINGKASAN_Briket.xls");

$whereBarangFilter = '';
if ($id_barang_briket != '') {
  $whereBarangFilter = "AND b.id_barang_briket = '$id_barang_briket'";
}

$statusFilter = '';
if ($status) {
  $statusFilter = "AND b.status = '$status'";
}

/* =========================
   QUERY
========================= */
$q = mysqli_query($conn, "
  SELECT
    b.id_bk,
    b.tanggal,
    br.nama_barang,

    (SELECT MIN(tanggal_bongkar)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS tgl_bongkar,

    (SELECT GROUP_CONCAT(DISTINCT ket SEPARATOR ', ')
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS ket,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS bongkar_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_bongkar bo
     WHERE bo.id_bk = b.id_bk) AS bongkar_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING') AS packing_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='PACKING') AS packing_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO') AS repro_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='REPRO') AS repro_add,

    (SELECT COALESCE(SUM(krg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL') AS jual_krg,

    (SELECT COALESCE(SUM(add_kg),0)
     FROM bkbriket_mutasi m
     WHERE m.id_bk = b.id_bk AND m.jenis='JUAL') AS jual_add

  FROM bkbriket b
  JOIN barang br ON br.id_barang = b.id_barang_briket

  WHERE
    b.tanggal <= '$tglAkhir'
    $statusFilter
    $whereBarangFilter

  ORDER BY br.nama_barang, b.tanggal ASC
");


$currentBarang = '';
$dataGroup = [];
$grandTotal = 0;

/* =========================
   GROUP DATA
========================= */
while($r = mysqli_fetch_assoc($q)){

  $bongkar = ($r['bongkar_krg'] * 25) + $r['bongkar_add'];
  $packing = ($r['packing_krg'] * 25) + $r['packing_add'];
  $repro   = ($r['repro_krg'] * 25) + $r['repro_add'];
  $jual    = ($r['jual_krg'] * 25) + $r['jual_add'];

  $saldo = $bongkar - ($packing + $repro + $jual);

  if ($saldo <= 0) continue;

  $dataGroup[$r['nama_barang']][] = [
    'tgl' => $r['tanggal'],
    'bongkar' => $r['tgl_bongkar'],
    'saldo' => $saldo,
    'krg' => floor($saldo/25),
    'add' => $saldo - (floor($saldo/25)*25),
    'ket' => $r['ket']
  ];
}

/* =========================
   OUTPUT
========================= */
?>
<style>
body {
  font-family: Calibri, Arial, sans-serif;
}

table {
  border-collapse: collapse;
  table-layout: auto; /* penting biar tidak melebar */
  width: 100%;
  font-family: Calibri, Arial, sans-serif;
}

th, td {
  border: 1px solid #000;
  padding: 5px;
  font-size: 12px;
}
th {
  background: #eaeaea;
  text-align: center;
  font-weight: bold;
}
td {
  text-align: center;
  vertical-align: middle;
  font-family: Calibri, sans-serif;
}

.td-brg {
  text-align: left;
}

.td-ket {
  text-align: left;
}

.td-total {
  text-align: right;
  font-weight: bold;
}

/* lebar kolom */

/* BARIS NAMA BARANG */
.barang-row td {
  background: #d9d9d9;
  font-weight: bold;
  border: none !important;
  padding: 8px;
}
.no-border td {
  border: none !important;
  font-weight: bold;
  text-align: center;
  background: #f2f2f2;
}
/* JUDUL */
.judul {
  text-align: center;
  font-weight: bold;
  font-size: 18px;
}

.subjudul {
  text-align: center;
  font-size: 14px;
}
.status-lolos {
  color: #5d5d5d;
  font-weight: bold;
}
</style>

<h4 class="judul">
  RINGKASAN BRIKET 
  <span style="color: <?= $warnaStatus ?>; font-weight:bold;">
    <?= strtoupper($status) ?>
  </span>
</h4>

<p class="subjudul">PT DIAN CIPTA SEJAHTERA</p>
<table border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse; table-layout:auto; width:auto;">

<colgroup>
  <col style="width:100px">  <!-- PRODUKSI -->
  <col style="width:100px">  <!-- BONGKAR -->
  <col style="width:50px">   <!-- KRG -->
  <col style="width:50px">   <!-- KG -->
  <col style="width:50px">   <!-- ADD -->
  <col style="width:120px">  <!-- JUMLAH -->
  <col style="width:120px">  <!-- TOTAL -->
  <col style="width:500px">  <!-- KETERANGAN -->
</colgroup>

<tr>
  <th rowspan="2">PRODUKSI</th>
  <th rowspan="2">BONGKAR OVEN</th>
  <th colspan="3">RINCIAN KARUNG</th>
  <th rowspan="2">JUMLAH</th>
  <th rowspan="2">TOTAL (KG)</th>
  <th rowspan="2">KETERANGAN</th>
</tr>
<tr>
  <th>KRG</th>
  <th>KG</th>
  <th>ADD</th>
</tr>

<?php
$currentBarang = '';
$totalBarang = 0;
$rowspan = 0;
$rowsBarang = [];
?>

<?php
foreach($dataGroup as $barang => $rows):

  $rowspan = count($rows);

  // HITUNG TOTAL BARANG DULU
  $totalBarang = 0;
  foreach($rows as $x){
    $totalBarang += $x['saldo'];
  }
?>

<tr class="barang-row">
  <td colspan="8" class="td-brg"><?= $barang ?></td>
</tr>

<?php
$first = true;
foreach($rows as $r):
?>

<tr>
  <td><?= date('d-m-Y', strtotime($r['tgl'])) ?></td>
  <td><?= $r['bongkar'] ? date('d-m-Y', strtotime($r['bongkar'])) : '-' ?></td>

  <td><?= $r['krg'] ?></td>
  <td>25</td>
  <td><?= number_format($r['add'],2) ?></td>

  <!-- JUMLAH PER PRODUKSI -->
  <td class="td-total" style="width:150px">
    <?= number_format($r['saldo'],2) ?>
  </td>

  <?php if($first): ?>
    <!-- TOTAL PER BARANG -->
    <td rowspan="<?= $rowspan ?>" class="td-total" style="width:150">
      <?= number_format($totalBarang,2) ?>
    </td>

    <!-- KETERANGAN -->
    <td rowspan="<?= $rowspan ?>" class="td-ket" style="width:500">
      <?= implode(', ', array_unique(array_column($rows,'ket'))) ?>
    </td>
  <?php endif; ?>

</tr>

<?php
$first = false;
endforeach;

$grandTotal += $totalBarang;
endforeach;
?>
</table>
<table style="width:100%">
  <tr>
    <td colspan="" style="border:0px solid #000; font-weight:bold; font-size: 40px;">TOTAL</td>
    <td style="border:0px solid #000; text-align:right; font-weight:bold; font-size: 40px;">
      <?= number_format($grandTotal,2) ?>
    </td>
  </tr>
</table>