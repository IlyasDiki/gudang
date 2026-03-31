<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$sql = "SELECT 
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
ORDER BY b.nama_barang, m.tanggal ASC";

$result = $conn->query($sql);

$html = "<h3>Ringkasan Bahan Baku AT</h3>";
$html .= "<table border='1' width='100%' cellpadding='5' cellspacing='0'>";
$html .= "<tr>
    <td colspan='6' class='judul'>
        <b>BAHAN BAKU AT</b>
    </td>
</tr>";
$html .= "<tr>
<th>NO</th>
<th>SUPPLIER</th>
<th>TGL TERIMA</th>
<th>STOK</th>
<th>SISA</th>
<th>KET</th>
</tr>";

$no = 1;
$total_stok = 0;
$total_sisa = 0;

while($row = $result->fetch_assoc()){

    $stok = (int)$row['stok_asalan'];
    $sisa = (int)$row['sisa_powder'];
    $ket = ($sisa <= 0) ? "HABIS" : "READY";

    $total_stok += $stok;
    $total_sisa += $sisa;

    $html .= "<tr>
    <td>$no</td>
    <td>{$row['nama_barang']}</td>
    <td>{$row['tanggal']}</td>
    <td>$stok</td>
    <td>$sisa</td>
    <td>$ket</td>
    </tr>";

    $no++;
}

$html .= "

<tr>
<td colspan='3'><b>JUMLAH</b></td>
<td><b>$total_stok</b></td>
<td><b>$total_sisa</b></td>
<td></td>
</tr>";

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Ringkasan_AT_{$bulan}_{$tahun}.pdf");