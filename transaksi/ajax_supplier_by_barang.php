<?php
require '../config/init.php';

$id_barang = $_GET['id_barang'] ?? 0;

$q = mysqli_query($conn, "
SELECT DISTINCT s.id_supplier, s.nama_supplier
FROM mutasi_detail md
JOIN supplier s ON s.id_supplier = md.id_supplier
WHERE md.id_barang = '$id_barang'
ORDER BY s.nama_supplier
");

echo '<option value="">-- Pilih Supplier --</option>';

while($d = mysqli_fetch_assoc($q)) {
    echo "<option value='{$d['id_supplier']}'>{$d['nama_supplier']}</option>";
}