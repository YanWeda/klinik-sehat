<?php
$judulHalaman = 'Kelola User';
require __DIR__ . '/../layout/header.php';

$pesanFlash = [
    'tambah' => 'Akun berhasil ditambahkan.',
    'update' => 'Akun berhasil diperbarui.',
    'hapus'  => 'Akun berhasil dihapus.',
];
$pesan = $_GET['pesan'] ?? '';
$roleForm = $userEdit ? ($userEdit['role'] ?? 'user') : 'user';
?>
<div class="judul-halaman">
    <div>
        <h1>Kelola User</h1>
        <p class="sub">Atur akun beserta perannya: <strong>Admin</strong> (akses penuh), <strong>Dokter</strong> (melayani antrian &amp; konsultasi), dan <strong>User</strong> (pasien).</p>
    </div>
</div>

<?php if (isset($pesanFlash[$pesan])): ?>
    <div class="alert alert-sukses"><?= $pesanFlash[$pesan] ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<section class="card">
    <h2><?= $userEdit ? 'Edit Akun: ' . e($userEdit['nama']) : 'Tambah Akun' ?></h2>
    <form method="POST" action="index.php?action=user">
        <?php if ($userEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $userEdit['id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <?php if (!$userEdit): ?>
                <div class="field">
                    <label for="nama">Nama (untuk login)</label>
                    <input type="text" id="nama" name="nama" maxlength="50" required>
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="password"><?= $userEdit ? 'Password baru' : 'Password' ?></label>
                <input type="password" id="password" name="password" minlength="6" <?= $userEdit ? '' : 'required' ?>>
                <?php if ($userEdit): ?><div class="hint">Kosongkan jika tidak ingin mengganti password.</div><?php endif; ?>
            </div>
            <div class="field">
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    <?php foreach (['admin' => 'Admin', 'dokter' => 'Dokter', 'user' => 'User (pasien)'] as $val => $lab): ?>
                        <option value="<?= $val ?>" <?= $roleForm === $val ? 'selected' : '' ?>><?= $lab ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" id="wrap-dokter">
                <label for="id_dokter">Hubungkan ke dokter</label>
                <select id="id_dokter" name="id_dokter">
                    <option value="">-- Pilih dokter --</option>
                    <?php foreach ($daftarDokter as $d): ?>
                        <option value="<?= (int) $d['id_dokter'] ?>" <?= ($userEdit && (int) ($userEdit['id_dokter'] ?? 0) === (int) $d['id_dokter']) ? 'selected' : '' ?>>
                            <?= e($d['nama_dokter']) ?> (<?= e($d['spesialis']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" id="wrap-pasien">
                <label for="id_pasien">Hubungkan ke data pasien</label>
                <select id="id_pasien" name="id_pasien">
                    <option value="">-- Buat otomatis --</option>
                    <?php foreach ($daftarPasien as $p): ?>
                        <option value="<?= (int) $p['id_pasien'] ?>" <?= ($userEdit && (int) ($userEdit['id_pasien'] ?? 0) === (int) $p['id_pasien']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-aksi">
            <?php if ($userEdit): ?>
                <button type="submit" name="submit_update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= e(bangunUrl(['action' => 'user', 'q' => $q, 'hal' => $hal])) ?>" class="btn">Batal</a>
            <?php else: ?>
                <button type="submit" name="submit_tambah" class="btn btn-primary">Tambah Akun</button>
            <?php endif; ?>
        </div>
    </form>
    <script>
        // Tampilkan pilihan tautan sesuai peran
        function aturRole() {
            var r = document.getElementById('role').value;
            document.getElementById('wrap-dokter').hidden = (r !== 'dokter');
            document.getElementById('wrap-pasien').hidden = (r !== 'user');
        }
        document.getElementById('role').addEventListener('change', aturRole);
        aturRole();
    </script>
</section>

<section class="card">
    <h2>Daftar Akun</h2>
    <?= kotakCari(['action' => 'user'], $q, 'Cari nama, peran, atau data terhubung...') ?>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr><th>No</th><th>Nama</th><th>Peran</th><th>Terhubung ke</th><th>Aksi</th></tr>
            <?php if (empty($daftarUser)): ?>
                <tr><td colspan="5" class="kosong">Tidak ada data yang cocok dengan pencarian.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftarUser as $i => $u): $diri = ((int) $u['id'] === (int) $_SESSION['user_id']); ?>
                <tr>
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= e($u['nama']) ?><?= $diri ? ' <span class="sub">(Anda)</span>' : '' ?></td>
                    <td><?= badgeRole($u['role'] ?? 'user') ?></td>
                    <td>
                        <?php if (($u['role'] ?? '') === 'dokter'): ?>
                            <?= $u['nama_dokter'] ? e($u['nama_dokter']) : '<span class="sub">belum terhubung</span>' ?>
                        <?php elseif (($u['role'] ?? '') === 'user'): ?>
                            <?= $u['nama_pasien'] ? e($u['nama_pasien']) : '<span class="sub">dibuat otomatis saat login</span>' ?>
                        <?php else: ?>
                            <span class="sub">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-sm" href="<?= e(bangunUrl(['action' => 'user', 'mode' => 'edit', 'id' => $u['id'], 'q' => $q, 'hal' => $hal])) ?>">Edit</a>
                            <?php if (!$diri): ?>
                                <form method="POST" action="index.php?action=user" class="inline"
                                      onsubmit="return confirm('Hapus akun ini?');">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" name="submit_hapus" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?= paginasi($total, $hal, ['action' => 'user', 'q' => $q]) ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>