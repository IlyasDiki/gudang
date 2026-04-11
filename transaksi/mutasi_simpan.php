<?php
require '../config/init.php';

$tanggal    = $_POST['tanggal'] ?? null;
$kode_jenis = $_POST['kode_jenis'] ?? null;
$id_barang  = $_POST['id_barang'] ?? null;
$idSupplier = $_POST['id_supplier'] ?? null;
$jumlah     = $_POST['jumlah'] ?? 0;
$user       = $_SESSION['id_pengguna'] ?? null;

// 🔥 karena ini KOREKSI → tidak ada transaksi
$idTransaksi = "NULL";

if (!$tanggal) die("Tanggal wajib diisi");
if (!$kode_jenis) die("Jenis mutasi belum dipilih");
if (!$id_barang) die("Barang belum dipilih");
if (!$jumlah) die("Jumlah belum diisi");

mysqli_begin_transaction($conn);

try {

  // =========================
  // AMBIL JENIS MUTASI
  // =========================
  $q = mysqli_query($conn,
    "SELECT id_jenis, tipe FROM jenis_mutasi WHERE kode_jenis='$kode_jenis'"
  );
  $jenis = mysqli_fetch_assoc($q);

  if (!$jenis) {
    throw new Exception('Jenis mutasi tidak valid');
  }

  $idJenis = $jenis['id_jenis'];

  // =========================
  // TENTUKAN ARAH
  // =========================
  $arah = ($jumlah > 0) ? "MASUK" : "KELUAR";
  $jumlah = abs($jumlah);

  // =========================
  // INSERT HEADER MUTASI
  // =========================
  mysqli_query($conn, "
    INSERT INTO mutasi (
        id_transaksi,
        tanggal,
        id_jenis,
        arah,
        keterangan,
        dibuat_oleh,
        dibuat_pada,
        id_supplier
    )
    VALUES (
        $idTransaksi,
        '$tanggal',
        '$idJenis',
        '$arah',
        'Koreksi Stok',
        '$user',
        NOW(),
        ".($idSupplier ? "'$idSupplier'" : "NULL")."
    )
  ");

  $id_mutasi = mysqli_insert_id($conn);

  // =========================
  // INSERT DETAIL
  // =========================
  mysqli_query($conn, "
    INSERT INTO mutasi_detail (
        id_mutasi,
        id_barang,
        jumlah,
        id_supplier
    )
    VALUES (
        '$id_mutasi',
        '$id_barang',
        '$jumlah',
        ".($idSupplier ? "'$idSupplier'" : "NULL")."
    )
  ");

  // =========================
  // OPTIONAL: AT
  // =========================
  if ($jenis['tipe'] == 'AT') {
    mysqli_query($conn, "
      INSERT INTO at_detail
      (id_mutasi, sortir, ma, aa, b_mentah, air, atp)
      VALUES (
        '$id_mutasi',
        '{$_POST['sortir']}',
        '{$_POST['ma']}',
        '{$_POST['aa']}',
        '{$_POST['b_mentah']}',
        '{$_POST['air']}',
        '{$_POST['atp']}'
      )
    ");
  }

  // =========================
  // OPTIONAL: PRODUKSI
  // =========================
  if ($jenis['tipe'] == 'PRODUKSI') {
    mysqli_query($conn, "
      INSERT INTO produksi_detail (id_mutasi, mixer)
      VALUES ('$id_mutasi', '{$_POST['mixer']}')
    ");
  }

  mysqli_commit($conn);

  header("Location: mutasi.php?success=1");
  exit;

} catch (Exception $e) {

  mysqli_rollback($conn);
  die("Gagal menyimpan: " . $e->getMessage());

}