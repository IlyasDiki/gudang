
<?php
require_once __DIR__ . '/../config/init.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$tanggal       = $_POST['tanggal'] ?? null;
$idBarangATP   = $_POST['id_barang_atp'] ?? null;
$idSupplier    = $_POST['id_supplier'] ?? null;
$mixer         = $_POST['mixer'] ?? 0;
$keterangan    = $_POST['keterangan'] ?? '';
$user          = $_SESSION['id_pengguna'] ?? null;

if (!$tanggal || !$idBarangATP || !$idSupplier || $mixer <= 0) {
  die("Data tidak lengkap");
}


if (!$user) {
  die("User belum login");
}

try {

  mysqli_begin_transaction($conn);

  // 1) simpan produksi header
  mysqli_query($conn, "
    INSERT INTO produksi (tanggal, id_barang_atp, id_supplier, keterangan, dibuat_oleh)
    VALUES ('$tanggal', '$idBarangATP', '$idSupplier', '$keterangan', '$user')
  ");
  $idProduksi = mysqli_insert_id($conn);

  // 2) simpan produksi detail
  mysqli_query($conn, "
    INSERT INTO produksi_detail (id_produksi, id_barang_atp, mixer)
    VALUES ('$idProduksi', '$idBarangATP', '$mixer')
  ");

  // 3) ambil jenis mutasi PRODUKSI
  $qJenis = mysqli_query($conn, "
    SELECT id_jenis
    FROM jenis_mutasi
    WHERE tipe = 'PRODUKSI'
    LIMIT 1
  ");
  $dataJenis = mysqli_fetch_assoc($qJenis);

  if (!$dataJenis) {
    throw new Exception("Jenis mutasi 'Produksi' belum dibuat di tabel jenis_mutasi");
  }
  $idJenis = $dataJenis['id_jenis'];

  // 4) simpan mutasi header (arah KELUAR)
  mysqli_query($conn, "
    INSERT INTO mutasi (tanggal, id_jenis, arah, keterangan, dibuat_oleh, id_supplier)
    VALUES ('$tanggal', '$idJenis', 'KELUAR', 'Produksi - Pemakaian Produksi', '$user', '$idSupplier')
  ");
  $idMutasi = mysqli_insert_id($conn);

  // 5) simpan mutasi detail (ATP keluar sebesar mixer)
  mysqli_query($conn, "
    INSERT INTO mutasi_detail (id_mutasi, id_barang, jumlah, id_supplier)
    VALUES ('$idMutasi', '$idBarangATP', '$mixer', '$idSupplier')
  ");

  mysqli_commit($conn);

  header("Location: produksi.php?success=1");
  exit;

} catch (Exception $e) {

  mysqli_rollback($conn);
  die("Gagal menyimpan produksi: " . $e->getMessage());
}