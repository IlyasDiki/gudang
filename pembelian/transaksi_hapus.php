<?php
require_once __DIR__ . '/../config/init.php';

$id = (int) $_GET['id'];

mysqli_query($conn, "
  DELETE FROM transaksi WHERE id_transaksi = $id
");

header("Location: transaksi.php");
exit;