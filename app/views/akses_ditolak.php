<?php
$judulHalaman = 'Akses Ditolak';
require __DIR__ . '/layout/header.php';
?>
<div class="card" style="max-width:520px;margin:40px auto;text-align:center;">
    <div style="font-size:46px;line-height:1;">&#128274;</div>
    <h2 style="margin-top:12px;">Akses ditolak</h2>
    <p class="sub">Akun Anda (<?= e(labelRole(roleSaatIni())) ?>) tidak memiliki izin untuk membuka halaman ini.</p>
    <p style="margin-top:18px;"><a class="btn btn-primary" href="index.php">Kembali ke Beranda</a></p>
</div>
<?php require __DIR__ . '/layout/footer.php'; ?>