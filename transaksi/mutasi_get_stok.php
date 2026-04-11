<?php
require '../config/init.php';

$id_barang = $_GET['id_barang'] ?? 0;
$id_supplier = $_GET['id_supplier'] ?? 0;

$whereSupplier = "";

if ($id_supplier) {
    $whereSupplier = "AND md.id_supplier = '$id_supplier'";
}

$q = mysqli_query($conn, "
SELECT 
    IFNULL(SUM(
        CASE 
            WHEN m.arah = 'MASUK' THEN md.jumlah
            WHEN m.arah = 'KELUAR' THEN -md.jumlah
            ELSE 0
        END
    ),0) AS stok
FROM mutasi_detail md
JOIN mutasi m ON m.id_mutasi = md.id_mutasi
WHERE md.id_barang = '$id_barang'
$whereSupplier
");

$data = mysqli_fetch_assoc($q);

echo json_encode([
    "stok" => $data['stok'] ?? 0
]);