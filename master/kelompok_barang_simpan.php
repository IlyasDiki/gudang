<?php
require_once __DIR__ . '/../config/init.php';

$kode  = mysqli_real_escape_string($conn, $_POST['kode_kelompok']);
$nama  = mysqli_real_escape_string($conn, $_POST['nama_kelompok']);
$tipe  = $_POST['tipe_kelompok'];
$parent = $_POST['parent_id'] ?: NULL;

mysqli_query($conn, "
  INSERT INTO kelompok_barang
    (kode_kelompok, nama_kelompok, tipe_kelompok, parent_id)
  VALUES
    ('$kode', '$nama', '$tipe', ".($parent ? "'$parent'" : "NULL").")
");

header("Location: kelompok_barang.php");
exit;