<?php
require '../config/init.php';

$bulan = $_POST['bulan'] ?? '';
$tahun = $_POST['tahun'] ?? '';

if (empty($bulan) || empty($tahun)) {
  die("Bulan dan tahun wajib diisi");
} 

// paksa jadi tanggal 1
$tanggal = $tahun . '-' . $bulan . '-01';
$id_barang    = $_POST['id_barang'] ?? [];
$id_supplier  = $_POST['id_supplier'] ?? [];
$jumlah       = $_POST['jumlah'] ?? [];


  // ambil jenis mutasi STOKAWAL
  $qJenis = mysqli_query($conn,"
  SELECT id_jenis
  FROM jenis_mutasi
  WHERE tipe='STOKAWAL'
  LIMIT 1
  ");

  $dataJenis = mysqli_fetch_assoc($qJenis);

  if(!$dataJenis){
    throw new Exception("Jenis mutasi STOKAWAL belum ada");
  }

  $idJenis = $dataJenis['id_jenis'];

$cekError = false;

for ($i = 0; $i < count($id_barang); $i++) {

  $idb = $id_barang[$i];
  $ids = $id_supplier[$i] ?? null;

  $qCek = mysqli_query($conn, "
    SELECT 1
    FROM mutasi m
    JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
    WHERE m.id_jenis = '$idJenis'
    AND md.id_barang = '$idb'
    ".($ids ? " AND md.id_supplier = '$ids'" : "")."
    AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
  ");

  if (mysqli_num_rows($qCek) > 0) {
    $cekError = true;
    break;
  }
}

if ($cekError) {
  echo "<script>
    alert('Stok awal untuk barang / supplier di bulan ini sudah ada!');
    window.location.href='stok_awal.php';
  </script>";
  exit;
}

if ($tanggal == '' || count($id_barang) == 0) {
  die("Data tidak lengkap.");
}

mysqli_begin_transaction($conn);

try {

  // HEADER MUTASI
  mysqli_query($conn,"
  INSERT INTO mutasi (tanggal,id_jenis,arah,keterangan,jenis)
  VALUES ('$tanggal','$idJenis','MASUK','Stok Awal','AWAL')
  ");

  $id_mutasi = mysqli_insert_id($conn);

  // DETAIL MUTASI
  for($i=0;$i<count($id_barang);$i++){

    $idb = $id_barang[$i];
    $ids = $id_supplier[$i] ?? null;
    $jml = $jumlah[$i];

    if($jml <= 0) continue;

    $ids = ($ids == '' ? "NULL" : "'$ids'");

    mysqli_query($conn,"
    INSERT INTO mutasi_detail
    (id_mutasi,id_barang,id_supplier,jumlah)
    VALUES
    ('$id_mutasi','$idb',$ids,'$jml')
    ");

  }

  mysqli_commit($conn);

  header("Location: stok_awal.php?success=1");
  exit;

}
catch(Exception $e){

  mysqli_rollback($conn);

  die("Gagal simpan : ".$e->getMessage());

}