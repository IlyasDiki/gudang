<?php
include "koneksi.php";

$sql = "INSERT INTO buku_kerja_at_powder (
    tgl_terima, jumlah_terima,
    tgl, sortir, saldo, ma, aa, b_mentah, air, atp,
    kand_ma, kand_aa, kand_bm, susut_timbang, total_susut,
    jumlah_produksi, tgl_produksi, mixer, saldo_produksi
) VALUES (
    :tgl_terima, :jumlah_terima,
    :tgl, :sortir, :saldo, :ma, :aa, :b_mentah, :air, :atp,
    :kand_ma, :kand_aa, :kand_bm, :susut_timbang, :total_susut,
    :jumlah_produksi, :tgl_produksi, :mixer, :saldo_produksi
)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':tgl_terima' => $_POST['tgl_terima'],
    ':jumlah_terima' => $_POST['jumlah_terima'],
    ':tgl' => $_POST['tgl'],
    ':sortir' => $_POST['sortir'],
    ':saldo' => $_POST['saldo'],
    ':ma' => $_POST['ma'],
    ':aa' => $_POST['aa'],
    ':b_mentah' => $_POST['b_mentah'],
    ':air' => $_POST['air'],
    ':atp' => $_POST['atp'],
    ':kand_ma' => $_POST['kand_ma'],
    ':kand_aa' => $_POST['kand_aa'],
    ':kand_bm' => $_POST['kand_bm'],
    ':susut_timbang' => $_POST['susut_timbang'],
    ':total_susut' => $_POST['total_susut'],
    ':jumlah_produksi' => $_POST['jumlah_produksi'],
    ':tgl_produksi' => $_POST['tgl_produksi'],
    ':mixer' => $_POST['mixer'],
    ':saldo_produksi' => $_POST['saldo_produksi'],
]);

header("Location: buku_kerja_at_list.php");