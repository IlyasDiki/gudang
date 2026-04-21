<?php
require '../config/init.php';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$tglAwal  = $tahun.'-'.$bulan.'-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Ringkasan_AT_{$bulan}_{$tahun}.xls");

/* =========================
   QUERY AT READY
========================= */
$sql = "
SELECT 
    d.id_supplier,
    s.nama_supplier,
    MAX(d.tanggal) as tanggal_terakhir,
    MAX(md.jumlah) as stok_awal,
    (MAX(md.jumlah) - SUM(d.sortir)) as sisa_at,
    (
        SUM(d.atp)
        - IFNULL((
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

/* =========================
   STOK FISIK
========================= */
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

$qFisikSum = mysqli_query($conn, "
SELECT COALESCE(SUM(jumlah),0) as total_fisik
FROM stok_fisik_at
WHERE tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
");

$totalFisik = mysqli_fetch_assoc($qFisikSum)['total_fisik'];

/* =========================
   TAMBAHAN
========================= */
$qTambahan = mysqli_query($conn, "
SELECT t.*, b.nama_barang
FROM tambahan t
LEFT JOIN barang b ON b.id_barang = t.id_barang
WHERE t.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
ORDER BY t.tanggal ASC
");

$namaBulan = [
"01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
"05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
"09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

/* =========================
   HITUNG TOTAL
========================= */
$total_stok = 0;
$total_sisa = 0;

$dataAT = [];
while($row = $result->fetch_assoc()){
    $total_stok += (int)$row['stok_awal'];
    $total_sisa += (int)$row['sisa_produksi'];
    $dataAT[] = $row;
}

$totalGlobalStok = $total_stok + $totalFisik;
$totalGlobalSisa = $total_sisa;
?>

<style>
tr.section-title th {
    border-top: 2px solid black;
    border-bottom: 2px solid black;
}
/* Judul utama: Ringkasan */
.font-ringkasan {
    font-family: Broadway, sans-serif;
    font-size: 18px;
    font-weight: normal;
}

/* Judul tabel: BAHAN BAKU AT */
.font-judul {
    font-family: "Britannic Bold", sans-serif;
    font-size: 20px;
    font-weight: bold;
}

/* Default isi tabel */
.font-isi {
    font-family: Calibri, Arial, sans-serif;
    font-size: 15px;
}

/* Header tabel */
.font-header {
    font-family: Calibri, Arial, sans-serif;
    font-size: 17px;
    font-weight: bold;
}
</style>

<h4 class="mb-1 font-ringkasan"><b>Ringkasan</b></h4>
<p class="text-muted">
  <i>PER</i> : <b><?= $namaBulan[$bulan] . ' ' . $tahun ?></b>
</p>
<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse; table-layout:auto; width:auto;">
    <colgroup>
    <col style="width:40px">   <!-- NO -->
    <col style="width:140px">  <!-- SUPPLIER (lebih lebar) -->
    <col style="width:100px">  <!-- TGL -->
    <col style="width:120px">  <!-- STOK -->
    <col style="width:30px">   <!-- Kg -->
    <col style="width:120px">  <!-- SISA -->
    <col style="width:30px">   <!-- Kg -->
    <col style="width:140px">  <!-- KETERANGAN -->
</colgroup>
<tr>
    <th colspan="8" class="font-judul"><b>BAHAN BAKU AT</b></th>
</tr>

<tr class="font-header" border="2">
    <th>NO</th>
    <th>SUPPLIER</th>
    <th>TGL TERIMA</th>
    <th colspan="2">STOK (ASALAN)</th>
    <th colspan="2">SISA POWDER</th>
    <th>KET</th>
</tr>

<tr class="font-header">
    <th colspan="8">STOK AT READY</th>
</tr>

<?php $no=1; foreach($dataAT as $row): ?>
<tr class="font-isi">
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
    <td><?= date('j-M-Y', strtotime($row['tanggal_terakhir'])) ?></td>
    <td><?= number_format($row['sisa_at'],0) ?></td>
    <td>Kg</td>
    <td><?= number_format($row['sisa_produksi'],0) ?></td>
    <td>Kg</td>
    <td></td>
</tr>
<?php endforeach; ?>

<tr class="font-header">
    <th colspan="3">JUMLAH</th>
    <td><?= number_format($total_stok,0) ?></td>
    <td>Kg</td>
    <td><?= number_format($total_sisa,0) ?></td>
    <td>Kg</td>
    <td></td>
</tr>

<tr class="font-header">
    <th colspan="8">STOK AT FISIK HABIS</th>
</tr>

<?php if(mysqli_num_rows($qFisikRows) > 0): $noF=1; ?>
<?php while($f = mysqli_fetch_assoc($qFisikRows)): ?>
<tr class="font-isi">
    <td><?= $noF++ ?></td>
    <td><?= htmlspecialchars($f['nama_supplier'] ?? '-') ?></td>
    <td><?= date('j-M-Y', strtotime($f['tanggal'])) ?></td>
    <td><?= number_format($f['jumlah'],0) ?></td>
    <td>Kg</td>
    <td></td>
    <td>Kg</td>
    <td><?= htmlspecialchars($f['keterangan']) ?></td>
</tr>
<?php endwhile; ?>

<tr class="font-header">
    <th colspan="3">JUMLAH</th>
    <td><?= number_format($totalFisik,0) ?></td>
    <td>Kg</td>
    <td></td>
    <td>Kg</td>
    <td></td>
</tr>

<?php else: ?>
<tr>
    <td colspan="8">Belum ada data fisik</td>
</tr>
<?php endif; ?>

<tr class="font-header">
    <th colspan="3">TOTAL</th>
    <td><?= number_format($totalGlobalStok,0) ?></td>
    <td>Kg</td>
    <td><?= number_format($totalGlobalSisa,0) ?></td>
    <td>Kg</td>
    <td></td>
</tr>

<tr class="font-header">
    <th colspan="8">TAMBAHAN</th>
</tr>

<?php if(mysqli_num_rows($qTambahan) > 0): $noT=1; ?>
<?php while($t = mysqli_fetch_assoc($qTambahan)): ?>
<tr class="font-isi">
    <td><?= $noT++ ?></td>
    <td colspan="4"><?= htmlspecialchars($t['nama_barang'] ?? '-') ?></td>
    <td><?= number_format($t['jumlah'],0) ?></td>
    <td>Kg</td>
    <td><?= htmlspecialchars($t['keterangan']) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="8">Belum ada data tambahan</td>
</tr>
<?php endif; ?>

</table>