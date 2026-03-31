<?php
require_once __DIR__ . '/../config/init.php';

$id = (int) $_GET['id'];

mysqli_query($conn, "
  DELETE FROM barang WHERE id_barang = $id
");

header("Location: barang.php");
exit;