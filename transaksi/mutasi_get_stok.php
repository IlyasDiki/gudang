<?php
require '../config/init.php';

$id_barang = $_GET['id_barang'] ?? 0;

if (!$id_barang) {
  echo json_encode(['stok' => 0]);
  exit;
}

$q = mysqli_query($conn, "
  SELECT 
    COALESCE(SUM(CASE WHEN m.arah='MASUK' THEN md.jumlah ELSE 0 END),0) -
    COALESCE(SUM(CASE WHEN m.arah='KELUAR' THEN md.jumlah ELSE 0 END),0) AS stok
  FROM mutasi_detail md
  JOIN mutasi m ON m.id_mutasi = md.id_mutasi
  WHERE md.id_barang = '$id_barang'
");

$data = mysqli_fetch_assoc($q);
echo json_encode(['stok' => $data['stok'] ?? 0]);