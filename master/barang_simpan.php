<?php
require_once __DIR__ . '/../config/init.php';

/* ======================
   Ambil & siapkan data
====================== */
$kode_barang  = $_POST['kode_barang'];
$nama_barang  = $_POST['nama_barang'];
$id_kelompok  = $_POST['id_kelompok'];
$satuan       = $_POST['satuan'];

/* stok minimum:
   - jika form tidak ada / kosong → default 20
*/
$stok_minimum = isset($_POST['stok_minimum']) && $_POST['stok_minimum'] !== ''
  ? $_POST['stok_minimum']
  : 20;

$aktif = 1;

/* ======================
   Simpan ke database
====================== */
$sql = "
  INSERT INTO barang 
  (kode_barang, nama_barang, id_kelompok, satuan, stok_minimum, aktif)
  VALUES (
    '$kode_barang',
    '$nama_barang',
    '$id_kelompok',
    '$satuan',
    '$stok_minimum',
    '$aktif'
  )
";

mysqli_query($conn, $sql);

/* ======================
   Redirect
====================== */
header("Location: " . BASE_URL . "master/barang.php");
exit;