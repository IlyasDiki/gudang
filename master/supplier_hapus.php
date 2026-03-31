<?php
require_once __DIR__ . '/../config/init.php';

$id = (int) $_GET['id'];

mysqli_query($conn, "
  DELETE FROM supplier WHERE id_supplier = $id
");

header("Location: supplier.php");
exit;