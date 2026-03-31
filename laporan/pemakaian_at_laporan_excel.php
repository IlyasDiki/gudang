<?php
require '../config/init.php';

function angka($nilai) {
  return rtrim(rtrim(number_format((float)$nilai, 2, '.', ''), '0'), '.');
}

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$tglAwal = $tahun . '-' . $bulan . '-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pemakaian_AT_{$bulan}_{$tahun}.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* ==========================
   AMBIL DATA PEMAKAIAN AT
========================== */
$q = mysqli_query($conn, "
  SELECT
    d.tanggal,
    d.sortir,
    d.ma,
    d.aa,
    d.b_mentah,
    d.air,
    d.atp
  FROM at_detail d
  WHERE DATE(d.tanggal) BETWEEN '$tglAwal' AND '$tglAkhir'
  ORDER BY d.tanggal ASC
");

$data = [];
$total = [
  'sortir'=>0,'ma'=>0,'aa'=>0,'b_mentah'=>0,'air'=>0,'atp'=>0
];

while ($row = mysqli_fetch_assoc($q)) {
  $row['sortir']   = (float)$row['sortir'];
  $row['ma']       = (float)$row['ma'];
  $row['aa']       = (float)$row['aa'];
  $row['b_mentah'] = (float)$row['b_mentah'];
  $row['air']      = (float)$row['air'];
  $row['atp']      = (float)$row['atp'];

  $data[] = $row;

  foreach ($total as $k => $v) {
    $total[$k] += $row[$k];
  }
}

/* ==========================
   AMBIL DATA PRODUKSI
========================== */
$qProd = mysqli_query($conn, "
  SELECT 
    p.tanggal,
    pd.mixer
  FROM produksi p
  JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
  WHERE DATE(p.tanggal) BETWEEN '$tglAwal' AND '$tglAkhir'
  ORDER BY p.tanggal ASC
");

$dataProd = [];
while ($row = mysqli_fetch_assoc($qProd)) {
  $row['mixer'] = (float)$row['mixer'];
  $dataProd[] = $row;
}

/* ==========================
   HITUNG RINGKASAN (SAMA PERSIS)
========================== */
$jumlah_awal = 2000;

$total_pakai = array_sum($total);

// rumus sesuai laporan web kamu
$susut = ($jumlah_awal - $total['sortir']) / $jumlah_awal;

$kand_ma = $jumlah_awal > 0 ? ($total['ma'] / $jumlah_awal) * 100 : 0;
$kand_aa = $jumlah_awal > 0 ? ($total['aa'] / $jumlah_awal) * 100 : 0;
$kand_bm = $jumlah_awal > 0 ? ($total['b_mentah'] / $jumlah_awal) * 100 : 0;
$total_susut = $jumlah_awal > 0 ? ($susut / $jumlah_awal) * 100 : 0;

/* ==========================
   ROWSPAN
========================== */
$rowsAT = count($data);
$rowsProd = count($dataProd);
$maxRows = max($rowsAT, $rowsProd);
if ($maxRows < 1) $maxRows = 1;

/* saldo berjalan AT */
$saldoAT = $jumlah_awal;

/* tanggal terima */
$tgl_terima = $rowsAT > 0 ? $data[0]['tanggal'] : '-';

/* total ATP untuk kolom jumlah produksi */
$totalATP = (float)$total['atp'];

/* saldo produksi awal = totalATP */
$saldoProd = $totalATP;
?>

<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse; font-family:Arial; font-size:12px;">

  <tr>
    <td colspan="20" style="font-weight:bold; font-size:14px;">LAPORAN PEMAKAIAN AT</td>
  </tr>
  <tr>
    <td colspan="20">Periode: <?= date('F Y', strtotime($tglAwal)) ?></td>
  </tr>

  <tr style="font-weight:bold; text-align:center;">
    <td rowspan="2">NO</td>
    <td rowspan="2">TGL TERIMA</td>
    <td rowspan="2">JUMLAH</td>
    <td colspan="13">PEMAKAIAN AT</td>
    <td colspan="4">PEMAKAIAN PRODUKSI</td>
  </tr>

  <tr style="font-weight:bold; text-align:center;">
    <td>TGL</td>
    <td>SORTIR</td>
    <td>SALDO</td>
    <td>MA</td>
    <td>AA</td>
    <td>B.MENTAH</td>
    <td>AIR</td>
    <td>ATP</td>
    <td>Kand. MA</td>
    <td>Kand. AA</td>
    <td>Kand. BM</td>
    <td>Susut Timb.</td>
    <td>Total Susut</td>

    <td>JUMLAH</td>
    <td>TGL</td>
    <td>MIXER</td>
    <td>SALDO</td>
  </tr>

  <?php for ($i=0; $i<$maxRows; $i++): ?>
    <?php
      $dAT = $data[$i] ?? null;
      $dP  = $dataProd[$i] ?? null;

      if ($dAT) {
        $pakai_row = $dAT['sortir'] + $dAT['ma'] + $dAT['aa'] + $dAT['b_mentah'] + $dAT['air'] + $dAT['atp'];
        $saldoAT -= $pakai_row;
      }

      // saldo produksi jalan (harus berlanjut, bukan reset tiap loop)
      if ($dP) {
        $saldoProd -= (float)$dP['mixer'];
      }
    ?>

    <tr>

      <?php if ($i==0): ?>
        <td rowspan="<?= $maxRows ?>">1</td>
        <td rowspan="<?= $maxRows ?>"><?= $tgl_terima ?></td>
        <td rowspan="<?= $maxRows ?>"><?= angka($jumlah_awal) ?></td>
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

      <?php if ($i==0): ?>
        <td rowspan="<?= $maxRows ?>"><?= round($kand_ma,2) ?>%</td>
        <td rowspan="<?= $maxRows ?>"><?= round($kand_aa,2) ?>%</td>
        <td rowspan="<?= $maxRows ?>"><?= round($kand_bm,2) ?>%</td>
        <td rowspan="<?= $maxRows ?>"><?= angka($susut) ?>%</td>
        <td rowspan="<?= $maxRows ?>"><?= round($total_susut,2) ?>%</td>
      <?php endif; ?>

      <!-- PEMAKAIAN PRODUKSI -->
      <td><?= $dP ? angka($totalATP) : '' ?></td>
      <td><?= $dP ? $dP['tanggal'] : '' ?></td>
      <td><?= $dP ? angka($dP['mixer']) : '' ?></td>
      <td><?= $dP ? angka($saldoProd) : '' ?></td>

    </tr>
  <?php endfor; ?>

  <!-- TOTAL -->
  <tr style="font-weight:bold;">
    <td colspan="4">TOTAL</td>
    <td><?= angka($total['sortir']) ?></td>
    <td>-</td>
    <td><?= angka($total['ma']) ?></td>
    <td><?= angka($total['aa']) ?></td>
    <td><?= angka($total['b_mentah']) ?></td>
    <td><?= angka($total['air']) ?></td>
    <td><?= angka($total['atp']) ?></td>

    <td><?= round($kand_ma,2) ?>%</td>
    <td><?= round($kand_aa,2) ?>%</td>
    <td><?= round($kand_bm,2) ?>%</td>
    <td><?= angka($susut) ?></td>
    <td><?= round($total_susut,2) ?>%</td>

    <td colspan="4"></td>
  </tr>

</table>
