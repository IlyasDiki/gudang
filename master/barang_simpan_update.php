<?php
require '../config/init.php';

$id       = $_POST['id_barang'] ?? null;
$kode     = $_POST['kode_barang'] ?? '';
$nama     = $_POST['nama_barang'] ?? '';
$kelompok = $_POST['id_kelompok'] ?? null;
$satuan   = $_POST['satuan'] ?? '';

if (!$id || $kode == '' || $nama == '') {
  die('Data tidak lengkap');
}

// FK handling
$kelompok_sql = ($kelompok == '' ? 'NULL' : "'$kelompok'");

mysqli_query($conn, "
  UPDATE barang SET
    kode_barang = '$kode',
    nama_barang = '$nama',
    id_kelompok = $kelompok_sql,
    satuan = '$satuan'
  WHERE id_barang = '$id'
");

header("Location: barang.php?edit=success");
exit;