<?php
require '../config/init.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = $_POST['id_at'] ?? 0;

/* ================= AMBIL DATA LAMA ================= */
$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM at_detail WHERE id_at = '$id'
"));

if (!$data) {
    die("Data tidak ditemukan");
}

/* ================= DATA BARU ================= */
$sortir    = $_POST['sortir'] ?? 0;
$ma        = $_POST['ma'] ?? 0;
$aa        = $_POST['aa'] ?? 0;
$b_mentah  = $_POST['b_mentah'] ?? 0;
$air       = $_POST['air'] ?? 0;
$atp       = $_POST['atp'] ?? 0;

/* ================= HITUNG TOTAL ================= */
$total_lama = $data['sortir'] + $data['ma'] + $data['aa'] +
              $data['b_mentah'] + $data['air'] + $data['atp'];

$total_baru = $sortir + $ma + $aa + $b_mentah + $air + $atp;

/* ================= HITUNG SELISIH ================= */
$selisih = $total_lama - $total_baru;

/*
Jika:
selisih positif → stok dikembalikan
selisih negatif → stok dikurangi lagi
*/

/* ================= TRANSAKSI ================= */
mysqli_begin_transaction($conn);

try {

    // ================= UPDATE STOK =================
    mysqli_query($conn, "
        UPDATE mutasi_detail 
        SET jumlah = jumlah + ($selisih)
        WHERE id_detail = {$data['id_mutasi_detail']}
    "); 

    // ================= UPDATE DATA =================
    mysqli_query($conn, "
        UPDATE at_detail SET
            sortir='$sortir',
            ma='$ma',
            aa='$aa',
            b_mentah='$b_mentah',
            air='$air',
            atp='$atp'
        WHERE id_at = '$id'
    ");

    mysqli_commit($conn);

    echo "<script>
        alert('Data berhasil diupdate');
        window.location='pemakaian_at_kelola.php';
    </script>";

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "<script>
        alert('Gagal update');
        window.history.back();
    </script>";
}