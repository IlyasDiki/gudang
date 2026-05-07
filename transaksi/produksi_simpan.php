
<?php
require_once __DIR__ . '/../config/init.php';

$tanggal           = $_POST['tanggal'] ?? null;
$idBarangATP       = $_POST['id_barang_atp'] ?? null;
$idSupplier        = $_POST['id_supplier'] ?? null;
$idMutasiDetail    = $_POST['id_mutasi_detail'] ?? null;
$mixer             = $_POST['mixer'] ?? 0;
$keterangan        = $_POST['keterangan'] ?? '';
$user              = $_SESSION['id_pengguna'] ?? null;

if (!$tanggal || !$idBarangATP || !$idSupplier || !$idMutasiDetail || $mixer <= 0) {
  die("Data tidak lengkap");
}

if (!$user) {
  die("User belum login");
}

try {

  mysqli_begin_transaction($conn);

  // 1) simpan produksi header
  mysqli_query($conn, "
    INSERT INTO produksi (tanggal, id_barang_atp, id_supplier, id_mutasi_detail, keterangan, dibuat_oleh)
    VALUES ('$tanggal', '$idBarangATP', '$idSupplier', '$idMutasiDetail', '$keterangan', '$user')
  ");
  $idProduksi = mysqli_insert_id($conn);

  // 2) simpan produksi detail dengan referensi id_mutasi_detail
  mysqli_query($conn, "
    INSERT INTO produksi_detail (id_produksi, id_barang_atp, mixer, id_mutasi_detail)
    VALUES ('$idProduksi', '$idBarangATP', '$mixer', '$idMutasiDetail')
  ");

  mysqli_commit($conn);

  header("Location: produksi.php?success=1");
  exit;

} catch (Exception $e) {

  mysqli_rollback($conn);
  die("Gagal menyimpan produksi: " . $e->getMessage());
}