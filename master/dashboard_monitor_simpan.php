<?php

require_once '../config/init.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

$id_barang = $_POST['id_barang'] ?? '';
$id_supplier = $_POST['id_supplier'] ?? null;

if(empty($id_barang)){
    $response['message'] = 'Barang belum dipilih';
    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| NULL SUPPLIER
|--------------------------------------------------------------------------
*/
if($id_supplier == ''){
    $id_supplier = NULL;
}

/*
|--------------------------------------------------------------------------
| CEK DUPLIKAT
|--------------------------------------------------------------------------
*/
$cek = mysqli_query($conn, "
    SELECT id_monitor
    FROM dashboard_monitor
    WHERE id_barang = '$id_barang'
    AND ".(
        $id_supplier === NULL
        ? "id_supplier IS NULL"
        : "id_supplier = '$id_supplier'"
    )."
");

if(mysqli_num_rows($cek) > 0){

    $response['message'] = 'Barang sudah ada di pantauan';

    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/
$q = mysqli_query($conn, "
    INSERT INTO dashboard_monitor (
        id_barang,
        id_supplier
    ) VALUES (
        '$id_barang',
        ".(
            $id_supplier === NULL
            ? "NULL"
            : "'$id_supplier'"
        )."
    )
");

if($q){

    $response['success'] = true;

} else {

    $response['message'] = mysqli_error($conn);

}

echo json_encode($response);