<?php
require '../config/init.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if(isset($_POST['simpan'])){

    $tanggal    = $_POST['tanggal'] ?? '';
    // id_barang coming from select; expect numeric id
    $id_barang  = isset($_POST['id_barang']) ? (int) $_POST['id_barang'] : 0;
    $jumlah     = isset($_POST['jumlah']) ? str_replace(',', '.', $_POST['jumlah']) : '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if(empty($tanggal) || empty($id_barang) || $jumlah === ''){
        header('Location: tambahan_pemakaian_at.php?error=1');
        exit;
    }

    $jumlah = (float) $jumlah;

    $stmt = mysqli_prepare($conn,"
        INSERT INTO tambahan
        (id_barang, jumlah, keterangan, tanggal)
        VALUES (?, ?, ?, ?)
    ");

    // types: id_barang (i), jumlah (d), keterangan (s), tanggal (s)
    mysqli_stmt_bind_param($stmt, 'idss',
        $id_barang,
        $jumlah,
        $keterangan,
        $tanggal
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: tambahan_pemakaian_at.php?success=1');
    exit;
}

header('Location: tambahan_pemakaian_at.php');
exit;