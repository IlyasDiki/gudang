<?php
require '../config/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM stok_fisik_at WHERE id_stok_fisik = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header('Location: stok_fisik_at.php');
exit;
