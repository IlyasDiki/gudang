<?php
require '../config/init.php';

$id = $_POST['id_produksi'];
$mixer = $_POST['mixer'];

mysqli_query($conn,"
    UPDATE produksi_detail
    SET mixer='$mixer'
    WHERE id_produksi='$id'
");

echo 'OK';