<?php
$password = 'admin';
// PASSWORD_DEFAULT akan menggunakan algoritma BCRYPT (paling aman saat ini)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Password Asli: " . $password . "\n";
echo "Password Hash: " . $hashedPassword . "\n";

// Untuk memverifikasi (saat login)
$inputUser = 'kataSandiRahasia123';
if (password_verify($inputUser, $hashedPassword)) {
    echo "Kata sandi cocok!";
} else {
    echo "Kata sandi salah!";
}
?>