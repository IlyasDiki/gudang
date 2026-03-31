<?php
// config/init.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/gudang/');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}