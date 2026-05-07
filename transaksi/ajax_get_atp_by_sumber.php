<?php
require '../config/init.php';

header('Content-Type: application/json');

$idBarang   = $_GET['id_barang'] ?? null;
$idSupplier = $_GET['id_supplier'] ?? null;

if (!$idBarang || !$idSupplier) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Parameter tidak lengkap'
    ]);
    exit;
}

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

$data = [];

/* ===================================================
   1. STOK SUPPLIER BIASA
=================================================== */
if (!$isRepro) {

    $q = mysqli_query($conn, "

    SELECT 
        md.id_detail,
        m.tanggal,
        md.jumlah AS total_atp,

        /* TOTAL MIXER TERPAKAI */
        IFNULL((
            SELECT SUM(pd.mixer)
            FROM produksi_detail pd
            WHERE pd.id_mutasi_detail = md.id_detail
        ),0) AS total_mixer,

        /* SALDO ATP */
        (
            md.jumlah -

            IFNULL((
                SELECT SUM(pd.mixer)
                FROM produksi_detail pd
                WHERE pd.id_mutasi_detail = md.id_detail
            ),0)

        ) AS saldo_atp

    FROM mutasi_detail md

    JOIN mutasi m
    ON m.id_mutasi = md.id_mutasi

    WHERE md.id_barang = '$idBarang'
    AND md.id_supplier = '$idSupplier'
    AND m.arah = 'MASUK'

    HAVING saldo_atp > 0

    ORDER BY m.tanggal ASC

    ");

    if (!$q) {

        echo json_encode([
            'status'  => 'error',
            'message' => mysqli_error($conn)
        ]);

        exit;
    }

    while ($row = mysqli_fetch_assoc($q)) {

        $data[] = [
            'id'            => 'supplier_' . $row['id_detail'],
            'real_id'       => $row['id_detail'],
            'tipe'          => 'supplier',

            'label'         => date('d M Y', strtotime($row['tanggal'])) . ' | SUPPLIER',

            'total_atp'     => (float)$row['total_atp'],
            'total_mixer'   => (float)$row['total_mixer'],
            'saldo_atp'     => (float)$row['saldo_atp']
        ];
    }
}

/* ===================================================
   2. STOK REPRO
=================================================== */
if ($isRepro) {

    $qRepro = mysqli_query($conn, "

    SELECT
        a.id_at,
        a.tanggal,

        a.atp AS total_atp,

        /* TOTAL MIXER TERPAKAI */
        IFNULL((
            SELECT SUM(pd.mixer)
            FROM produksi_detail pd
            WHERE pd.id_mutasi_detail = CONCAT('repro_', a.id_at)
        ),0) AS total_mixer,

        /* SALDO ATP */
        (
            a.atp

            -

            IFNULL((
                SELECT SUM(pd.mixer)
                FROM produksi_detail pd
                WHERE pd.id_mutasi_detail = CONCAT('repro_', a.id_at)
            ),0)

        ) AS saldo_atp

    FROM at_detail a

    JOIN supplier s
    ON s.id_supplier = a.id_supplier

    WHERE s.nama_supplier = 'REPRO BRIKET'
    AND a.atp > 0

    HAVING saldo_atp > 0

    ORDER BY a.tanggal ASC

    ");

    if (!$qRepro) {

        echo json_encode([
            'status'  => 'error',
            'message' => mysqli_error($conn)
        ]);

        exit;
    }

    while ($row = mysqli_fetch_assoc($qRepro)) {

        $data[] = [
            'id'            => 'repro_' . $row['id_at'],
            'real_id'       => 'repro_' . $row['id_at'],
            'tipe'          => 'repro',

            'label'         => date('d M Y', strtotime($row['tanggal'])) . ' | REPRO ATP',

            'total_atp'     => (float)$row['total_atp'],
            'total_mixer'   => (float)$row['total_mixer'],
            'saldo_atp'     => (float)$row['saldo_atp']
        ];
    }
}

/* =========================
   OUTPUT JSON
========================= */
echo json_encode([
    'status' => 'ok',
    'stok'   => $data
]);