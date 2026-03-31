<?php
require '../config/init.php';

header('Content-Type: application/json');

$idBarang   = $_GET['id_barang'] ?? null;
$idSupplier = $_GET['id_supplier'] ?? null;

if(!$idBarang || !$idSupplier){

echo json_encode([
'status'=>'error',
'message'=>'Parameter tidak lengkap'
]);

exit;
}


/* ======================
   STOK MASUK (STOK AWAL)
====================== */

$qMasuk=mysqli_query($conn,"
SELECT IFNULL(SUM(jumlah),0) AS stok_masuk
FROM mutasi_detail
WHERE id_barang='$idBarang'
AND id_supplier='$idSupplier'
");

$masuk=mysqli_fetch_assoc($qMasuk)['stok_masuk'] ?? 0;


/* ======================
   PEMAKAIAN AT
====================== */

$qPakai=mysqli_query($conn,"
SELECT IFNULL(SUM(
sortir + ma + aa + b_mentah + air + atp
),0) AS total_pakai
FROM at_detail
WHERE id_barang='$idBarang'
AND id_supplier='$idSupplier'
");

$pakai=mysqli_fetch_assoc($qPakai)['total_pakai'] ?? 0;


/* ======================
   SALDO
====================== */

$saldo = $masuk - $pakai;

echo json_encode([

'status'=>'ok',

'saldo'=>rtrim(rtrim(number_format($saldo,2,'.',''),'0'),'.')

]);