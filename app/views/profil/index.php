<?php
$judulHalaman = 'Profil Saya';
require __DIR__ . '/../layout/header.php';
?>
<div class="judul-halaman">
    <div><h1>Profil Saya</h1><p class="sub">Data diri yang dipakai klinik saat Anda berobat.</p></div>
</div>

<?php if (($_GET['pesan'] ?? '') === 'update'): ?>
    <div class="alert alert-sukses">Profil berhasil diperbarui.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<section class="card" style="max-width:640px;">
    <form method="POST" action="index.php?action=profil">
        <div class="form-grid">
            <div class="field penuh">
                <label>Nama</label>
                <input type="text" value="<?= e($pasien['nama']) ?>" disabled>
            </div>
            <div class="field">
                <label for="jenis_kelamin">Jenis kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <?php if (!in_array($pasien['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)): ?>
                        <option value="">-- Pilih --</option>
                    <?php endif; ?>
                    <?php foreach (['Laki-laki', 'Perempuan'] as $jk): ?>
                        <option value="<?= $jk ?>" <?= $pasien['jenis_kelamin'] === $jk ? 'selected' : '' ?>><?= $jk ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field penuh">
                <label for="keluhan">Keluhan terakhir</label>
                <input type="text" id="keluhan" name="keluhan" maxlength="255" required
                       value="<?= e($pasien['keluhan'] === '-' ? '' : $pasien['keluhan']) ?>" placeholder="Contoh: Demam sejak kemarin">
            </div>
        </div>
        <div class="form-aksi"><button type="submit" name="submit_update" class="btn btn-primary">Simpan</button></div>
    </form>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>