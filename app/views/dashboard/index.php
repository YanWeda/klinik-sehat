<?php
$judulHalaman = 'Beranda';
require __DIR__ . '/../layout/header.php';
?>
<div class="judul-halaman">
    <div>
        <h1>Selamat datang, <?= e($_SESSION['user_nama'] ?? '') ?></h1>
        <p class="sub"><?= tanggalIndo(date('Y-m-d'), true) ?> &middot; <?= e($intro) ?> <?= e(NAMA_KLINIK) ?></p>
    </div>
    <a class="btn btn-primary" href="<?= e($tombol[1]) ?>"><?= e($tombol[0]) ?></a>
</div>

<section class="stats">
    <?php foreach ($kartu as $k): ?>
        <div class="stat <?= e($k[0]) ?>">
            <div class="nilai"><?= e($k[1]) ?></div>
            <div class="label"><?= e($k[2]) ?></div>
        </div>
    <?php endforeach; ?>
</section>

<div class="dua-kolom">
    <section class="card">
        <h2><?= e($judulAntrian) ?></h2>
        <div class="tabel-wrap">
            <table class="tabel">
                <tr><th>No</th><th>Pasien</th><th>Dokter</th><th>Status</th></tr>
                <?php if (empty($antrianAktif)): ?>
                    <tr><td colspan="4" class="kosong">Tidak ada antrian aktif saat ini</td></tr>
                <?php else: foreach ($antrianAktif as $a): ?>
                    <tr>
                        <td><span class="nomor"><?= (int) $a['no_antrian'] ?></span></td>
                        <td><?= e($a['nama_pasien'] ?? '(data dihapus)') ?></td>
                        <td><?= e($a['nama_dokter'] ?? '(data dihapus)') ?></td>
                        <td><?= badge($a['status']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </table>
        </div>
        <p style="margin:12px 0 0;"><a href="index.php?action=antrian">Buka halaman antrian &rarr;</a></p>
    </section>

    <section class="card">
        <h2><?= e($judulKonsul) ?></h2>
        <div class="tabel-wrap">
            <table class="tabel">
                <tr><th>Pasien</th><th>Dokter</th><th>Status</th><th></th></tr>
                <?php if (empty($konsultasiTerbaru)): ?>
                    <tr><td colspan="4" class="kosong">Belum ada konsultasi</td></tr>
                <?php else: foreach ($konsultasiTerbaru as $k): ?>
                    <tr>
                        <td><?= e($k['nama_pasien'] ?? '(data dihapus)') ?><br><span class="sub"><?= e($k['topik']) ?></span></td>
                        <td><?= e($k['nama_dokter'] ?? '(data dihapus)') ?></td>
                        <td><?= badge($k['status']) ?></td>
                        <td><a class="btn btn-sm" href="index.php?action=konsultasi&mode=chat&id=<?= (int) $k['id_konsultasi'] ?>">Buka</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </table>
        </div>
        <p style="margin:12px 0 0;"><a href="index.php?action=konsultasi">Buka halaman konsultasi &rarr;</a></p>
    </section>
</div>
<?php require __DIR__ . '/../layout/footer.php'; ?>