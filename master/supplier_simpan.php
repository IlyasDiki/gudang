<?php
require_once __DIR__ . '/../config/init.php';

$nama   = mysqli_real_escape_string($conn, $_POST['nama_supplier']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
$telp   = mysqli_real_escape_string($conn, $_POST['telepon']);

mysqli_query($conn, "
  INSERT INTO supplier (nama_supplier, alamat, telepon)
  VALUES ('$nama', '$alamat', '$telp')
");

header("Location: supplier.php");
exit;