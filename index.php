<?php

session_start();

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/AboutController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/AntrianController.php';
require_once __DIR__ . '/app/controllers/KonsultasiController.php';
require_once __DIR__ . '/app/controllers/DokterController.php';
require_once __DIR__ . '/app/controllers/PasienController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/ProfilController.php';


$AuthController = new AuthController($conn);
$AboutController = new AboutController();
$DashboardController = new DashboardController($conn);
$AntrianController = new AntrianController($conn);
$KonsultasiController = new KonsultasiController($conn);
$DokterController = new DokterController($conn);
$PasienController = new PasienController($conn);
$UserController = new UserController($conn);
$ProfilController = new ProfilController($conn);

$action = $_GET['action'] ?? 'index';

// Halaman yang boleh dibuka tanpa login
$halamanPublik = ['login', 'register', 'register_dokter'];

// Sudah login: baca ulang peran dari database (akun dihapus / diganti peran langsung berlaku)
if (isset($_SESSION['user_id']) && !$AuthController->segarkanSesi()) {
    header("Location: index.php?action=login");
    exit;
}

if (!isset($_SESSION['user_id']) && !in_array($action, $halamanPublik)) {
    header("Location: index.php?action=login");
    exit;
}

// Pengecekan hak akses berdasarkan peran (admin boleh semua)
if (isset($_SESSION['user_id']) && array_key_exists($action, aksesHalaman()) && !bolehAkses($action)) {
    http_response_code(403);
    require __DIR__ . '/app/views/akses_ditolak.php';
    exit;
}

if ($action === 'login') {
    $AuthController->login();
} elseif ($action === 'register') {
    $AuthController->register();
} elseif ($action === 'register_dokter') {
    $AuthController->registerDokter();
} elseif ($action === 'logout') {
    $AuthController->logout();
} elseif ($action === 'index') {
    $DashboardController->index();
} elseif ($action === 'antrian') {
    $AntrianController->index();
} elseif ($action === 'konsultasi') {
    $KonsultasiController->index();
} elseif ($action === 'about') {
    $AboutController->index();
} elseif ($action === 'dokter') {
    $DokterController->index();
} elseif ($action === 'pasien') {
    $PasienController->index();
} elseif ($action === 'user') {
    $UserController->index();
} elseif ($action === 'profil') {
    $ProfilController->index();
} else {
    header("Location: index.php");
    exit;
}

?>