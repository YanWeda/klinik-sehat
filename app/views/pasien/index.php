<?php
$judulHalaman = 'Data Pasien';
require __DIR__ . '/../layout/header.php';

$pesan = [
    'tambah' => 'Data pasien berhasil ditambahkan.',
    'update' => 'Data pasien berhasil diperbarui.',
    'hapus'  => 'Data pasien berhasil dihapus.',
];
$status = $_GET['status'] ?? '';
?>
<div class="judul-halaman">
    <div>
        <h1><?= $bisaUbah ? 'Data Pasien' : 'Pasien Saya' ?></h1>
        <p class="sub"><?= $bisaUbah ? 'Kelola data pasien yang terdaftar di klinik.' : 'Pasien yang pernah antri atau berkonsultasi dengan Anda (hanya lihat).' ?></p>
    </div>
</div>

<?php if (isset($pesan[$status])): ?>
    <div class="alert alert-sukses"><?= $pesan[$status] ?></div>
<?php endif; ?>

<?php if ($bisaUbah): ?>
<section class="card">
    <h2><?= $pasienEdit ? 'Edit Pasien' : 'Tambah Pasien' ?></h2>
    <form method="POST" action="index.php?action=pasien">
        <?php if ($pasienEdit): ?>
            <input type="hidden" name="id_pasien" value="<?= (int) $pasienEdit['id_pasien'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="field">
                <label for="nama">Nama pasien</label>
                <input type="text" id="nama" name="nama" required value="<?= $pasienEdit ? e($pasienEdit['nama']) : '' ?>">
            </div>
            <div class="field">
                <label for="jenis_kelamin">Jenis kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <?php foreach (['Laki-laki', 'Perempuan'] as $jk): ?>
                        <option value="<?= $jk ?>" <?= ($pasienEdit && $pasienEdit['jenis_kelamin'] === $jk) ? 'selected' : '' ?>><?= $jk ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="keluhan">Keluhan</label>
                <input type="text" id="keluhan" name="keluhan" required value="<?= $pasienEdit ? e($pasienEdit['keluhan']) : '' ?>">
            </div>
        </div>
        <div class="form-aksi">
            <?php if ($pasienEdit): ?>
                <button type="submit" name="submit_update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= e(bangunUrl(['action' => 'pasien', 'q' => $q, 'hal' => $hal])) ?>" class="btn">Batal</a>
            <?php else: ?>
                <button type="submit" name="submit_tambah" class="btn btn-primary">Tambah Pasien</button>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <h2>Daftar Pasien</h2>
    <?= kotakCari(['action' => 'pasien'], $q, 'Cari nama, keluhan, atau jenis kelamin...') ?>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th><th>ID</th><th>Nama</th><th>Jenis Kelamin</th><th>Keluhan</th>
                <?php if ($bisaUbah): ?><th>Aksi</th><?php endif; ?>
            </tr>
            <?php if (empty($daftarPasien)): ?>
                <tr><td colspan="<?= $bisaUbah ? 6 : 5 ?>" class="kosong"><?= $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data pasien' ?></td></tr>
            <?php else: foreach ($daftarPasien as $i => $p): ?>
                <tr>
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= e($p['id_pasien']) ?></td>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['jenis_kelamin']) ?></td>
                    <td><?= e($p['keluhan']) ?></td>
                    <?php if ($bisaUbah): ?>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-sm" href="<?= e(bangunUrl(['action' => 'pasien', 'mode' => 'edit', 'id' => $p['id_pasien'], 'q' => $q, 'hal' => $hal])) ?>">Edit</a>
                            <form method="POST" action="index.php?action=pasien" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus pasien ini?');">
                                <input type="hidden" name="id_pasien" value="<?= (int) $p['id_pasien'] ?>">
                                <button type="submit" name="submit_hapus" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>
    <?= paginasi($total, $hal, ['action' => 'pasien', 'q' => $q]) ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>