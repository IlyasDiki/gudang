<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;


/* =========================
   PARAMETER
========================= */
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$tglAwal  = $tahun.'-'.$bulan.'-01';
$tglAkhir = date('Y-m-t', strtotime($tglAwal));

/* =========================
   QUERY AT READY (SAMA EXCEL)
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

/* =========================
   HITUNG TOTAL & GROUP DATA
========================= */
$total_stok = 0;
$total_sisa = 0;
$grandTotalSaldo = 0;

$dataGroup = [];

while($row = $result->fetch_assoc()){
    $supplier = $row['nama_supplier'] ?? '-';
    $dataGroup[$supplier][] = $row;
    $total_stok += (int)$row['stok_awal'];
    $total_sisa += (int)$row['sisa_produksi'];
    $grandTotalSaldo += (int)$row['sisa_produksi'];
}

$totalAkhir = $grandTotalSaldo - $totalFisik;
$totalGlobalStok = $total_stok + $totalFisik;
$totalGlobalSisa = $total_sisa;

/* =========================
   HTML PDF
========================= */
$html = '
<style>
body{ font-family: Calibri; font-size:12px; }
table{ border-collapse:collapse; width:100%; }
th, td{ border:1px solid #000; padding:6px; }
th{ text-align:center; }
.judul{ font-size:16px; font-weight:bold; text-align:center; }
.header{ font-weight:bold; text-align:center; }
.no-border th, .no-border td { border:none; padding:6px; }
</style>

<h3>Ringkasan</h3>
<p><i>PER</i>: '.$bulan.'-'.$tahun.'</p>

<table>

<tr>
<th colspan="8" class="judul">BAHAN BAKU AT</th>
</tr>

<tr class="header">
<th>NO</th>
<th>SUPPLIER</th>
<th>TGL TERIMA</th>
<th colspan="2">STOK</th>
<th colspan="2">SISA</th>
<th>KET</th>
</tr>

<tr class="header">
<th colspan="8">STOK AT READY</th>
</tr>
';

/* =========================
   LOOP AT READY
========================= */
$no=1;
foreach($dataGroup as $supplier => $rows){
    $first = true;
    
    foreach($rows as $row){
        $html .= '
<tr>
<td>'.($first ? $no++ : '').'</td>
<td>'.($first ? $row['nama_supplier'] : '').'</td>
<td>'.date('j-M-Y', strtotime($row['tanggal_terakhir'])).'</td>
<td align="right">'.number_format($row['sisa_at'],0).'</td>
<td>Kg</td>
<td align="right">'.number_format($row['sisa_produksi'],0).'</td>
<td>Kg</td>
<td></td>
</tr>';
        $first = false;
    }
}

$html .= '
<tr class="header">
<td colspan="3">JUMLAH</td>
<td>'.number_format($total_stok,0).'</td>
<td>Kg</td>
<td>'.number_format($total_sisa,0).'</td>
<td>Kg</td>
<td></td>
</tr>

/* =========================
   FISIK
========================= */
<tr class="header">
<td colspan="8">STOK AT FISIK HABIS</td>
</tr>
';

if(mysqli_num_rows($qFisikRows) > 0){
$noF=1;
while($f = mysqli_fetch_assoc($qFisikRows)){

$html .= '
<tr>
<td>'.$noF++.'</td>
<td>'.$f['nama_supplier'].'</td>
<td>'.date('j-M-Y', strtotime($f['tanggal'])).'</td>
<td align="right">'.number_format($f['jumlah'],0).'</td>
<td>Kg</td>
<td></td>
<td>Kg</td>
<td>'.$f['keterangan'].'</td>
</tr>';
}

$html .= '
<tr class="header">
<td colspan="3">JUMLAH</td>
<td>'.number_format($totalFisik,0).'</td>
<td>Kg</td>
<td></td>
<td>Kg</td>
<td></td>
</tr>';
}

$html .= '
<tr class="header">
<td colspan="3">TOTAL</td>
<td>'.number_format($totalGlobalStok,0).'</td>
<td>Kg</td>
<td>'.number_format($totalGlobalSisa,0).'</td>
<td>Kg</td>
<td></td>
</tr>

/* =========================
   TAMBAHAN
========================= */
<tr class="header">
<td colspan="8">TAMBAHAN</td>
</tr>
';

if(mysqli_num_rows($qTambahan) > 0){
$noT=1;
while($t = mysqli_fetch_assoc($qTambahan)){

$html .= '
<tr>
<td>'.$noT++.'</td>
<td colspan="4">'.$t['nama_barang'].'</td>
<td>'.number_format($t['jumlah'],0).'</td>
<td>Kg</td>
<td>'.$t['keterangan'].'</td>
</tr>';
}
}

$html .= '</table>';



// ================================
// DOMPDF CONFIG
// ================================
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'potrait');
$dompdf->render();

// ================================
// OUTPUT PDF
// ================================
$dompdf->stream("Ringkasan AT_$bulan-$tahun.pdf", ["Attachment"=>0]);