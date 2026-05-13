<?php
require '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

$id = isset($_POST['id_stok_fisik']) ? (int) $_POST['id_stok_fisik'] : 0;
$jumlah = isset($_POST['jumlah']) ? str_replace(',', '.', $_POST['jumlah']) : '';

if ($id <= 0 || $jumlah === '') {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid request';
    exit;
}

$jumlah = (float) $jumlah;

$stmt = mysqli_prepare($conn, "UPDATE stok_fisik_at SET jumlah = ? WHERE id_stok_fisik = ?");
if (!$stmt) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Query prepare failed';
    exit;
}

mysqli_stmt_bind_param($stmt, 'di', $jumlah, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo 'ok';
