<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

// 🔥 TAMBAHKAN INI
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = (int) ($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode([
        "status" => "error",
        "message" => "ID tidak valid"
    ]);
    exit;
}

try {

    mysqli_begin_transaction($conn);

    // 🔥 DEBUG 1
    mysqli_query($conn, "
        DELETE FROM mutasi_detail
        WHERE id_tdetail = $id
    ");

    // 🔥 DEBUG 2
    mysqli_query($conn, "
        DELETE FROM transaksi_detail
        WHERE id_detail = $id
    ");

    mysqli_commit($conn);

    echo json_encode([
        "status" => "success",
        "message" => "Data berhasil dihapus"
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage() // 🔥 INI YANG KITA BUTUH
    ]);
}
exit;