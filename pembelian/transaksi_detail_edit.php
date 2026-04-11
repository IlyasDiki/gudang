<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id     = (int) ($_POST['id'] ?? 0);
$jumlah = (int) ($_POST['jumlah'] ?? 0);

if (!$id || $jumlah <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data tidak valid"
    ]);
    exit;
}

try {

    mysqli_begin_transaction($conn);

    // 🔥 UPDATE TRANSAKSI DETAIL
    mysqli_query($conn, "
        UPDATE transaksi_detail
        SET jumlah = '$jumlah'
        WHERE id_detail = $id
    ");

    // 🔥 UPDATE MUTASI DETAIL JUGA (PENTING!)
    mysqli_query($conn, "
        UPDATE mutasi_detail
        SET jumlah = '$jumlah'
        WHERE id_tdetail = $id
    ");

    mysqli_commit($conn);

    echo json_encode([
        "status" => "success",
        "message" => "Data berhasil diupdate"
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}