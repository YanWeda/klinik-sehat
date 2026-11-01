<?php
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/AntrianModel.php';
require_once __DIR__ . '/../models/KonsultasiModel.php';

class DashboardController {
    private $dashboard;
    private $antrian;
    private $konsultasi;

    public function __construct($conn) {
        $this->dashboard  = new DashboardModel($conn);
        $this->antrian    = new AntrianModel($conn);
        $this->konsultasi = new KonsultasiModel($conn);
    }

    private function hitungStatus($daftar, $status) {
        return count(array_filter($daftar, function ($x) use ($status) { return $x['status'] === $status; }));
    }

    public function index() {
        $role    = roleSaatIni();
        $hariIni = date('Y-m-d');

        if ($role === 'admin') {
            $stat           = $this->dashboard->getStatistik($hariIni);
            $antrianHariIni = $this->antrian->getByTanggal($hariIni);
            $semuaKonsul    = $this->konsultasi->getAll();
            $kartu = [
                ['info',    $stat['antrian'],    'Antrian hari ini (' . $stat['menunggu'] . ' menunggu)'],
                ['warning', $stat['konsultasi'], 'Konsultasi online aktif'],
                ['',        $stat['pasien'],     'Total pasien'],
                ['success', $stat['dokter'],     'Dokter'],
            ];
            $intro = 'Ringkasan seluruh layanan';
            $judulAntrian = 'Antrian Aktif Hari Ini';
            $judulKonsul  = 'Konsultasi Online Terbaru';
            $tombol = ['+ Ambil Nomor Antrian', 'index.php?action=antrian'];

        } elseif ($role === 'dokter') {
            $idDokter       = idDokterSaatIni();
            $antrianHariIni = $this->antrian->getByTanggal($hariIni, $idDokter, null);
            $semuaKonsul    = $this->konsultasi->getAll($idDokter, null);
            $aktif = $this->hitungStatus($semuaKonsul, 'Menunggu') + $this->hitungStatus($semuaKonsul, 'Berlangsung');
            $kartu = [
                ['info',    count($antrianHariIni),                         'Antrian saya hari ini'],
                ['warning', $this->hitungStatus($antrianHariIni, 'Menunggu'), 'Pasien menunggu'],
                ['success', $this->hitungStatus($antrianHariIni, 'Selesai'),  'Selesai diperiksa hari ini'],
                ['info',    $aktif,                                          'Konsultasi online aktif'],
            ];
            $intro = 'Ringkasan praktik Anda hari ini';
            $judulAntrian = 'Pasien yang Perlu Dilayani';
            $judulKonsul  = 'Konsultasi Terbaru dengan Saya';
            $tombol = ['Kelola Antrian Saya', 'index.php?action=antrian'];

        } else {
            $idPasien       = idPasienSaatIni();
            $antrianHariIni = $this->antrian->getByTanggal($hariIni, null, $idPasien);
            $semuaKonsul    = $this->konsultasi->getAll(null, $idPasien);
            $aktif = $this->hitungStatus($semuaKonsul, 'Menunggu') + $this->hitungStatus($semuaKonsul, 'Berlangsung');
            $kartu = [
                ['info',    count($antrianHariIni),                          'Antrian saya hari ini'],
                ['warning', $aktif,                                          'Konsultasi aktif'],
                ['success', $this->hitungStatus($semuaKonsul, 'Selesai'),    'Konsultasi selesai'],
            ];
            $intro = 'Layanan klinik untuk Anda';
            $judulAntrian = 'Antrian Saya Hari Ini';
            $judulKonsul  = 'Konsultasi Saya';
            $tombol = ['+ Ambil Nomor Antrian', 'index.php?action=antrian'];
        }

        $antrianAktif = array_values(array_filter(
            $antrianHariIni,
            function ($a) { return in_array($a['status'], ['Menunggu', 'Dipanggil'], true); }
        ));
        $antrianAktif      = array_slice($antrianAktif, 0, 8);
        $konsultasiTerbaru = array_slice($semuaKonsul, 0, 5);

        require __DIR__ . '/../views/dashboard/index.php';
    }
}