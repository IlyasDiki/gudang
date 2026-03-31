<?php
require_once __DIR__ . '/../config/init.php';

$tanggal    = $_POST['tanggal_terima'];
$idSupplier = $_POST['id_supplier'];
$jenis      = $_POST['jenis_pembelian'];
$user       = $_SESSION['id_pengguna'];

mysqli_begin_transaction($conn);

try {

  // 1. Simpan pembelian
  mysqli_query($conn, "
    INSERT INTO pembelian 
    (tanggal_terima, id_supplier, jenis_pembelian, dibuat_oleh, dibuat_pada)
    VALUES
    ('$tanggal', '$idSupplier', '$jenis', '$user', NOW())
  ");

  $idPembelian = mysqli_insert_id($conn);

  // 2. Simpan detail + mutasi
  foreach ($_POST['id_barang'] as $i => $idBarang) {

    $jumlah = $_POST['jumlah'][$i];
    if ($jumlah <= 0) continue;

    // detail pembelian
    mysqli_query($conn, "
      INSERT INTO pembelian_detail
      (id_pembelian, id_barang, jumlah)
      VALUES
      ('$idPembelian', '$idBarang', '$jumlah')
    ");

    // mutasi MASUK
    mysqli_query($conn, "
      INSERT INTO mutasi
      (tanggal, id_barang, jenis_mutasi, sumber, ref_id, jumlah, dibuat_oleh)
      VALUES
      ('$tanggal', '$idBarang', 'MASUK', 'PEMBELIAN', '$idPembelian', '$jumlah', '$user')
    ");
  }

  mysqli_commit($conn);
  header("Location: pembelian.php?status=success");
  exit;

} catch (Exception $e) {
  mysqli_rollback($conn);
  echo "Gagal simpan: " . $e->getMessage();
}