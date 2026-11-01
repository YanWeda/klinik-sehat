<?php
$judulHalaman = $judulAntrian;
require __DIR__ . '/../layout/header.php';

$pesan = $_GET['pesan'] ?? '';

// Tombol kecil untuk mengubah status satu baris antrian
function tombolStatus($baris, $tanggal, $status, $label, $kelas) {
    return '<form method="POST" action="index.php?action=antrian&tanggal=' . e($tanggal) . '" class="inline">'
         . '<input type="hidden" name="id_antrian" value="' . (int) $baris['id_antrian'] . '">'
         . '<input type="hidden" name="tanggal" value="' . e($tanggal) . '">'
         . '<input type="hidden" name="status" value="' . e($status) . '">'
         . '<button type="submit" name="submit_status" class="btn btn-sm ' . $kelas . '">' . e($label) . '</button>'
         . '</form>';
}
?>
<div class="judul-halaman">
    <div>
        <h1><?= e($judulAntrian) ?></h1>
        <p class="sub"><?= tanggalIndo($tanggal, true) ?><?= $tanggal === $hariIni ? ' (hari ini)' : '' ?></p>
    </div>
    <form method="GET" action="index.php" class="aksi">
        <input type="hidden" name="action" value="antrian">
        <input type="date" name="tanggal" value="<?= e($tanggal) ?>" class="btn" style="padding:7px 10px;">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <?php if ($tanggal !== $hariIni): ?><a href="index.php?action=antrian" class="btn">Hari Ini</a><?php endif; ?>
    </form>
</div>

<?php if ($pesan === 'tambah'): ?>
    <div class="alert alert-sukses">Nomor antrian <strong><?= (int) ($_GET['no'] ?? 0) ?></strong> berhasil dibuat.</div>
<?php elseif ($pesan === 'status'): ?>
    <div class="alert alert-sukses">Status antrian berhasil diperbarui.</div>
<?php elseif ($pesan === 'hapus'): ?>
    <div class="alert alert-sukses">Data antrian berhasil dihapus.</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- Ringkasan -->
<section class="stats">
    <div class="stat"><div class="nilai"><?= count($daftarAntrian) ?></div><div class="label">Total antrian</div></div>
    <div class="stat warning"><div class="nilai"><?= $ringkasan['Menunggu'] ?></div><div class="label">Menunggu</div></div>
    <div class="stat info"><div class="nilai"><?= $ringkasan['Dipanggil'] ?></div><div class="label">Sedang dipanggil</div></div>
    <div class="stat success"><div class="nilai"><?= $ringkasan['Selesai'] ?></div><div class="label">Selesai</div></div>
    <div class="stat danger"><div class="nilai"><?= $ringkasan['Batal'] ?></div><div class="label">Batal</div></div>
</section>

<!-- Papan antrian per dokter -->
<?php if (!empty($papan)): ?>
<section class="papan">
    <?php foreach ($papan as $p): ?>
        <div class="papan-item">
            <div class="dokter"><?= e($p['nama']) ?></div>
            <div class="spesialis"><?= e($p['spesialis']) ?></div>
            <div class="angka"><?= $p['dipanggil'] ? (int) $p['dipanggil']['no_antrian'] : '-' ?></div>
            <div class="info">
                <?php if ($p['dipanggil']): ?>
                    Sedang dipanggil<?= $p['dipanggil']['nama_pasien'] !== null ? ': ' . e($p['dipanggil']['nama_pasien']) : '' ?>
                <?php else: ?>
                    Belum ada yang dipanggil
                <?php endif; ?>
                <br><?= $p['menunggu'] ?> pasien menunggu
            </div>
        </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Form ambil nomor (admin & user) -->
<?php if ($bisaTambah): ?>
<section class="card">
    <h2>Ambil Nomor Antrian</h2>
    <?php if (roleSaatIni() === 'admin' && (empty($daftarPasien) || empty($daftarDokter))): ?>
        <div class="alert alert-info">
            Data <?= empty($daftarPasien) ? '<a href="index.php?action=pasien">pasien</a>' : '' ?>
            <?= (empty($daftarPasien) && empty($daftarDokter)) ? 'dan' : '' ?>
            <?= empty($daftarDokter) ? '<a href="index.php?action=dokter">dokter</a>' : '' ?>
            masih kosong. Tambahkan dulu sebelum membuat antrian.
        </div>
    <?php elseif (empty($daftarDokter)): ?>
        <div class="alert alert-info">Belum ada dokter yang terdaftar. Silakan coba lagi nanti.</div>
    <?php else: ?>
    <form method="POST" action="index.php?action=antrian&tanggal=<?= e($tanggal) ?>">
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
            <div class="field">
                <label for="tanggal_antri">Tanggal Kunjungan</label>
                <input type="date" name="tanggal" id="tanggal_antri" value="<?= e(max($tanggal, $hariIni)) ?>" min="<?= e($hariIni) ?>" required>
            </div>
            <div class="field">
                <button type="submit" name="submit_tambah" class="btn btn-primary" style="width:100%;">Ambil Nomor</button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- Daftar antrian -->
<section class="card">
    <h2>Daftar Antrian</h2>
    <?= kotakCari(['action' => 'antrian', 'tanggal' => $tanggal], $q, 'Cari nomor, pasien, dokter, atau status...') ?>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr><th>No</th><th>Pasien</th><th>Dokter</th><th>Status</th><th>Aksi</th></tr>
            <?php if (empty($tampilAntrian)): ?>
                <tr><td colspan="5" class="kosong"><?= $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada antrian pada tanggal ini' ?></td></tr>
            <?php else: foreach ($tampilAntrian as $a): ?>
                <tr>
                    <td><span class="nomor"><?= (int) $a['no_antrian'] ?></span></td>
                    <td><?= e($a['nama_pasien'] ?? '(data dihapus)') ?></td>
                    <td><?= e($a['nama_dokter'] ?? '(data dihapus)') ?><br><span class="sub"><?= e($a['spesialis'] ?? '') ?></span></td>
                    <td><?= badge($a['status']) ?></td>
                    <td>
                        <div class="aksi">
                            <?php if (empty($a['aksi'])): ?>
                                <span class="sub">-</span>
                            <?php endif; ?>
                            <?php foreach ($a['aksi'] as $ak): ?>
                                <?php if ($ak === 'hapus'): ?>
                                    <form method="POST" action="index.php?action=antrian&tanggal=<?= e($tanggal) ?>" class="inline"
                                          onsubmit="return confirm('Hapus data antrian ini?');">
                                        <input type="hidden" name="id_antrian" value="<?= (int) $a['id_antrian'] ?>">
                                        <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
                                        <button type="submit" name="submit_hapus" class="btn btn-sm">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <?= tombolStatus($a, $tanggal, $ak, $ak === 'Dipanggil' ? 'Panggil' : $ak, $ak === 'Batal' ? 'btn-danger' : 'btn-primary') ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>
    <?= paginasi($total, $hal, ['action' => 'antrian', 'tanggal' => $tanggal, 'q' => $q]) ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>