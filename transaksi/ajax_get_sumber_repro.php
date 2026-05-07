<?php
require '../config/init.php';
header('Content-Type: application/json');

/* ambil hasil produksi yang belum dipakai lagi */
$q = mysqli_query($conn, "
SELECT 
    p.id_produksi,
    p.tanggal,
    SUM(pd.mixer) as total_produksi,

    IFNULL((
        SELECT SUM(pd2.mixer)
        FROM produksi_detail pd2
        WHERE pd2.id_mutasi_detail = p.id_produksi
    ),0) as sudah_pakai

FROM produksi p
JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi

GROUP BY p.id_produksi
HAVING total_produksi - sudah_pakai > 0
ORDER BY p.tanggal ASC
");

$data = [];

while($row = mysqli_fetch_assoc($q)){
    $sisa = $row['total_produksi'] - $row['sudah_pakai'];

    $data[] = [
        'id' => $row['id_produksi'],
        'label' => date('d M Y', strtotime($row['tanggal'])),
        'saldo' => $sisa,
        'total_atp' => $row['total_produksi'],
        'total_mixer' => $row['sudah_pakai'],
        'saldo_atp' => $sisa
    ];
}

echo json_encode([
    'status' => 'ok',
    'stok' => $data
]);