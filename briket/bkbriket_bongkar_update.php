<?php
require '../config/init.php';

/* ambil JSON */
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if(!$data){
  die("Data tidak diterima");
}

$id_bk = $data['id_bk'] ?? '';
$rows  = $data['data'] ?? [];

$id_bk = mysqli_real_escape_string($conn, $id_bk);

if(!$id_bk){
  die("ID tidak valid");
}

/* loop data */
foreach($rows as $r){

  $id_bongkar = $r['id_bongkar'] ?? '';
  $tgl = $r['tgl'] ?? '';
  $krg = (float)($r['krg'] ?? 0);
  $add = (float)($r['add'] ?? 0);
  $ket = $r['ket'] ?? '';

  $tgl = mysqli_real_escape_string($conn, $tgl);
  $ket = mysqli_real_escape_string($conn, $ket);

  /* skip kalau kosong */
  if($tgl == '') continue;
  if($krg == 0 && $add == 0) continue;

  /* UPDATE */
  if($id_bongkar != ''){

    mysqli_query($conn,"
      UPDATE bkbriket_bongkar
      SET 
        tanggal_bongkar='$tgl',
        krg='$krg',
        add_kg='$add',
        ket='$ket'
      WHERE id_bongkar='$id_bongkar'
      AND id_bk='$id_bk'
    ");

  } else {

    /* INSERT */
    mysqli_query($conn,"
      INSERT INTO bkbriket_bongkar
      (id_bk,tanggal_bongkar,krg,add_kg,ket)
      VALUES
      ('$id_bk','$tgl','$krg','$add','$ket')
    ");
  }

}

echo "OK";