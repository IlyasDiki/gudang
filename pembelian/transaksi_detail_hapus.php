<?php
require_once __DIR__ . '/../config/init.php';

$id = (int) $_GET['id'];

mysqli_query($conn, "
  DELETE FROM transaksi_detail WHERE id_detail = $id
");

header("Location: transaksi.php");
exit;