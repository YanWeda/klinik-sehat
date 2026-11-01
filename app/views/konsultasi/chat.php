<?php
$judulHalaman = 'Ruang Konsultasi';
require __DIR__ . '/../layout/header.php';
$selesai  = ($konsultasi['status'] === 'Selesai');
$urlChat  = 'index.php?action=konsultasi&mode=chat&id=' . (int) $konsultasi['id_konsultasi'];
?>
<div class="judul-halaman">
    <div>
        <h1><?= e($konsultasi['topik']) ?></h1>
        <div class="chat-info">
            <div><span>Pasien:</span> <strong><?= e($konsultasi['nama_pasien'] ?? '(data dihapus)') ?></strong></div>
            <div><span>Dokter:</span> <strong><?= e($konsultasi['nama_dokter'] ?? '(data dihapus)') ?></strong></div>
            <div><span>Status:</span> <?= badge($konsultasi['status']) ?></div>
        </div>
    </div>
    <a class="btn" href="index.php?action=konsultasi">&larr; Kembali</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<section class="card">
    <div class="chat" id="kotak-chat">
        <?php if (empty($daftarPesan)): ?>
            <p class="sub" style="text-align:center;margin:20px 0;">Belum ada pesan. Mulai percakapan di bawah ini.</p>
        <?php else: foreach ($daftarPesan as $m): ?>
            <div class="bubble <?= $m['pengirim'] === 'Dokter' ? 'dokter' : 'pasien' ?>">
                <div class="nama"><?= $m['pengirim'] === 'Dokter' ? e($konsultasi['nama_dokter'] ?? 'Dokter') : e($konsultasi['nama_pasien'] ?? 'Pasien') ?></div>
                <?= nl2br(e($m['isi'])) ?>
                <small><?= e(date('d/m/Y H:i', strtotime($m['dibuat']))) ?></small>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <?php if ($selesai): ?>
        <div class="alert alert-info" style="margin-bottom:0;">
            Konsultasi ini telah selesai<?= $konsultasi['selesai_pada'] ? ' pada ' . e(date('d/m/Y H:i', strtotime($konsultasi['selesai_pada']))) : '' ?>.
            Pesan baru tidak dapat dikirim.
        </div>
    <?php else: ?>
        <form method="POST" action="<?= e($urlChat) ?>">
            <div class="form-grid">
                <?php if ($pengirimTetap === null): ?>
                    <div class="field">
                        <label for="pengirim">Kirim sebagai</label>
                        <select name="pengirim" id="pengirim">
                            <option value="Pasien" <?= $sbg === 'Pasien' ? 'selected' : '' ?>>Pasien</option>
                            <option value="Dokter" <?= $sbg === 'Dokter' ? 'selected' : '' ?>>Dokter</option>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="field">
                        <label>Anda mengirim sebagai</label>
                        <input type="text" value="<?= e($pengirimTetap) ?>" disabled>
                    </div>
                <?php endif; ?>
                <div class="field penuh">
                    <label for="isi">Pesan</label>
                    <textarea name="isi" id="isi" maxlength="2000" placeholder="Tulis pesan..." required></textarea>
                </div>
            </div>
            <div class="form-aksi">
                <button type="submit" name="submit_kirim" class="btn btn-primary">Kirim Pesan</button>
                <?php if ($bisaAkhiri): ?>
                    <button type="submit" name="submit_selesai" class="btn btn-danger" formnovalidate
                            onclick="return confirm('Akhiri konsultasi ini? Setelah selesai pesan baru tidak dapat dikirim.');">
                        Akhiri Konsultasi
                    </button>
                <?php endif; ?>
            </div>
        </form>
        <script>
            // Gulir ke pesan terbaru & segarkan otomatis tiap 10 detik selama kotak pesan kosong
            var kotak = document.getElementById('kotak-chat');
            kotak.scrollTop = kotak.scrollHeight;
            setInterval(function () {
                if (document.getElementById('isi').value === '') { location.reload(); }
            }, 10000);
        </script>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>