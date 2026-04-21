<?php
require '../config/init.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* =========================
   SIMPAN DATA
========================= */
if(isset($_POST['simpan'])){
    $tanggal     = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
    $id_supplier   = isset($_POST['id_supplier']) ? (int) $_POST['id_supplier'] : 0;
    $jumlah   = isset($_POST['jumlah']) ? str_replace(',', '.', $_POST['jumlah']) : '';
    $keterangan  = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';

    // Basic validation
    if(empty($tanggal) || $id_supplier <= 0 || $jumlah === ''){
        echo "<script>alert('Mohon isi semua field yang diperlukan'); window.history.back();</script>";
        exit;
    }

    // Cast jumlah to float
    $jumlah = (float) $jumlah;

    // Use prepared statement to avoid injection
    $stmt = mysqli_prepare($conn, "INSERT INTO stok_fisik_at (tanggal, id_supplier, jumlah, keterangan) VALUES (?, ?, ?, ?)");
    if(!$stmt){
        echo "Prepare failed: ".mysqli_error($conn);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'sids', $tanggal, $id_supplier, $jumlah, $keterangan);
    $exec = mysqli_stmt_execute($stmt);

    if($exec){
        mysqli_stmt_close($stmt);
        header('Location: stok_fisik_at.php?success=1');
        exit;
    }else{
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        echo "Error: ". $err;
    }
}