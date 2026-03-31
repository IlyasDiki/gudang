<?php
require '../config/init.php';

$id     = $_POST['id_supplier'] ?? null;
$nama   = $_POST['nama_supplier'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$telp   = $_POST['telepon'] ?? '';

if (!$id || $nama == '') {
  die('Data tidak lengkap');
}

mysqli_query($conn, "
  UPDATE supplier SET
    nama_supplier = '$nama',
    alamat = '$alamat',
    telepon = '$telp'
  WHERE id_supplier = '$id'
");

header("Location: supplier.php?edit=success");
exit; 
