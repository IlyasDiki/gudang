<?php
require_once __DIR__ . '/../config/init.php';

$tanggal        = $_POST['tanggal_terima'] ?? null;
$jenisTransaksi = $_POST['jenis_transaksi'] ?? null;
$idKelompok     = $_POST['id_kelompok'] ?? null;
$user           = $_SESSION['id_pengguna'] ?? null;

if (!$tanggal || !$jenisTransaksi || !$idKelompok || !$user) {
  die('Data tidak lengkap');
}

mysqli_query($conn,"
INSERT INTO transaksi
(tanggal_terima, jenis_transaksi, id_kelompok, dibuat_oleh, dibuat_pada)
VALUES
(
'$tanggal',
'$jenisTransaksi',
'$idKelompok',
'$user',
NOW()
)
");

header("Location: transaksi.php?status=sukses");
exit;