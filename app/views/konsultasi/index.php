<?php
$judulHalaman = 'Konsultasi Online';
require __DIR__ . '/../layout/header.php';
$pesan = $_GET['pesan'] ?? '';
$subJudul = [
    'admin'  => 'Pantau seluruh konsultasi online antara pasien dan dokter.',
    'dokter' => 'Konsultasi online dari pasien yang ditujukan kepada Anda.',
    'user'   => 'Konsultasi dengan dokter melalui chat tanpa harus datang ke klinik.',
][roleSaatIni()] ?? '';
?>
<div class="judul-halaman">
    <div>
        <h1>Konsultasi Online</h1>
        <p class="sub"><?= e($subJudul) ?></p>
    </div>
</div>

<?php if ($pesan === 'hapus'): ?>
    <div class="alert alert-sukses">Konsultasi berhasil dihapus.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($bisaMulai): ?>
<section class="card">
    <h2>Mulai Konsultasi Baru</h2>
    <?php if (roleSaatIni() === 'admin' && (empty($daftarPasien) || empty($daftarDokter))): ?>
        <div class="alert alert-info">
            Data <?= empty($daftarPasien) ? '<a href="index.php?action=pasien">pasien</a>' : '' ?>
            <?= (empty($daftarPasien) && empty($daftarDokter)) ? 'dan' : '' ?>
            <?= empty($daftarDokter) ? '<a href="index.php?action=dokter">dokter</a>' : '' ?>
            masih kosong. Tambahkan dulu sebelum memulai konsultasi.
        </div>
    <?php elseif (empty($daftarDokter)): ?>
        <div class="alert alert-info">Belum ada dokter yang terdaftar. Silakan coba lagi nanti.</div>
    <?php else: ?>
    <form method="POST" action="index.php?action=konsultasi">
        <div class="form-grid">
            <?php if (roleSaatIni() === 'admin'): ?>
                <div class="field">
                    <label for="id_pasien">Pasien</label>
                    <select name="id_pasien" id="id_pasien" required>
                        <option value="">-- Pilih pasien --</option>
                        <?php foreach ($daftarPasien as $p): ?>
                            <option value="<?= (int) $p['id_pasien'] ?>"><?= e($p['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="field">
                    <label>Atas nama</label>
                    <input type="text" value="<?= e($_SESSION['user_nama']) ?>" disabled>
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="id_dokter">Dokter</label>
                <select name="id_dokter" id="id_dokter" required>
                    <option value="">-- Pilih dokter --</option>
                    <?php foreach ($daftarDokter as $d): ?>
                        <option value="<?= (int) $d['id_dokter'] ?>"><?= e($d['nama_dokter']) ?> (<?= e($d['spesialis']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field penuh">
                <label for="topik">Topik konsultasi</label>
                <input type="text" name="topik" id="topik" maxlength="150" placeholder="Contoh: Demam dan batuk sejak 3 hari" required>
            </div>
            <div class="field penuh">
                <label for="pesan_awal">Keluhan / pesan pertama (opsional)</label>
                <textarea name="pesan_awal" id="pesan_awal" maxlength="2000" placeholder="Ceritakan keluhan..."></textarea>
            </div>
        </div>
        <div class="form-aksi"><button type="submit" name="submit_mulai" class="btn btn-primary">Mulai Konsultasi</button></div>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="card">
    <h2>Daftar Konsultasi</h2>
    <?= kotakCari(['action' => 'konsultasi'], $q, 'Cari pasien, dokter, topik, atau status...') ?>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr><th>No</th><th>Pasien</th><th>Dokter</th><th>Topik</th><th>Status</th><th>Dimulai</th><th>Pesan</th><th>Aksi</th></tr>
            <?php if (empty($daftarKonsultasi)): ?>
                <tr><td colspan="8" class="kosong"><?= $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada konsultasi' ?></td></tr>
            <?php else: foreach ($daftarKonsultasi as $i => $k): ?>
                <tr>
                    <td><?= $offset + $i + 1 ?></td>
                    <td><?= e($k['nama_pasien'] ?? '(data dihapus)') ?></td>
                    <td><?= e($k['nama_dokter'] ?? '(data dihapus)') ?></td>
                    <td><?= e($k['topik']) ?></td>
                    <td><?= badge($k['status']) ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($k['dibuat']))) ?></td>
                    <td><?= (int) $k['jumlah_pesan'] ?></td>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-sm btn-primary" href="index.php?action=konsultasi&mode=chat&id=<?= (int) $k['id_konsultasi'] ?>">
                                <?= $k['status'] === 'Selesai' ? 'Lihat' : 'Buka Chat' ?>
                            </a>
                            <?php if ($bisaHapus): ?>
                                <form method="POST" action="index.php?action=konsultasi" class="inline"
                                      onsubmit="return confirm('Hapus konsultasi ini beserta seluruh pesannya?');">
                                    <input type="hidden" name="id_konsultasi" value="<?= (int) $k['id_konsultasi'] ?>">
                                    <button type="submit" name="submit_hapus" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>
    <?= paginasi($total, $hal, ['action' => 'konsultasi', 'q' => $q]) ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>