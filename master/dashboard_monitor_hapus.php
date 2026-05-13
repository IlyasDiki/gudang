<?php

require_once '../config/init.php';

$id = $_POST['id'] ?? 0;

mysqli_query($conn, "
    DELETE FROM dashboard_monitor
    WHERE id_monitor = '$id'
");