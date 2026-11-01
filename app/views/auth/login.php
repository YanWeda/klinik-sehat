<?php
$status = $_GET['status'] ?? '';
$pesan = [
    'registered'        => 'Registrasi pasien berhasil, silakan login.',
    'registered_dokter' => 'Registrasi dokter berhasil, silakan login.',
    'logout'            => 'Anda sudah logout.',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= e(NAMA_KLINIK) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth">
    <div class="auth-kartu">
        <div class="logo">+</div>
        <h2><?= e(NAMA_KLINIK) ?></h2>
        <p class="sub">Masuk untuk mengakses sistem klinik</p>

        <?php if (isset($pesan[$status])): ?>
            <div class="alert alert-sukses"><?= $pesan[$status] ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login">
            <div class="field">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required autofocus value="<?= e($namaInput) ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="submit_login" class="btn btn-primary">Masuk</button>
        </form>

        <p class="auth-pemisah">Belum punya akun? Daftar sebagai:</p>
        <div class="auth-daftar">
            <a class="btn btn-outline" href="index.php?action=register">Pasien</a>
            <a class="btn btn-outline" href="index.php?action=register_dokter">Dokter</a>
        </div>
    </div>
</body>
</html>