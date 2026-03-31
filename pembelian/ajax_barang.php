<?php
require_once __DIR__ . '/../config/init.php';

$id = $_GET['id_kelompok'];

$q = mysqli_query($conn,"
SELECT *
FROM barang
WHERE id_kelompok='$id'
ORDER BY nama_barang
");

echo "<option value=''>-- Pilih Barang --</option>";

while($r=mysqli_fetch_assoc($q)){

echo "<option value='$r[id_barang]'>$r[nama_barang]</option>";

}