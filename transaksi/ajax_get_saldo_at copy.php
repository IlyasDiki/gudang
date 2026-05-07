<?php
require '../config/init.php';

header('Content-Type: application/json');

$idBarang   = $_GET['id_barang'] ?? null;
$idSupplier = $_GET['id_supplier'] ?? null;

if (!$idBarang || !$idSupplier) {
    echo json_encode(['status'=>'error', 'message' => 'Parameter tidak lengkap']);
    exit;
}

/* ===================================================
   1. STOK SUPPLIER BIASA
=================================================== */

$q = mysqli_query($conn, "
SELECT 
    md.id_detail,
    m.tanggal,
    md.jumlah,

    (
        md.jumlah -
        IFNULL((
            SELECT SUM(sortir+ma+aa+b_mentah+air+atp)
            FROM at_detail
            WHERE id_mutasi_detail = md.id_detail
        ),0)
    ) AS sisa

FROM mutasi_detail md
JOIN mutasi m ON m.id_mutasi = md.id_mutasi

WHERE md.id_barang = '$idBarang'
AND md.id_supplier = '$idSupplier'
AND m.arah = 'MASUK'
HAVING sisa > 0
ORDER BY m.tanggal ASC
");

if (!$q) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query error: ' . mysqli_error($conn),
        'debug' => ['idBarang' => $idBarang, 'idSupplier' => $idSupplier]
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($q)) {
    $data[] = [
        'id' => $row['id_detail'],
        'saldo' => $row['sisa'],
        'label' => date('d M Y', strtotime($row['tanggal']))
    ];
}


echo json_encode([
    'status' => 'ok',
    'stok' => $data,
    'debug' => ['total_found' => count($data)]
]);