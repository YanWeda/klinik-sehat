<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi - <?= e(NAMA_KLINIK) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth">
    <div class="auth-kartu">
        <div class="logo">+</div>
        <h2>Daftar Sebagai Pasien</h2>
        <p class="sub"><?= e(NAMA_KLINIK) ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register">
            <div class="field">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" maxlength="50" required autofocus value="<?= e($namaInput) ?>">
            </div>
            <div class="field">
                <label for="jenis_kelamin">Jenis kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach (['Laki-laki', 'Perempuan'] as $jk): ?>
                        <option value="<?= $jk ?>" <?= $jkInput === $jk ? 'selected' : '' ?>><?= $jk ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="password">Password (minimal 6 karakter)</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <div class="field">
                <label for="konfirmasi">Konfirmasi Password</label>
                <input type="password" id="konfirmasi" name="konfirmasi" minlength="6" required>
            </div>
            <button type="submit" name="submit_register" class="btn btn-primary">Daftar</button>
        </form>

        <p class="auth-bawah">Sudah punya akun? <a href="index.php?action=login">Login</a><br>Seorang dokter? <a href="index.php?action=register_dokter">Daftar sebagai dokter</a></p>
    </div>
</body>
</html>