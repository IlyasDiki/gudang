<?php
require '../config/init.php';

$id_bk = $_POST['id_bk'] ?? '';
$id_bk = mysqli_real_escape_string($conn, $id_bk);

if(!$id_bk) die("ID produksi tidak ditemukan");

/* =========================================================
   1) HAPUS DATA BONGKAR YANG DIHAPUS USER
========================================================= */
$deleted_bongkar = $_POST['deleted_bongkar'] ?? '';
$deleted_bongkar = trim($deleted_bongkar);

if($deleted_bongkar != ''){
  $ids = explode(",", $deleted_bongkar);
  $ids_fix = [];

  foreach($ids as $id){
    $id = trim($id);
    if($id !== '' && ctype_digit($id)){
      $ids_fix[] = $id;
    }
  }

  if(count($ids_fix) > 0){
    $idlist = implode(",", $ids_fix);
    mysqli_query($conn, "DELETE FROM bkbriket_bongkar WHERE id_bongkar IN ($idlist) AND id_bk='$id_bk'");
  }
}

/* =========================================================
   2) HAPUS DATA MUTASI YANG DIHAPUS USER
========================================================= */
$deleted_mutasi = $_POST['deleted_mutasi'] ?? '';
$deleted_mutasi = trim($deleted_mutasi);

if($deleted_mutasi != ''){
  $ids = explode(",", $deleted_mutasi);
  $ids_fix = [];

  foreach($ids as $id){
    $id = trim($id);
    if($id !== '' && ctype_digit($id)){
      $ids_fix[] = $id;
    }
  }

  if(count($ids_fix) > 0){
    $idlist = implode(",", $ids_fix);
    mysqli_query($conn, "DELETE FROM bkbriket_mutasi WHERE id_mutasi IN ($idlist) AND id_bk='$id_bk'");
  }
}

/* =========================================================
   3) SIMPAN / UPDATE BONGKAR OVEN (MULTI ROW)
========================================================= */
$b_id      = $_POST['b_id'] ?? [];
$b_tanggal = $_POST['b_tanggal'] ?? [];
$b_krg     = $_POST['b_krg'] ?? [];
$b_add     = $_POST['b_add'] ?? [];
$b_ket     = $_POST['b_ket'] ?? [];

for($i=0; $i<count($b_tanggal); $i++){

  $id_bongkar = $b_id[$i] ?? '';
  $tgl        = $b_tanggal[$i] ?? '';
  $krg        = $b_krg[$i] ?? 0;
  $add        = $b_add[$i] ?? 0;
  $ket = $_POST['ket'][$i] ?? '';

  $id_bongkar = mysqli_real_escape_string($conn, $id_bongkar);
  $tgl        = mysqli_real_escape_string($conn, $tgl);
  $krg        = (float)$krg;
  $add        = (float)$add;
  $ket        = mysqli_real_escape_string($conn, $ket);

  // kalau tanggal kosong, skip
  if($tgl == '') continue;

  // kalau nilainya 0 semua, skip
  if($krg == 0 && $add == 0) continue;

  // UPDATE jika id ada
  if($id_bongkar != '' && ctype_digit($id_bongkar)){

    mysqli_query($conn, "
      UPDATE bkbriket_bongkar
      SET tanggal_bongkar='$tgl',
          krg='$krg',
          add_kg='$add',
          ket='$ket'
      WHERE id_bongkar='$id_bongkar'
        AND id_bk='$id_bk'
    ");

  } else {

    // INSERT jika id kosong
    mysqli_query($conn, "
      INSERT INTO bkbriket_bongkar
        (id_bk, tanggal_bongkar, krg, add_kg, ket)
      VALUES
        ('$id_bk', '$tgl', '$krg', '$add', '$ket')
    ");
  }
}

/* =========================================================
   4) SIMPAN / UPDATE MUTASI (MULTI ROW)
========================================================= */
$id_mutasi   = $_POST['id_mutasi'] ?? [];
$tanggal     = $_POST['tanggal'] ?? [];
$jenis       = $_POST['jenis'] ?? [];
$krg         = $_POST['krg'] ?? [];
$add_kg      = $_POST['add_kg'] ?? [];
$keterangan  = $_POST['keterangan'] ?? [];

for($i=0; $i<count($tanggal); $i++){

  $idm  = $id_mutasi[$i] ?? '';
  $tgl  = $tanggal[$i] ?? '';
  $jns  = $jenis[$i] ?? '';
  $k    = $krg[$i] ?? 0;
  $a    = $add_kg[$i] ?? 0;
  $ket  = $keterangan[$i] ?? '';

  $idm = mysqli_real_escape_string($conn, $idm);
  $tgl = mysqli_real_escape_string($conn, $tgl);
  $jns = mysqli_real_escape_string($conn, $jns);
  $k   = (float)$k;
  $a   = (float)$a;
  $ket = mysqli_real_escape_string($conn, $ket);

  // kalau tanggal atau jenis kosong, skip
  if($tgl == '' || $jns == '') continue;

  // kalau nilainya 0 semua, skip
  if($k == 0 && $a == 0) continue;

  // UPDATE jika id ada
  if($idm != '' && ctype_digit($idm)){

    mysqli_query($conn, "
      UPDATE bkbriket_mutasi
      SET tanggal='$tgl',
          jenis='$jns',
          krg='$k',
          add_kg='$a',
          keterangan='$ket'
      WHERE id_mutasi='$idm'
        AND id_bk='$id_bk'
    ");

  } else {

    // INSERT jika id kosong
    mysqli_query($conn, "
      INSERT INTO bkbriket_mutasi
        (id_bk, tanggal, jenis, krg, add_kg, keterangan)
      VALUES
        ('$id_bk', '$tgl', '$jns', '$k', '$a', '$ket')
    ");
  }
}

/* =========================================================
   5) REDIRECT BALIK
========================================================= */
header("Location: bkbriket_detail.php?id_bk=$id_bk&success=1");
exit;