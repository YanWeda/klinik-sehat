<?php
$judulHalaman = 'Data Obat';
require __DIR__ . '/../layout/header.php';

$pesan = [
    'tambah' => 'Data obat berhasil ditambahkan.',
    'update' => 'Data obat berhasil diperbarui.',
    'hapus'  => 'Data obat berhasil dihapus.',
];
$status = $_GET['status'] ?? '';

// Judul kolom: nama_obat -> Nama Obat
function judulKolom($nama) {
    return ucwords(str_replace('_', ' ', $nama));
}

// Tentukan jenis input dari tipe kolom database (dan nama kolomnya)
function jenisInput($tipe, $nama = '') {
    $tipe = strtolower($tipe);
    if (strpos($tipe, 'enum') === 0)                      return 'enum';
    if (strpos($tipe, 'datetime') === 0 || strpos($tipe, 'timestamp') === 0) return 'datetime';
    if (strpos($tipe, 'date') === 0)                      return 'date';
    // Kolom bernama tanggal/exp tapi bertipe teks -> tetap pakai kalender
    if (preg_match('/(tgl|tanggal|exp|kadaluarsa|kedaluwarsa)/i', $nama)
        && preg_match('/^(varchar|char|text)/', $tipe))   return 'date';
    if (preg_match('/^(tinyint|smallint|mediumint|int|bigint)/', $tipe)) return 'number';
    if (preg_match('/^(decimal|float|double)/', $tipe))   return 'decimal';
    if (preg_match('/text|blob/', $tipe))                 return 'textarea';
    return 'text';
}

// Ambil pilihan dari tipe enum('a','b')
function pilihanEnum($tipe) {
    preg_match_all("/'([^']*)'/", $tipe, $m);
    return $m[1];
}
?>
<div class="judul-halaman">
    <div><h1>Data Obat</h1><p class="sub"><?= $bisaUbah ? 'Kelola persediaan obat klinik.' : 'Daftar persediaan obat klinik (hanya lihat).' ?></p></div>
</div>

<?php if (isset($pesan[$status])): ?>
    <div class="alert alert-sukses"><?= $pesan[$status] ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($bisaUbah): ?>
<section class="card">
    <h2><?= $obatEdit ? 'Edit Obat' : 'Tambah Obat' ?></h2>
    <form method="POST" action="index.php?action=obat">
        <?php if ($obatEdit): ?>
            <input type="hidden" name="kunci" value="<?= e($obatEdit[$kunciObat]) ?>">
        <?php endif; ?>

        <div class="form-grid">
        <?php foreach ($kolomForm as $col):
            $nama  = $col['Field'];
            $jenis = jenisInput($col['Type'], $nama);
            $nilai = $obatEdit ? ($obatEdit[$nama] ?? '') : '';
            // Samakan format nilai dengan input kalender
            if ($nilai !== '' && $nilai !== null) {
                if ($jenis === 'date')     $nilai = substr($nilai, 0, 10);
                if ($jenis === 'datetime') $nilai = str_replace(' ', 'T', substr($nilai, 0, 16));
            }
            $wajib = ($col['Null'] === 'NO') ? 'required' : '';
        ?>
            <div class="field <?= $jenis === 'textarea' ? 'penuh' : '' ?>">
                <label for="f_<?= e($nama) ?>"><?= e(judulKolom($nama)) ?></label>

                <?php if ($jenis === 'enum'): ?>
                    <select name="<?= e($nama) ?>" id="f_<?= e($nama) ?>" <?= $wajib ?>>
                        <?php foreach (pilihanEnum($col['Type']) as $opsi): ?>
                            <option value="<?= e($opsi) ?>" <?= $nilai === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($jenis === 'textarea'): ?>
                    <textarea name="<?= e($nama) ?>" id="f_<?= e($nama) ?>" <?= $wajib ?>><?= e($nilai) ?></textarea>
                <?php else:
                    $typeAttr = ['number' => 'number', 'decimal' => 'number', 'date' => 'date', 'datetime' => 'datetime-local', 'text' => 'text'][$jenis];
                    $step     = $jenis === 'decimal' ? 'step="any"' : '';
                ?>
                    <input type="<?= $typeAttr ?>" <?= $step ?> name="<?= e($nama) ?>" id="f_<?= e($nama) ?>" <?= $wajib ?> value="<?= e($nilai) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="form-aksi">
            <?php if ($obatEdit): ?>
                <button type="submit" name="submit_update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="index.php?action=obat" class="btn">Batal</a>
            <?php else: ?>
                <button type="submit" name="submit_tambah" class="btn btn-primary">Tambah Obat</button>
            <?php endif; ?>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <h2>Daftar Obat</h2>
    <div class="tabel-wrap">
        <table class="tabel">
            <tr>
                <th>No</th>
                <?php foreach ($kolomObat as $col): ?><th><?= e(judulKolom($col['Field'])) ?></th><?php endforeach; ?>
                <?php if ($bisaUbah): ?><th>Aksi</th><?php endif; ?>
            </tr>
            <?php if (empty($daftarObat)): ?>
                <tr><td colspan="<?= count($kolomObat) + ($bisaUbah ? 2 : 1) ?>" class="kosong">Belum ada data obat</td></tr>
            <?php else: foreach ($daftarObat as $i => $obat): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <?php foreach ($kolomObat as $col): ?><td><?= e($obat[$col['Field']]) ?></td><?php endforeach; ?>
                    <?php if ($bisaUbah): ?>
                    <td>
                        <div class="aksi">
                            <a class="btn btn-sm" href="index.php?action=obat&mode=edit&id=<?= urlencode($obat[$kunciObat]) ?>">Edit</a>
                            <form method="POST" action="index.php?action=obat" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus obat ini?');">
                                <input type="hidden" name="kunci" value="<?= e($obat[$kunciObat]) ?>">
                                <button type="submit" name="submit_hapus" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>