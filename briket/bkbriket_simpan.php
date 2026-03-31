<?php
require '../config/init.php';

$tanggal = $_POST['tanggal'] ?? '';
$id_barang_briket = $_POST['id_barang_briket'] ?? '';
$id_kelompok = $_POST['id_kelompok'] ?? '';
$lokasi = $_POST['lokasi'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';

if($tanggal=='' || $lokasi=='' || $id_barang_briket=='' || $id_kelompok==''){
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

/* simpan data */
mysqli_query($conn,"
INSERT INTO bkbriket
(tanggal,id_barang_briket,id_kelompok,lokasi,keterangan,status)
VALUES
('$tanggal','$id_barang_briket','$id_kelompok','$lokasi','$keterangan','$status')
");

/* ambil id terakhir */
$id_bk = mysqli_insert_id($conn);

/* redirect ke halaman detail */
header("Location: bkbriket_detail.php?id_bk=".$id_bk);
exit;