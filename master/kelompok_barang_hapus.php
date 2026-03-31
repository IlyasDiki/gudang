<?php
require_once __DIR__ . '/../config/init.php';

$id = (int) $_GET['id'];

mysqli_query($conn, "
  DELETE FROM kelompok_barang WHERE id_kelompok = $id
");

header("Location: kelompok_barang.php");
exit;