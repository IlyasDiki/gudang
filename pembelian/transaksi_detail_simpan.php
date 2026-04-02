<?php
require '../config/init.php';
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$idtransaksi = $_POST['id_transaksi'] ?? null;
$idBarang    = $_POST['id_barang'] ?? null;
$idSupplier  = $_POST['id_supplier'] ?? null;
$jumlah      = $_POST['jumlah'] ?? 0;
$user        = $_SESSION['id_pengguna'] ?? null;

if (!$idtransaksi || !$idBarang || $jumlah <= 0) {
  die("Data tidak lengkap");
}

if (!$user) {
  die("User belum login");
}
if ($idSupplier) {
  $cek = mysqli_query($conn, "SELECT id_supplier FROM supplier WHERE id_supplier = '$idSupplier'");
  if (mysqli_num_rows($cek) == 0) {
    throw new Exception("Supplier tidak ditemukan");
  }
}
try {

  mysqli_begin_transaction($conn);

  // 1) Ambil arah dari transaksi -> jenis_transaksi
  $qArah = mysqli_query($conn, "
    SELECT jt.arah
    FROM transaksi t
    JOIN jenis_transaksi jt ON jt.id_jenist = t.jenis_transaksi
    WHERE t.id_transaksi = '$idtransaksi'
    LIMIT 1
  ");
  $dataArah = mysqli_fetch_assoc($qArah);

  if (!$dataArah) {
    throw new Exception("Transaksi tidak ditemukan / jenis transaksi belum ada");
  }

  $arah = $dataArah['arah']; // MASUK / KELUAR

  // 2) Simpan transaksi detail
  mysqli_query($conn, "
    INSERT INTO transaksi_detail (id_transaksi, id_barang, id_supplier, jumlah)
    VALUES ('$idtransaksi', '$idBarang',  ".($idSupplier ? "'$idSupplier'" : "NULL").", '$jumlah')
  ");

  // 🔥 LANGSUNG ambil di sini
  $idDetailTransaksi = mysqli_insert_id($conn);

  // 3) Cari apakah transaksi ini sudah punya mutasi header
  $qMutasi = mysqli_query($conn, "
    SELECT id_mutasi
    FROM mutasi
    WHERE id_transaksi = '$idtransaksi'
    LIMIT 1
  ");
  $dataMutasi = mysqli_fetch_assoc($qMutasi);

  // 4) Kalau belum ada, buat mutasi header 1x
  if (!$dataMutasi) {

    // Ambil id_jenis untuk transaksi
    $qJenis = mysqli_query($conn, "
      SELECT id_jenis
      FROM jenis_mutasi
      WHERE tipe = 'TRANSAKSI'
      LIMIT 1
    ");
    $dataJenis = mysqli_fetch_assoc($qJenis);

    if (!$dataJenis) {
      throw new Exception("Jenis mutasi TRANSAKSI belum ada");
    }

    $idJenis = $dataJenis['id_jenis'];

    // Ambil tanggal transaksi (biar sama, bukan CURDATE)
    $qTgl = mysqli_query($conn, "
      SELECT tanggal_terima
      FROM transaksi
      WHERE id_transaksi = '$idtransaksi'
      LIMIT 1
    ");
    $dataTgl = mysqli_fetch_assoc($qTgl);

    $tanggalTransaksi = $dataTgl['tanggal_terima'] ?? date('Y-m-d');

    mysqli_query($conn, "
      INSERT INTO mutasi (id_transaksi, tanggal, id_jenis, arah, keterangan, dibuat_oleh, dibuat_pada, id_supplier)
      VALUES (
        '$idtransaksi',
        '$tanggalTransaksi',
        '$idJenis',
        '$arah',
        'Transaksi Barang',
        '$user',
        NOW(),
        ".($idSupplier ? "'$idSupplier'" : "NULL")."
      )
    ");

    $idMutasi = mysqli_insert_id($conn);

  } else {

    $idMutasi = $dataMutasi['id_mutasi'];

  }

  
  // 5) Simpan mutasi detail
  mysqli_query($conn, "
    INSERT INTO mutasi_detail (id_mutasi, id_barang, jumlah, id_supplier, id_tdetail)
    VALUES ('$idMutasi', '$idBarang', '$jumlah', ".($idSupplier ? "'$idSupplier'" : "NULL").", '$idDetailTransaksi')
  ");

  mysqli_commit($conn);

  if (mysqli_commit($conn)) {
      echo json_encode([
          "status" => "success",
          "message" => "Data berhasil ditambahkan"
      ]);
  } else {
      echo json_encode([
          "status" => "error",
          "message" => "Gagal menyimpan data"
      ]);
  }
exit;

} catch (Exception $e) {

  mysqli_rollback($conn);
  die("Gagal menyimpan: " . $e->getMessage());

}