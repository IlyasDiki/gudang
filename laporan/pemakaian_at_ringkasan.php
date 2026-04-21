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
$sql = "
SELECT 
    d.id_supplier,
    s.nama_supplier,

    MAX(d.tanggal) as tanggal_terakhir,

    /* stok awal dari mutasi */
    MAX(md.jumlah) as stok_awal,

    /* sisa arang */
    (MAX(md.jumlah) - SUM(d.sortir)) as sisa_at,

    /* sisa powder */
    (
        SUM(d.atp)
        -
        IFNULL((
            SELECT SUM(pd.mixer)
            FROM produksi_detail pd
            WHERE pd.id_mutasi_detail = d.id_mutasi_detail
        ),0)
    ) as sisa_produksi

FROM at_detail d
JOIN mutasi_detail md ON md.id_detail = d.id_mutasi_detail
LEFT JOIN supplier s ON s.id_supplier = d.id_supplier

WHERE d.tanggal <= '$tglAkhir'

GROUP BY d.id_mutasi_detail

HAVING sisa_at > 0 OR sisa_produksi > 0

ORDER BY tanggal_terakhir DESC
";

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
SELECT 
    s.tanggal, 
    s.jumlah, 
    s.keterangan, 
    sp.nama_supplier
FROM stok_fisik_at s
LEFT JOIN supplier sp ON sp.id_supplier = s.id_supplier
WHERE s.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
ORDER BY s.tanggal ASC
");

if(!$qFisikRows){
    die("Query error: " . mysqli_error($conn));
}

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
th{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}
td{
  border:1px solid #000;
  padding:6px;
    text-align:left;
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
<p class="text-muted">
  PER: <b><?= $namaBulan[$bulan] . ' ' . $tahun ?></b>
</p>

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
    <th colspan="8" class="judul">
        <b>BAHAN BAKU AT</b>
    </th>
</tr>

<tr>
    <th>NO</th>
    <th>SUPPLIER</th>
    <th>TGL TERIMA</th>
    <th colspan="2">STOK (ASALAN)</th>
    <th colspan="2">SISA POWDER</th>
    <th>KET</th>
</tr>

<tr>
    <th colspan="8">
        <b>STOK AT READY</b>
    </th>
</tr>
<?php
$no = 1;

$total_stok = 0;
$total_sisa = 0;

while($row = $result->fetch_assoc()) {

    $stok = (int)$row['stok_awal']; // ⬅️ dari query
    $sisa = (int)$row['sisa_produksi'];

    $total_stok += $stok;
    $total_sisa += $sisa;

    // accumulate grand total for saldo, so we can subtract fisik later
    $grandTotalSaldo += $sisa;

    // KETERANGAN
   //$ket = ($sisa <= 0) ? "HABIS" : "READY";

    echo "<tr>";
    echo "<td>".$no++."</td>";
    echo "<td>".htmlspecialchars($row['nama_supplier'] ?? '-')."</td>";
    echo "<td>".date('j-M-Y', strtotime($row['tanggal_terakhir']))."</td>";
    echo "<td align='right'>".number_format($row['sisa_at'],0)."</td>";
    echo "<td align='right' width='1%'>Kg</td>";
    echo "<td align='right'>".number_format($row['sisa_produksi'],0)."</td>";
    echo "<td align='right' width='1%'>Kg</td>";
    echo "<td align='center'></td>";
    echo "</tr>";
}
// now that grandTotalSaldo is available, calculate totalAkhir
$totalAkhir = $grandTotalSaldo - $totalFisik;
?>

<tr style="font-weight:bold; background:#f0f0f0;">
    <th colspan="3" align="center">JUMLAH</th>
    <td align="right"><?php echo number_format($total_stok,0); ?></td>
    <td>Kg</td>
    <td align="right"><?php echo number_format($total_sisa,0); ?></td>
    <td>Kg</td>
    <td></td>
</tr>

<?php
echo "
<tr style='font-weight:bold;'>
    <th colspan='8'><b>STOK AT FISIK HABIS</b></th>
</tr>";

// list actual fisik entries
if($qFisikRows && mysqli_num_rows($qFisikRows) > 0){
    $noF = 1;
    while($f = mysqli_fetch_assoc($qFisikRows)){
        echo "<tr>";
        echo "<td>".$noF++."</td>";
        echo "<td>".htmlspecialchars($f['nama_supplier'] ?? '-')."</td>";
        echo "<td>".date('j-M-Y', strtotime($f['tanggal']))."</td>";
        echo "<td align='right'>".number_format($f['jumlah'],0)."</td>";
        echo "<td align='right' width='1%'>Kg</td>";
        echo "<td></td>";
        echo "<td align='right' width='1%'>Kg</td>";
        echo "<td>".htmlspecialchars($f['keterangan'])."</td>";
        echo "</tr>";
    }

    echo "<tr style='font-weight:bold; background:#f0f0f0;'>
            <th colspan='3' align='center'>JUMLAH</th>
            <td align='right'>".number_format($totalFisik,0)."</td>
            <td width='1%'>Kg</td>
            <td></td>
            <td width='1%'>Kg</td>
            <td></td>
          </tr>";
} else {
    echo "<tr><td colspan='8' class='text-center'>Belum ada stok fisik pada periode ini</td></tr>";
}

/* =========================
   TOTAL GLOBAL
========================= */
$totalGlobalStok = $total_stok + $totalFisik;
$totalGlobalSisa = $total_sisa;

echo "<tr style='background:#d9d9d9; font-weight:bold;'>
    <th colspan='3'><b>TOTAL</b></th>
    <td><b>".number_format($totalGlobalStok,0)."</b></td>
    <td width='1%'>Kg</td>
    <td><b>".number_format($totalGlobalSisa,0)."</b></td>
    <td width='1%'>Kg</td>
    <td></td>
</tr>";

// render tambahan section (still inside PHP)
echo "
<tr style='background:#cfd8dc; font-weight:bold;'>
    <td colspan='8' class='judul'><b>TAMBAHAN</b></td>
</tr>";

// heading row for tambahan entries

if($resultAdj && $resultAdj->num_rows > 0){
    $noT = 1;
    while($t = $resultAdj->fetch_assoc()){
        echo "<tr>";
        echo "<td>" . $noT++ . "</td>";
        echo "<td colspan='4'>" . htmlspecialchars($t['nama_barang'] ?? '-') . "</td>";
        echo "<td align='right'>" . number_format((float)$t['jumlah'],0) . "</td>";
        echo "<td align='right' width='1%'>Kg</td>";
        echo "<td>" . htmlspecialchars($t['keterangan']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>Belum ada data tambahan pada periode ini</td></tr>";
}
?>

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