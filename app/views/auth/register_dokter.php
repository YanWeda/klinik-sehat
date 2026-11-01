<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Dokter - <?= e(NAMA_KLINIK) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth">
    <div class="auth-kartu">
        <div class="logo">+</div>
        <h2>Daftar Sebagai Dokter</h2>
        <p class="sub"><?= e(NAMA_KLINIK) ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register_dokter">
            <div class="field">
                <label for="nama">Username</label>
                <input type="text" id="nama" name="nama" maxlength="50" required autofocus value="<?= e($namaInput) ?>" placeholder="Contoh: dr. Ayu Lestari">
                <div class="hint">Dipakai untuk login dan tampil sebagai nama dokter.</div>
            </div>
            <div class="field">
                <label for="spesialis">Spesialis</label>
                <input type="text" id="spesialis" name="spesialis" maxlength="100" required value="<?= e($spesialisInput) ?>" placeholder="Contoh: Dokter Umum">
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
            <?php if ($perluKode): ?>
                <div class="field">
                    <label for="kode">Kode registrasi dokter</label>
                    <input type="password" id="kode" name="kode" required autocomplete="off">
                    <div class="hint">Kode diberikan oleh admin klinik.</div>
                </div>
            <?php endif; ?>
            <button type="submit" name="submit_register_dokter" class="btn btn-primary">Daftar sebagai Dokter</button>
        </form>

        <p class="auth-bawah">Sudah punya akun? <a href="index.php?action=login">Login</a><br>Pasien? <a href="index.php?action=register">Daftar sebagai pasien</a></p>
    </div>
</body>
</html>