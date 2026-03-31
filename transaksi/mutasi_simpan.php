<?php
require '../config/init.php';

$tanggal    = $_POST['tanggal'];
$id_transaksi  = $_POST['id_transaksi'];
$kode_jenis = $_POST['kode_jenis'];
$user       = $_SESSION['id_pengguna'];

mysqli_begin_transaction($conn);

try {

  // ambil jenis
  $q = mysqli_query($conn,
    "SELECT id_jenis, tipe FROM jenis_mutasi WHERE kode_jenis='$kode_jenis'"
  );
  $jenis = mysqli_fetch_assoc($q);

  if (!$jenis) {
    throw new Exception('Jenis mutasi tidak valid');
  }

  // header mutasi
  mysqli_query($conn, "
    INSERT INTO mutasi (tanggal, id_transaksi, id_jenis, dibuat_oleh)
    VALUES ('$tanggal', '$id_transaksi', '{$jenis['id_jenis']}', '$user')
  ");

  $id_mutasi = mysqli_insert_id($conn);
  $qStok = mysqli_query($conn, "
    SELECT 
      COALESCE(SUM(CASE WHEN m.arah='MASUK' THEN md.jumlah ELSE 0 END),0) -
      COALESCE(SUM(CASE WHEN m.arah='KELUAR' THEN md.jumlah ELSE 0 END),0) AS stok
    FROM mutasi_detail md
    JOIN mutasi m ON m.id_mutasi = md.id_mutasi
    WHERE md.id_barang = '$id_barang'
  ");
  $stok_sistem = mysqli_fetch_assoc($qStok)['stok'] ?? 0;

  $selisih = $stok_fisik - $stok_sistem;

  if ($selisih == 0) {
    die("Tidak ada koreksi karena stok fisik sama dengan stok sistem.");
  }

  $arah = ($selisih > 0) ? "MASUK" : "KELUAR";
  $jumlah = abs($selisih);
  // ===============================
  // PEMBELIAN / KOREKSI
  // ===============================
  if (in_array($jenis['tipe'], ['PEMBELIAN', 'KOREKSI'])) {
    $jumlah = $_POST['jumlah'] ?? 0;

    mysqli_query($conn, "
      INSERT INTO mutasi_detail (id_mutasi, jumlah)
      VALUES ('$id_mutasi', '$jumlah')
    ");
  }

  // ===============================
  // PEMAKAIAN AT
  // ===============================
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

  // ===============================
  // PRODUKSI
  // ===============================
  if ($jenis['tipe'] == 'PRODUKSI') {
    mysqli_query($conn, "
      INSERT INTO produksi_detail (id_mutasi, mixer)
      VALUES ('$id_mutasi', '{$_POST['mixer']}')
    ");
  }

  mysqli_commit($conn);
  header("Location: mutasi.php?success=1");

} catch (Exception $e) {
  mysqli_rollback($conn);
  die($e->getMessage());
}