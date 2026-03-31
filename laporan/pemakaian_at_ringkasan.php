<?php
require '../config/init.php';

function angka($n){
    return rtrim(rtrim(number_format((float)$n,2,'.',''), '0'),'.');
}

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$tglAwal  = $tahun.'-'.$bulan.'-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));

$id_barang = $_GET['id_barang'] ?? 0;
$id_barang = (int)$id_barang;

/* =========================
   AMBIL STOK MASUK AT
   ========================= */
// build base query and optionally filter by specific barang
$sql = "
SELECT 
    b.id_barang,
    b.nama_barang,
    m.tanggal,
    md.jumlah AS stok_asalan,

    (
        md.jumlah

        -

        IFNULL((
            SELECT SUM(ad.atp)
            FROM at_detail ad
            WHERE ad.id_barang = b.id_barang
            AND ad.tanggal <= m.tanggal
        ),0)

        -

        IFNULL((
            SELECT SUM(pd.mixer)
            FROM produksi_detail pd
            JOIN mutasi m2 ON m2.id_mutasi = pd.id_produksi
            WHERE pd.id_barang_atp = b.id_barang
            AND m2.tanggal <= m.tanggal
        ),0)

    ) AS sisa_powder

FROM mutasi_detail md
JOIN mutasi m ON m.id_mutasi = md.id_mutasi
JOIN barang b ON b.id_barang = md.id_barang
JOIN kelompok_barang kb ON kb.id_kelompok = b.id_kelompok

WHERE m.id_jenis = 5
AND kb.nama_kelompok = 'Powder'
";
if($id_barang > 0){
    $sql .= "\nAND b.id_barang = '$id_barang'\n";
}
$sql .= "\nORDER BY b.nama_barang, m.tanggal ASC\n";

$result = $conn->query($sql);

// collect tambahan entries within the same period (and optional item filter)
$adj = "
SELECT t.*, b.nama_barang
FROM tambahan t
LEFT JOIN barang b ON b.id_barang = t.id_barang
WHERE t.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
";
if($id_barang > 0){
    $adj .= " AND t.id_barang = '$id_barang'";
}
$adj .= "
ORDER BY t.tanggal ASC";

$resultAdj = $conn->query($adj);

// optional barang filter for fisik data
$whereBarang = '';
if($id_barang > 0){
    $whereBarang = " AND id_barang = '$id_barang'";
}
$qFisik = mysqli_query($conn,"
SELECT COALESCE(SUM(jumlah),0) as total_fisik
FROM stok_fisik_at
WHERE tanggal BETWEEN '$tglAwal' AND '$tglAkhir'" . $whereBarang . "
");

$dataFisik = mysqli_fetch_assoc($qFisik);
$totalFisik = $dataFisik['total_fisik'];

// prepare for detail listing (include barang name for supplier column)
$qFisikRows = mysqli_query($conn, "
SELECT s.tanggal, s.jumlah, s.keterangan, b.nama_barang
FROM stok_fisik_at s
LEFT JOIN barang b ON b.id_barang = s.id_barang
WHERE s.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'" . $whereBarang . "
ORDER BY s.tanggal ASC
");

// grandTotalSaldo will be computed after iterating $result rows below
$grandTotalSaldo = 0;
$totalAkhir = 0;

$qLast = mysqli_query($conn,"
    SELECT t.*, b.nama_barang
    FROM tambahan t
    LEFT JOIN barang b ON t.id_barang = b.id_barang
    ORDER BY t.tanggal DESC, t.id_tambahan DESC
    LIMIT 10
");

$namaBulan = [
"01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
"05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
"09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Ringkasan Bahan Baku AT</title>
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
body{
    font-family:Calibri, Arial;
    font-size:13px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}
.header-top{
    font-weight:bold;
}
.judul{
    background:#f4e3d7;
    font-size:18px;
    font-weight:bold;
}
.section{
    background:#d9ead3;
    font-weight:bold;
}
.total-row{
    background:#f2f2f2;
    font-weight:bold;
}
.card {
  border-radius: 10px;
}

table {
  font-size: 13px;
}

thead th {
  background: #1f2937;
  color: white;
  text-align: center;
}

tbody tr.group-row {
  background: #e9ecef;
  font-weight: bold;
}

tfoot {
  background: #d1d5db;
  font-weight: bold;
}
</style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

<div class="content-wrapper" style="margin-left:0;">
  <div class="content pt-4">
    <div class="container">

      <div class="card shadow-lg">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
  <a href="../index.php" class="btn btn-info btn-sm">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

<h4 class="mb-1"><b>Ringkasan Pemakaian AT</b></h4>
<small class="text-muted">
  Periode: <b><?= $namaBulan[$bulan] . ' ' . $tahun ?></b>
</small>

<hr>

<form method="GET" class="row g-2 mb-4">

  <div class="col-md-3">
    <label>Bulan</label>
    <select name="bulan" class="form-control">
      <?php foreach($namaBulan as $k => $v): ?>
        <option value="<?= $k ?>" <?= ($k==$bulan?'selected':'') ?>>
          <?= $v ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label>Tahun</label>
    <select name="tahun" class="form-control">
      <?php
        $thnNow = date('Y');
        for($t=$thnNow-3;$t<=$thnNow+1;$t++):
      ?>
        <option value="<?= $t ?>" <?= ($t==$tahun?'selected':'') ?>>
          <?= $t ?>
        </option>
      <?php endfor; ?>
    </select>
  </div>

  <div class="col-md-6 d-flex align-items-end gap-2">

    <button type="submit" class="btn btn-primary mr-2">
      Tampilkan
    </button>

    <a href="pemakaian_at_ringkasan_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-success mr-2">
      Export Excel
    </a>

    <a href="pemakaian_at_ringkasan_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-danger">
      Export PDF
    </a>

  </div>

</form>

<table>

<tr>
    <td colspan="6" class="judul">
        <b>BAHAN BAKU AT</b>
    </td>
</tr>

<tr>
    <th>NO</th>
    <th>SUPPLIER</th>
    <th>TGL TERIMA</th>
    <th>STOK (ASALAN)</th>
    <th>SISA POWDER</th>
    <th>KET</th>
</tr>

<?php
$no = 1;

$total_stok = 0;
$total_sisa = 0;

while($row = $result->fetch_assoc()) {

    $stok = (int)$row['stok_asalan'];
    $sisa = (int)$row['sisa_powder'];

    $total_stok += $stok;
    $total_sisa += $sisa;

    // accumulate grand total for saldo, so we can subtract fisik later
    $grandTotalSaldo += $sisa;

    // KETERANGAN
    $ket = ($sisa <= 0) ? "HABIS" : "READY";

    echo "<tr>";
    echo "<td>".$no++."</td>";
    echo "<td>".$row['nama_barang']."</td>";
    echo "<td>".$row['tanggal']."</td>";
    echo "<td align='right'>".number_format($row['stok_asalan'],0)."</td>";
    echo "<td align='right'>".number_format($row['sisa_powder'],0)."</td>";
    echo "<td align='center'>".$ket."</td>";
    echo "</tr>";
}
// now that grandTotalSaldo is available, calculate totalAkhir
$totalAkhir = $grandTotalSaldo - $totalFisik;
?>

<tr style="font-weight:bold; background:#f0f0f0;">
    <td colspan="3" align="center">JUMLAH</td>
    <td align="right"><?php echo number_format($total_stok,0); ?></td>
    <td align="right"><?php echo number_format($total_sisa,0); ?></td>
    <td></td>
</tr>

<?php
echo "
<tr style='background:#d9e1c3; font-weight:bold;'>
    <td colspan='6' class='judul'><b>STOK AT FISIK HABIS</b></td>
</tr>";

// list actual fisik entries
if($qFisikRows && mysqli_num_rows($qFisikRows) > 0){
    $noF = 1;
    while($f = mysqli_fetch_assoc($qFisikRows)){
        echo "<tr>";
        echo "<td>".$noF++."</td>";
        // supplier column: use barang name from stok_fisik_at
        echo "<td>".htmlspecialchars($f['nama_barang'] ?? '-')."</td>";
        echo "<td>".$f['tanggal']."</td>";
        echo "<td >-</td>";
        echo "<td align='right'>".number_format($f['jumlah'],0)."</td>";
        echo "<td>".htmlspecialchars($f['keterangan'])."</td>";
        echo "</tr>";
    }
    // subtotal for fisik
    echo "<tr style='font-weight:bold; background:#f0f0f0;'>
            <td colspan='3' align='center'>JUMLAH</td>
            <td></td>
            <td align='right'>".number_format($totalFisik,0)."</td>
            <td></td>
          </tr>";
} else {
    echo "<tr><td colspan='6' class='text-center'>Belum ada stok fisik pada periode ini</td></tr>";
}

// render tambahan section (still inside PHP)
echo "
<tr style='background:#cfd8dc; font-weight:bold;'>
    <td colspan='6' class='judul'><b>TAMBAHAN</b></td>
</tr>";

// heading row for tambahan entries
echo "
<tr>
    <th>No</th>
    <th colspan='3'>Nama Barang</th>
    <th>Jumlah</th>
    <th>Keterangan</th>
</tr>";

if($resultAdj && $resultAdj->num_rows > 0){
    $noT = 1;
    while($t = $resultAdj->fetch_assoc()){
        echo "<tr>";
        echo "<td>" . $noT++ . "</td>";
        echo "<td colspan='3'>" . htmlspecialchars($t['nama_barang'] ?? '-') . "</td>";
        echo "<td align='right'>" . number_format((float)$t['jumlah'],0) . "</td>";
        echo "<td>" . htmlspecialchars($t['keterangan']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>Belum ada data tambahan pada periode ini</td></tr>";
}
?>

<?php if(isset($totalAkhir)): ?>
<div class="mt-3">
    <strong>Saldo akhir setelah fisik: <?= number_format($totalAkhir,0) ?></strong>
</div>
<?php endif; ?>

</table>

<script>
// allow pressing Enter in an editable cell to append a new blank row below
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    if (!table) return;
    table.addEventListener('keydown', function(e) {
        const td = e.target;
        if (td && td.isContentEditable && e.key === 'Enter') {
            e.preventDefault();
            const tr = td.closest('tr');
            if (!tr) return;
            const newTr = tr.cloneNode(true);
            newTr.querySelectorAll('[contenteditable]').forEach(cell => cell.textContent = '');
            tr.parentNode.insertBefore(newTr, tr.nextSibling);
            // focus same column in new row
            const cells = Array.from(tr.querySelectorAll('td'));
            const idx = cells.indexOf(td);
            const newCells = Array.from(newTr.querySelectorAll('td'));
            if (newCells[idx]) {
                newCells[idx].focus();
            }
            recalcTotals();
        }
    });
});
</script>

      </div>
      </div>

    </div>
  </div>
</div>

</div>
</body>
</html>