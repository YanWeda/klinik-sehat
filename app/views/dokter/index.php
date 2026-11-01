<?php
$judulHalaman = 'Data Dokter';
require __DIR__ . '/../layout/header.php';

$pesan = [
    'tambah' => 'Data dokter berhasil ditambahkan.',
    'update' => 'Data dokter berhasil diperbarui.',
    'hapus'  => 'Data dokter berhasil dihapus.',
];
$status = $_GET['status'] ?? '';
?>
<div class="judul-halaman">
    <div><h1>Data Dokter</h1><p class="sub">Kelola daftar dokter yang praktik di klinik.</p></div>
</div>

<?php if (isset($pesan[$status])): ?>
    <div class="alert alert-sukses"><?= $pesan[$status] ?></div>
<?php endif; ?>

<section class="card">
    <h2><?= $dokterEdit ? 'Edit Dokter' : 'Tambah Dokter' ?></h2>
    <form method="POST" action="index.php?action=dokter">
        <?php if ($dokterEdit): ?>
            <input type="hidden" name="id_dokter" value="<?= (int) $dokterEdit['id_dokter'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="field">
                <label for="nama_dokter">Nama dokter</label>
                <input type="text" id="nama_dokter" name="nama_dokter" required value="<?= $dokterEdit ? e($dokterEdit['nama_dokter']) : '' ?>">
            </div>
            <div class="field">
                <label for="spesialis">Spesialis</label>
                <input type="text" id="spesialis" name="spesialis" required value="<?= $dokterEdit ? e($dokterEdit['spesialis']) : '' ?>">
            </div>
            <div class="field">
                <label for="jenis_kelamin">Jenis kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <?php foreach (['Laki-laki', 'Perempuan'] as $jk): ?>
                        <option value="<?= $jk ?>" <?= ($dokterEdit && $dokterEdit['jenis_kelamin'] === $jk) ? 'selected' : '' ?>><?= $jk ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-aksi">
            <?php if ($dokterEdit): ?>
                <button type="submit" name="submit_update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= e(bangunUrl(['action' => 'dokter', 'q' => $q, 'hal' => $hal])) ?>" class="btn">Batal</a>
            <?php else: ?>
                <button type="submit" name="submit_tambah" class="btn btn-primary">Tambah Dokter</button>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h2>Daftar Dokter</h2>
    <?= kotakCari(['action' => 'dokter'], $q, 'Cari nama dokter atau spesialis...') ?>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr><th>No</th><th>ID</th><th>Nama Dokter</th><th>Spesialis</th><th>Jenis Kelamin</th><th>Aksi</th></tr>
            <?php if (empty($daftarDokter)): ?>
                <tr><td colspan="6" class="kosong"><?= $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data dokter' ?></td></tr>
            <?php else: foreach ($daftarDokter as $i => $d): ?>
                <tr>
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= e($d['id_dokter']) ?></td>
                    <td><?= e($d['nama_dokter']) ?></td>
                    <td><?= e($d['spesialis']) ?></td>
                    <td><?= e($d['jenis_kelamin']) ?></td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-sm" href="<?= e(bangunUrl(['action' => 'dokter', 'mode' => 'edit', 'id' => $d['id_dokter'], 'q' => $q, 'hal' => $hal])) ?>">Edit</a>
                            <form method="POST" action="index.php?action=dokter" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus dokter ini?');">
                                <input type="hidden" name="id_dokter" value="<?= (int) $d['id_dokter'] ?>">
                                <button type="submit" name="submit_hapus" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>
    <?= paginasi($total, $hal, ['action' => 'dokter', 'q' => $q]) ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>