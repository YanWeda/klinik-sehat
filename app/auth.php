<?php
// Peta hak akses: halaman (action) => peran yang boleh membuka.
// ADMIN selalu boleh membuka semua halaman.
function aksesHalaman() {
    return [
        'index'      => ['admin', 'dokter', 'user'],
        'antrian'    => ['admin', 'dokter', 'user'],
        'konsultasi' => ['admin', 'dokter', 'user'],
        'pasien'     => ['admin', 'dokter'],
        'dokter'     => ['admin'],
        'user'       => ['admin'],
        'profil'     => ['user'],
        'about'      => ['admin', 'dokter', 'user'],
    ];
}

function roleSaatIni()     { return $_SESSION['user_role'] ?? ''; }
function adalahAdmin()     { return roleSaatIni() === 'admin'; }
function idDokterSaatIni() { return (int) ($_SESSION['user_id_dokter'] ?? 0); }
function idPasienSaatIni() { return (int) ($_SESSION['user_id_pasien'] ?? 0); }

function bolehAkses($halaman) {
    $role = roleSaatIni();
    if ($role === 'admin') return true;
    $peta = aksesHalaman();
    return isset($peta[$halaman]) && in_array($role, $peta[$halaman], true);
}

function labelRole($role) {
    return ['admin' => 'Admin', 'dokter' => 'Dokter', 'user' => 'User'][$role] ?? ucfirst((string) $role);
}