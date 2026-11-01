<?php
if (!defined('CSS_LOADED')) define('CSS_LOADED', true);
$judulHalaman = $judulHalaman ?? 'Beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman) ?> - <?= e(NAMA_KLINIK) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="role-<?= e(roleSaatIni()) ?>">
<?php require __DIR__ . '/../navigasi.php'; ?>
<main class="container">
<?php if (roleSaatIni() === 'dokter' && idDokterSaatIni() === 0): ?>
    <div class="alert alert-info">Akun dokter Anda belum terhubung ke data dokter, sehingga antrian dan konsultasi belum tampil. Hubungi admin untuk menghubungkannya.</div>
<?php endif; ?>