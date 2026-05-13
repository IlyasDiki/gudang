<?php
require '../config/init.php';

$id = $_GET['id'] ?? 0;

mysqli_query($conn, "
    DELETE FROM produksi_detail
    WHERE id_produksi = '$id'
");

mysqli_query($conn, "
    DELETE FROM produksi
    WHERE id_produksi = '$id'
");

header("Location: produksi.php");
exit;