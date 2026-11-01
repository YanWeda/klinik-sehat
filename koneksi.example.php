<?php
// CONTOH koneksi database.
// Salin file ini menjadi "koneksi.php", lalu sesuaikan dengan pengaturan MySQL Anda.
// (koneksi.php sengaja tidak ikut diunggah ke GitHub agar kata sandi tidak bocor.)

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // kesalahan database dilempar sebagai exception

$host     = "localhost";
$user     = "root";      // Laragon / XAMPP bawaan: root
$password = "";          // Laragon / XAMPP bawaan: kosong
$database = "klinik";

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");