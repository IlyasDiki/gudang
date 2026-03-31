<?php
require_once __DIR__ . '/../config/init.php';

$id = $_GET['id_barang'];

$q = mysqli_query($conn,"
SELECT *
FROM supplier
ORDER BY nama_supplier
");

echo "<option value=''>-- Pilih Supplier --</option>";

while($r=mysqli_fetch_assoc($q)){

echo "<option value='$r[id_supplier]'>$r[nama_supplier]</option>";

}