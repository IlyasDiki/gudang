<?php
session_start();
$_SESSION['login'] = true;
require 'config/init.php';

// Cek jenis_mutasi untuk mutasi di Hasil Bongkar Karantina
$query = mysqli_query($conn, "
SELECT m.id_mutasi, jm.tipe, jm.nama_jenis, m.arah, m.tanggal, md.jumlah, b.nama_barang
FROM mutasi m
JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
JOIN barang b ON b.id_barang = md.id_barang
LEFT JOIN kelompok_barang kb2 ON kb2.id_kelompok = b.id_kelompok
LEFT JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis
WHERE kb2.nama_kelompok = 'Hasil Bongkar Karantina'
ORDER BY m.tanggal
");
echo "Mutasi untuk Hasil Bongkar Karantina:\n";
while($row = mysqli_fetch_assoc($query)){
    echo $row['tanggal'] . ' - ' . $row['nama_jenis'] . ' (' . $row['tipe'] . ') - ' . $row['arah'] . ' - ' . $row['nama_barang'] . ' - ' . $row['jumlah'] . PHP_EOL;
}

// Sama untuk Hasil Bongkar Oven
$query2 = mysqli_query($conn, "
SELECT m.id_mutasi, jm.tipe, jm.nama_jenis, m.arah, m.tanggal, md.jumlah, b.nama_barang
FROM mutasi m
JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
JOIN barang b ON b.id_barang = md.id_barang
LEFT JOIN kelompok_barang kb2 ON kb2.id_kelompok = b.id_kelompok
LEFT JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis
WHERE kb2.nama_kelompok = 'Hasil Bongkar Oven'
ORDER BY m.tanggal
");
echo "\nMutasi untuk Hasil Bongkar Oven:\n";
while($row = mysqli_fetch_assoc($query2)){
    echo $row['tanggal'] . ' - ' . $row['nama_jenis'] . ' (' . $row['tipe'] . ') - ' . $row['arah'] . ' - ' . $row['nama_barang'] . ' - ' . $row['jumlah'] . PHP_EOL;
}
?>