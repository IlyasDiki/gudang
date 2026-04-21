<?php
require '../config/init.php';

header('Content-Type: application/json');

$idBarang   = $_GET['id_barang'] ?? 0;
$idSupplier = $_GET['id_supplier'] ?? 0;

$data = [];

/* ======================
   AMBIL STOK PER BATCH
====================== */

$q = mysqli_query($conn, "
SELECT 
    md.id_detail,
    md.tanggal,
    md.jumlah,
    
    (
        md.jumlah -
        IFNULL((
            SELECT SUM(
                ad.sortir + ad.ma + ad.aa + ad.b_mentah + ad.air + ad.atp
            )
            FROM at_detail ad
            WHERE ad.id_mutasi_detail = md.id_detail
        ),0)
    ) AS sisa

FROM mutasi_detail md
WHERE md.id_barang = '$idBarang'
AND md.id_supplier = '$idSupplier'
HAVING sisa > 0
ORDER BY md.tanggal ASC
");

while($row = mysqli_fetch_assoc($q)){

    $data[] = [
        'id'      => $row['id_detail'],
        'tanggal' => $row['tanggal'],
        'saldo'   => number_format($row['sisa'],2,'.','')
    ];
}

echo json_encode($data);