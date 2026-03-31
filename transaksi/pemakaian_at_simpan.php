<?php
require '../config/init.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =======================
// DATA FORM
// =======================
$tanggal     = $_POST['tanggal'] ?? null;
$idBarang    = $_POST['id_barang'] ?? null;
$idSupplier  = $_POST['id_supplier'] ?? null;

$sortir    = floatval($_POST['sortir'] ?? 0);
$ma        = floatval($_POST['ma'] ?? 0);
$aa        = floatval($_POST['aa'] ?? 0);
$bMentah   = floatval($_POST['b_mentah'] ?? 0);
$air       = floatval($_POST['air'] ?? 0);
$atp       = floatval($_POST['atp'] ?? 0);

$user = $_SESSION['id_pengguna'] ?? null;

// =======================
// VALIDASI
// =======================
if (empty($tanggal) || empty($idBarang) || empty($idSupplier)) {
  die('Tanggal, barang, atau supplier belum diisi');
}

if (!isset($_SESSION['id_pengguna'])) {
  die('User belum login');
}


// =======================
// JENIS MUTASI AT
// =======================
$qJenis = mysqli_query($conn,"
SELECT id_jenis
FROM jenis_mutasi
WHERE kode_jenis='AT'
");

$jenis = mysqli_fetch_assoc($qJenis);

if (!$jenis) {
  die('Jenis mutasi AT belum dibuat');
}

$idJenis = $jenis['id_jenis'];


// =======================
// TRANSAKSI
// =======================
mysqli_begin_transaction($conn);

try {

  // 1️⃣ HEADER MUTASI
  mysqli_query($conn,"
  INSERT INTO mutasi
  (tanggal, id_jenis, keterangan, dibuat_oleh, id_supplier)
  VALUES
  (
    '$tanggal',
    '$idJenis', 
    'Pemakaian AT',
    '$user', 
    '$idSupplier'
  )
  ");

  $idMutasi = mysqli_insert_id($conn);


  // 2️⃣ DETAIL PEMAKAIAN AT
  mysqli_query($conn,"
  INSERT INTO at_detail
  (
    id_mutasi,
    id_barang,
    id_supplier,
    sortir,
    ma,
    aa,
    b_mentah,
    air,
    atp,
    tanggal
  )
  VALUES
  (
    '$idMutasi',
    '$idBarang',
    '$idSupplier',
    '$sortir',
    '$ma',
    '$aa',
    '$bMentah',
    '$air',
    '$atp',
    '$tanggal'
  )
  ");


  mysqli_commit($conn);

  header("Location: pemakaian_at.php?success=1");
  exit;

}

catch (Exception $e) {

  mysqli_rollback($conn);

  die('Gagal simpan pemakaian AT: '.$e->getMessage());

}