<?php
// Aman untuk dicetak ke HTML
function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 2026-10-13 -> "Selasa, 13 Oktober 2026"
function tanggalIndo($ymd, $denganHari = false) {
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $t = strtotime($ymd);
    if (!$t) return e($ymd);
    $teks = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    return $denganHari ? $hari[(int) date('w', $t)] . ', ' . $teks : $teks;
}

// Label status berwarna
function badge($status) {
    $kelas = [
        'Menunggu'    => 'warning',
        'Dipanggil'   => 'info',
        'Berlangsung' => 'info',
        'Selesai'     => 'success',
        'Batal'       => 'danger',
    ][$status] ?? 'muted';
    return '<span class="badge badge-' . $kelas . '">' . e($status) . '</span>';
}

// Label peran akun
function badgeRole($role) {
    $kelas = ['admin' => 'info', 'dokter' => 'success', 'user' => 'muted'][$role] ?? 'muted';
    return '<span class="badge badge-' . $kelas . '">' . e(labelRole($role)) . '</span>';
}


// ================= Pencarian & halaman =================
if (!defined('PER_HALAMAN')) define('PER_HALAMAN', 10);

// Kata kunci pencarian dari ?q= (maksimal 100 karakter)
function kataKunciCari() {
    return substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
}

// Nomor halaman dari ?hal= (minimal 1)
function halamanCari() {
    return max(1, (int) ($_GET['hal'] ?? 1));
}

// Pola LIKE yang aman: karakter % dan _ yang diketik pengguna dianggap huruf biasa
function likeCari($q) {
    return '%' . addcslashes($q, '%_\\') . '%';
}

// Mengembalikan [halamanAktif, offset, totalHalaman]; halaman di luar jangkauan dirapikan
function hitungHalaman($total, $perHalaman = PER_HALAMAN) {
    $totalHal = max(1, (int) ceil($total / $perHalaman));
    $hal      = min(halamanCari(), $totalHal);
    return [$hal, ($hal - 1) * $perHalaman, $totalHal];
}

// Membuat alamat index.php?... (parameter kosong dibuang, hal=1 dibuang)
function bangunUrl(array $params) {
    if (isset($params['hal']) && (int) $params['hal'] <= 1) unset($params['hal']);
    $params = array_filter($params, function ($v) { return $v !== '' && $v !== null; });
    return 'index.php?' . http_build_query($params);
}

// Kotak pencarian. $params = parameter tetap (mis. ['action' => 'pasien'])
function kotakCari(array $params, $q, $placeholder = 'Cari data...') {
    unset($params['q'], $params['hal']);

    $html  = '<div class="cari-wrap">';
    $html .= '<form method="GET" action="index.php" class="cari-bar" role="search">';
    foreach ($params as $k => $v) {
        $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    $html .= '<div class="cari-kotak' . ($q !== '' ? ' ada-isi' : '') . '">';
    $html .= '<input type="search" name="q" value="' . e($q) . '" placeholder="' . e($placeholder) . '" maxlength="100" aria-label="Cari data">';
    if ($q !== '') {
        $html .= '<a class="cari-hapus" href="' . e(bangunUrl($params)) . '" title="Hapus pencarian" aria-label="Hapus pencarian">&times;</a>';
    }
    $html .= '<button type="submit" class="cari-tombol" title="Cari" aria-label="Cari">'
           . '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" '
           . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
           . '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg></button>';
    $html .= '</div>';
    $html .= '</form>';
    if ($q !== '') {
        $html .= '<p class="cari-info">Hasil pencarian untuk <strong>&ldquo;' . e($q) . '&rdquo;</strong></p>';
    }
    return $html . '</div>';
}

// Penomoran halaman. $params = parameter tetap + q (tanpa hal)
function paginasi($total, $hal, array $params, $perHalaman = PER_HALAMAN) {
    $totalHal = max(1, (int) ceil($total / $perHalaman));
    $dari     = $total === 0 ? 0 : ($hal - 1) * $perHalaman + 1;
    $sampai   = min($total, $hal * $perHalaman);

    $html  = '<div class="paginasi">';
    $html .= $total === 0
        ? '<div class="info-data">Menampilkan 0 data</div>'
        : '<div class="info-data">Menampilkan ' . $dari . '&ndash;' . $sampai . ' dari ' . $total . ' data &middot; terbaru di atas</div>';

    if ($totalHal > 1) {
        $link = function ($n) use ($params) { return e(bangunUrl(array_merge($params, ['hal' => $n]))); };
        $html .= '<div class="halaman">';
        $html .= $hal > 1
            ? '<a href="' . $link($hal - 1) . '">&laquo; Sebelumnya</a>'
            : '<span class="nonaktif">&laquo; Sebelumnya</span>';

        $sebelumnya = 0;
        for ($n = 1; $n <= $totalHal; $n++) {
            if ($n !== 1 && $n !== $totalHal && abs($n - $hal) > 2) continue;
            if ($sebelumnya && $n - $sebelumnya > 1) $html .= '<span class="nonaktif">&hellip;</span>';
            $html .= $n === $hal
                ? '<span class="aktif">' . $n . '</span>'
                : '<a href="' . $link($n) . '">' . $n . '</a>';
            $sebelumnya = $n;
        }

        $html .= $hal < $totalHal
            ? '<a href="' . $link($hal + 1) . '">Berikutnya &raquo;</a>'
            : '<span class="nonaktif">Berikutnya &raquo;</span>';
        $html .= '</div>';
    }
    return $html . '</div>';
}