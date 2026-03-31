<?php
require '../config/init.php';

$id     = $_POST['id_kelompok'] ?? null;
$kode   = $_POST['kode_kelompok'] ?? '';
$nama   = $_POST['nama_kelompok'] ?? '';
$tipe   = $_POST['tipe_kelompok'] ?? '';
$parent = $_POST['parent_id'] ?? null;

if (!$id || $kode == '') {
  die('Data tidak lengkap');
}

// jika parent kosong → NULL
if ($parent === '' || $parent === '0') {
  $parent = null;
}

// cegah parent = dirinya sendiri
if ($parent == $id) {
  die('Parent tidak boleh sama dengan dirinya sendiri');
}

$parent_sql = is_null($parent) ? 'NULL' : "'$parent'";

mysqli_query($conn, "
  UPDATE kelompok_barang SET
    kode_kelompok = '$kode',
    nama_kelompok = '$nama',
    tipe_kelompok = '$tipe',
    parent_id = $parent_sql
  WHERE id_kelompok = '$id'
");

header("Location: kelompok_barang.php?edit=success");
exit;
