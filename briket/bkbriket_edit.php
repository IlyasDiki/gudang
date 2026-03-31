<?php
require '../config/init.php';

$id_bk = $_POST['id_bk'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$id_barang_briket = $_POST['id_barang_briket'] ?? '';
$id_kelompok = $_POST['id_kelompok'] ?? '';
$lokasi = $_POST['lokasi'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';

if($id_bk=='' || $tanggal=='' || $lokasi=='' || $id_kelompok==''){
  die("Data tidak lengkap");
}

/* menentukan status otomatis */
if($id_kelompok == 15){
    $status = 'LOLOS';
}
elseif($id_kelompok == 16){
    $status = 'KARANTINA';
}
else{
    die("Jenis hasil bongkar tidak valid");
}

$id_bk = mysqli_real_escape_string($conn, $id_bk);
$tanggal = mysqli_real_escape_string($conn, $tanggal);
$id_barang_briket = mysqli_real_escape_string($conn, $id_barang_briket);
$id_kelompok = mysqli_real_escape_string($conn, $id_kelompok);
$lokasi = mysqli_real_escape_string($conn, $lokasi);
$keterangan = mysqli_real_escape_string($conn, $keterangan);
$status = mysqli_real_escape_string($conn, $status);

mysqli_query($conn, "
  UPDATE bkbriket 
  SET tanggal='$tanggal', id_barang_briket='$id_barang_briket', id_kelompok='$id_kelompok', lokasi='$lokasi', keterangan='$keterangan', status='$status'
  WHERE id_bk='$id_bk'
");

header("Location: bkbriket.php?edit_success=1");
exit;
?>