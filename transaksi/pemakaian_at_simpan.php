<?php
require '../config/init.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =======================
// DATA FORM
// =======================
$tanggal     = $_POST['tanggal'] ?? null;
$idBarang    = $_POST['id_barang'] ?? null;
$idSupplier  = $_POST['id_supplier'] ?? null;
$idDetail    = $_POST['id_mutasi_detail'] ?? null;

$sortir    = floatval($_POST['sortir'] ?? 0);
$ma        = floatval($_POST['ma'] ?? 0);
$aa        = floatval($_POST['aa'] ?? 0);
$bMentah   = floatval($_POST['b_mentah'] ?? 0);
$air       = floatval($_POST['air'] ?? 0);
$atp       = floatval($_POST['atp'] ?? 0);

$user = $_SESSION['id_pengguna'] ?? null;

// =======================
// VALIDASI
// =======================
if (empty($tanggal) || empty($idBarang) || empty($idSupplier) || empty($idDetail)) {
    die('Data tidak lengkap');
}

$totalPakai = $sortir + $ma + $aa + $bMentah + $air + $atp;

if ($totalPakai <= 0) {
    die('Tidak ada pemakaian');
}

// =======================
// CEK SISA STOK BATCH
// =======================
$qCek = mysqli_query($conn, "
SELECT 
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
WHERE md.id_detail = '$idDetail'
");

$data = mysqli_fetch_assoc($qCek);

if (!$data) {
    die('Batch tidak ditemukan');
}

if ($totalPakai > $data['sisa']) {
    die('❌ Stok batch tidak cukup! Sisa: ' . $data['sisa']);
}

// =======================
// JENIS MUTASI
// =======================
$qJenis = mysqli_query($conn,"
SELECT id_jenis FROM jenis_mutasi WHERE kode_jenis='AT'
");
$idJenis = mysqli_fetch_assoc($qJenis)['id_jenis'];

// =======================
// TRANSAKSI
// =======================
mysqli_begin_transaction($conn);

try {

    // HEADER
    mysqli_query($conn,"
    INSERT INTO mutasi
    (tanggal, id_jenis, keterangan, dibuat_oleh, id_supplier)
    VALUES
    ('$tanggal','$idJenis','Pemakaian AT','$user','$idSupplier')
    ");

    // DETAIL (TANPA FIFO)
    mysqli_query($conn,"
    INSERT INTO at_detail
    (
        id_mutasi_detail,
        id_barang,
        id_supplier,
        sortir,
        ma,
        aa,
        b_mentah,
        air,
        atp,
        tanggal
    )
    VALUES
    (
        '$idDetail',
        '$idBarang',
        '$idSupplier',
        '$sortir',
        '$ma',
        '$aa',
        '$bMentah',
        '$air',
        '$atp',
        '$tanggal'
    )
    ");

    mysqli_commit($conn);

    header("Location: pemakaian_at.php?success=1");
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);
    die("Gagal simpan: " . $e->getMessage());
}