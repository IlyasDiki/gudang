<?php
require '../config/init.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM tambahan WHERE id_tambahan = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header('Location: tambahan_pemakaian_at.php');
exit;
