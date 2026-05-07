<?php
require '../config/init.php';

$id_bk   = $_POST['id_bk'] ?? '';
$tgl     = $_POST['tanggal_bongkar'] ?? '';
$krg     = $_POST['krg'] ?? 0;
$add     = $_POST['add_kg'] ?? 0;
$ket     = $_POST['keterangan'] ?? '';

$id_bk = mysqli_real_escape_string($conn, $id_bk);
$tgl   = mysqli_real_escape_string($conn, $tgl);
$krg   = (float)$krg;
$add   = (float)$add;
$ket   = mysqli_real_escape_string($conn, $ket);

if(!$id_bk || !$tgl){
  die("Data tidak lengkap");
}

// skip kalau kosong
if($krg == 0 && $add == 0){
  die("Nilai tidak boleh kosong");
}

mysqli_query($conn, "
  INSERT INTO bkbriket_bongkar
    (id_bk, tanggal_bongkar, krg, add_kg, ket)
  VALUES
    ('$id_bk', '$tgl', '$krg', '$add', '$ket')
");

// balik ke halaman utama
header("Location: bkbriket.php");
exit;