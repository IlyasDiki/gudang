<?php
session_start();
require '../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND aktif=1");
$user = mysqli_fetch_assoc($q);

if ($user && password_verify($password, $user['password'])) {

  $_SESSION['login']   = true;
  $_SESSION['id_pengguna'] = $user['id_pengguna'];
  $_SESSION['nama']    = $user['nama'];
  $_SESSION['role']    = $user['role'];

  header("Location: /gudang/index.php");
  exit;

} else {
  header("Location: login.php?error=1");
  exit;
}
