<?php
require '../config/init.php';

header('Content-Type: application/json');

$idBarang   = $_GET['id_barang'] ?? null;
$idSupplier = $_GET['id_supplier'] ?? null;

if (!$idBarang || !$idSupplier) {

    echo json_encode([
        'status'=>'error',
        'message' => 'Parameter tidak lengkap'
    ]);

    exit;
}

$data = [];

/* =========================
   CEK SUPPLIER REPRO
========================= */

$qCekSupplier = mysqli_query($conn, "
    SELECT nama_supplier
    FROM supplier
    WHERE id_supplier = '$idSupplier'
");

$dSupplier = mysqli_fetch_assoc($qCekSupplier);

$isRepro = strtoupper(trim($dSupplier['nama_supplier'] ?? '')) == 'REPRO BRIKET';


/* ===================================================
   1. STOK SUPPLIER BIASA
=================================================== */

if (!$isRepro) {

    $q = mysqli_query($conn, "

    SELECT 
        md.id_detail,
        m.tanggal,
        md.jumlah,

        (
            md.jumlah -

            IFNULL((
                SELECT SUM(sortir+ma+aa+b_mentah+air+atp)
                FROM at_detail
                WHERE id_mutasi_detail = md.id_detail
            ),0)

        ) AS sisa

    FROM mutasi_detail md

    JOIN mutasi m 
    ON m.id_mutasi = md.id_mutasi

    WHERE md.id_barang = '$idBarang'
    AND md.id_supplier = '$idSupplier'
    AND m.arah = 'MASUK'

    HAVING sisa > 0

    ORDER BY m.tanggal ASC

    ");

    while ($row = mysqli_fetch_assoc($q)) {

        $data[] = [
            'id'       => 'supplier_'.$row['id_detail'],
            'real_id'  => $row['id_detail'],
            'tipe'     => 'supplier',
            'saldo'    => $row['sisa'],
            'label'    => date('d M Y', strtotime($row['tanggal'])) . ' | SUPPLIER'
        ];
    }
}


/* ===================================================
   2. STOK REPRO
=================================================== */

if ($isRepro) {

    $qRepro = mysqli_query($conn, "

    SELECT
        bm.id_mutasi,
        bm.tanggal,

        ((bm.krg * 25) + bm.add_kg) AS qty,

        (
            ((bm.krg * 25) + bm.add_kg)

            -

            IFNULL((
                SELECT SUM(sortir+ma+aa+b_mentah+air+atp)
                FROM at_detail
                WHERE id_mutasi_detail = CONCAT('repro_', bm.id_mutasi)
            ),0)

        ) AS saldo

    FROM bkbriket_mutasi bm

    JOIN bkbriket b
    ON b.id_bk = bm.id_bk

    WHERE bm.jenis = 'REPRO'
    AND b.status = 'KARANTINA'

    HAVING saldo > 0

    ORDER BY bm.tanggal ASC

    ");

    if (!$qRepro) {

        echo json_encode([
            'status' => 'error',
            'message' => mysqli_error($conn)
        ]);

        exit;
    }

    while($r = mysqli_fetch_assoc($qRepro)) {

        $data[] = [
            'id'       => $r['id_mutasi'],
            'real_id'  => $r['id_mutasi'],
            'tipe'     => 'repro',
            'saldo'    => $r['saldo'],
            'label'    => date('d-m-Y', strtotime($r['tanggal'])) . ' | REPRO'
        ];
    }
}

echo json_encode([
    'status' => 'ok',
    'stok'   => $data
]);