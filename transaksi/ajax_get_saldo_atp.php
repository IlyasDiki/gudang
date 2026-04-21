<?php
require '../config/init.php';

header('Content-Type: application/json');

$idSupplier = $_GET['id_supplier'] ?? null;

if(!$idSupplier){
    echo json_encode(['status'=>'error','msg'=>'supplier kosong']);
    exit;
}

/* =========================
   TOTAL ATP MASUK
========================= */
$qMasuk = mysqli_query($conn,"
SELECT IFNULL(SUM(atp),0) as masuk
FROM at_detail
WHERE id_supplier='$idSupplier'
");

$masuk = mysqli_fetch_assoc($qMasuk)['masuk'] ?? 0;

/* =========================
   TOTAL MIXER (KELUAR)
========================= */
$qKeluar = mysqli_query($conn,"
SELECT IFNULL(SUM(pd.mixer),0) as keluar
FROM produksi_detail pd
JOIN produksi p ON p.id_produksi=pd.id_produksi
WHERE p.id_supplier='$idSupplier'
");

$keluar = mysqli_fetch_assoc($qKeluar)['keluar'] ?? 0;

$saldo = $masuk - $keluar;

echo json_encode([
    'status' => 'ok',
    'total_atp' => number_format($masuk,2),
    'total_mixer' => number_format($keluar,2),
    'saldo' => number_format($saldo,2)
]);