<?php
// Muat CSS bila halaman ini dipanggil tanpa layout (mis. halaman About)
if (!defined('CSS_LOADED')) {
    define('CSS_LOADED', true);
    echo '<link rel="stylesheet" href="assets/css/style.css">';
}

$halamanAktif = $_GET['action'] ?? 'index';
$namaKlinik   = defined('NAMA_KLINIK') ? NAMA_KLINIK : 'Klinik';
$roleNav      = function_exists('roleSaatIni') ? roleSaatIni() : '';

$semuaMenu = [
    'index'      => ['Beranda',      'index.php'],
    'antrian'    => ['Antrian',      'index.php?action=antrian'],
    'konsultasi' => ['Konsultasi',   'index.php?action=konsultasi'],
    'pasien'     => ['Pasien',       'index.php?action=pasien'],
    'dokter'     => ['Dokter',       'index.php?action=dokter'],
    'user'       => ['Kelola User',  'index.php?action=user'],
    'profil'     => ['Profil Saya',  'index.php?action=profil'],
    'about'      => ['About',        'index.php?action=about'],
];

// Nama menu menyesuaikan peran
$namaPerRole = [
    'dokter' => ['antrian' => 'Antrian Pasien', 'konsultasi' => 'Konsultasi Pasien', 'pasien' => 'Pasien'],
    'user'   => ['antrian' => 'Antrian Saya', 'konsultasi' => 'Konsultasi Saya'],
];

$menu = [];
foreach ($semuaMenu as $key => [$label, $url]) {
    if (function_exists('bolehAkses') && !bolehAkses($key)) continue;
    if ($key === 'profil' && $roleNav !== 'user') continue;   // hanya untuk akun User
    $menu[$key] = [$namaPerRole[$roleNav][$key] ?? $label, $url];
}
?>
<header class="navbar role-<?= htmlspecialchars($roleNav) ?>">
    <div class="navbar-inner">
        <a class="brand" href="index.php"><span class="logo">+</span><?= htmlspecialchars($namaKlinik) ?></a>

        <nav class="menu">
            <?php foreach ($menu as $key => [$label, $url]): ?>
                <a href="<?= $url ?>" class="<?= $halamanAktif === $key ? 'aktif' : '' ?>"><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (isset($_SESSION['user_nama'])): ?>
            <div class="user">
                <span>Halo, <strong><?= htmlspecialchars($_SESSION['user_nama']) ?></strong></span>
                <?php if ($roleNav !== ''): ?>
                    <span class="role-badge"><?= htmlspecialchars(function_exists('labelRole') ? labelRole($roleNav) : $roleNav) ?></span>
                <?php endif; ?>
                <form method="POST" action="index.php?action=logout">
                    <button type="submit" class="btn btn-sm">Logout</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</header>