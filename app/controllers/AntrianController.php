<?php
require_once __DIR__ . '/../models/AntrianModel.php';
require_once __DIR__ . '/../models/PasienModel.php';
require_once __DIR__ . '/../models/DokterModel.php';

class AntrianController {
    private $model;
    private $pasienModel;
    private $dokterModel;

    public function __construct($conn) {
        $this->model       = new AntrianModel($conn);
        $this->pasienModel = new PasienModel($conn);
        $this->dokterModel = new DokterModel($conn);
    }

    private function validasiTanggal($t) {
        $d = DateTime::createFromFormat('Y-m-d', (string) $t);
        return ($d && $d->format('Y-m-d') === $t) ? $t : date('Y-m-d');
    }

    // Satu-satunya tempat aturan hak akses antrian:
    //  - admin  : semua aksi
    //  - dokter : Panggil / Selesai / Batal untuk antrian dokter itu sendiri
    //  - user   : hanya membatalkan antrian miliknya yang masih Menunggu
    private function aksiDiizinkan($a) {
        $role = roleSaatIni();
        $aksi = [];

        $milikDokter = $role === 'dokter' && idDokterSaatIni() > 0 && (int) $a['id_dokter'] === idDokterSaatIni();
        $milikPasien = $role === 'user'   && idPasienSaatIni() > 0 && (int) $a['id_pasien'] === idPasienSaatIni();

        if ($role === 'admin' || $milikDokter) {
            if ($a['status'] === 'Menunggu')       $aksi = ['Dipanggil', 'Batal'];
            elseif ($a['status'] === 'Dipanggil')  $aksi = ['Selesai', 'Batal'];
        } elseif ($milikPasien && $a['status'] === 'Menunggu') {
            $aksi = ['Batal'];
        }

        if ($role === 'admin') $aksi[] = 'hapus';
        return $aksi;
    }

    public function index() {
        $role    = roleSaatIni();
        $hariIni = date('Y-m-d');
        $tanggal = $this->validasiTanggal($_GET['tanggal'] ?? $hariIni);
        $error   = null;

        $bisaTambah = in_array($role, ['admin', 'user'], true);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tglPost = $this->validasiTanggal($_POST['tanggal'] ?? $tanggal);

            try {
                // Ambil nomor antrian
                if (isset($_POST['submit_tambah'])) {
                    $tanggal  = $tglPost;
                    // User hanya boleh mendaftarkan dirinya sendiri
                    $idPasien = $role === 'user' ? idPasienSaatIni() : (int) ($_POST['id_pasien'] ?? 0);
                    $idDokter = (int) ($_POST['id_dokter'] ?? 0);

                    if (!$bisaTambah) {
                        $error = "Akun dokter tidak dapat membuat antrian.";
                    } elseif ($idPasien <= 0 || $idDokter <= 0) {
                        $error = "Pilih pasien dan dokter terlebih dahulu.";
                    } elseif ($tglPost < $hariIni) {
                        $error = "Tanggal kunjungan tidak boleh sebelum hari ini.";
                    } elseif ($this->model->sudahAntri($idPasien, $idDokter, $tglPost)) {
                        $error = "Pasien tersebut masih memiliki antrian aktif ke dokter yang sama pada tanggal ini.";
                    } else {
                        $no = $this->model->tambah($idPasien, $idDokter, $tglPost);
                        header("Location: index.php?action=antrian&tanggal=$tglPost&pesan=tambah&no=$no");
                        exit;
                    }
                }

                // Ubah status
                if (isset($_POST['submit_status'])) {
                    $a      = $this->model->getById((int) ($_POST['id_antrian'] ?? 0));
                    $status = $_POST['status'] ?? '';
                    if (!$a || !in_array($status, $this->aksiDiizinkan($a), true)) {
                        $error = "Anda tidak berhak melakukan aksi ini pada antrian tersebut.";
                    } else {
                        $this->model->ubahStatus((int) $a['id_antrian'], $status);
                        header("Location: index.php?action=antrian&tanggal=$tglPost&pesan=status");
                        exit;
                    }
                }

                // Hapus (admin)
                if (isset($_POST['submit_hapus'])) {
                    $a = $this->model->getById((int) ($_POST['id_antrian'] ?? 0));
                    if (!$a || !in_array('hapus', $this->aksiDiizinkan($a), true)) {
                        $error = "Anda tidak berhak menghapus antrian ini.";
                    } else {
                        $this->model->hapus((int) $a['id_antrian']);
                        header("Location: index.php?action=antrian&tanggal=$tglPost&pesan=hapus");
                        exit;
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }

        // Data sesuai peran
        $filterDokter = $role === 'dokter' ? idDokterSaatIni() : null;
        $filterPasien = $role === 'user'   ? idPasienSaatIni() : null;

        $daftarAntrian = $this->model->getByTanggal($tanggal, $filterDokter, $filterPasien);
        foreach ($daftarAntrian as &$a) {
            $a['aksi'] = $this->aksiDiizinkan($a);
        }
        unset($a);

        $daftarDokter = $this->dokterModel->getAll();
        $daftarPasien = $role === 'admin' ? $this->pasienModel->getAll() : [];

        // Ringkasan jumlah per status
        $ringkasan = ['Menunggu' => 0, 'Dipanggil' => 0, 'Selesai' => 0, 'Batal' => 0];
        foreach ($daftarAntrian as $a) {
            if (isset($ringkasan[$a['status']])) $ringkasan[$a['status']]++;
        }

        // Papan nomor per dokter. User melihat papan semua dokter (tanpa nama pasien lain).
        $untukPapan = $role === 'user' ? $this->model->getByTanggal($tanggal) : $daftarAntrian;
        $papan = [];
        foreach ($daftarDokter as $d) {
            if ($role === 'dokter' && (int) $d['id_dokter'] !== idDokterSaatIni()) continue;
            $papan[$d['id_dokter']] = [
                'nama' => $d['nama_dokter'], 'spesialis' => $d['spesialis'],
                'dipanggil' => null, 'menunggu' => 0,
            ];
        }
        foreach ($untukPapan as $a) {
            if (!isset($papan[$a['id_dokter']])) continue;
            if ($a['status'] === 'Dipanggil') {
                $papan[$a['id_dokter']]['dipanggil'] = $a;
                if ($role === 'user' && (int) $a['id_pasien'] !== idPasienSaatIni()) {
                    $papan[$a['id_dokter']]['dipanggil']['nama_pasien'] = null;   // sembunyikan nama pasien lain
                }
            }
            if ($a['status'] === 'Menunggu') $papan[$a['id_dokter']]['menunggu']++;
        }

        // Pencarian & halaman khusus tabel. Ringkasan dan papan di atas tetap menghitung
        // seluruh antrian pada tanggal itu. Urutan tabel: terbaru di atas.
        $q     = kataKunciCari();
        $cocok = $daftarAntrian;
        if ($q !== '') {
            $cocok = array_values(array_filter($daftarAntrian, function ($a) use ($q) {
                $teks = implode(' ', [$a['no_antrian'], $a['nama_pasien'] ?? '', $a['nama_dokter'] ?? '', $a['spesialis'] ?? '', $a['status']]);
                return stripos($teks, $q) !== false;
            }));
        }
        usort($cocok, function ($x, $y) { return $y['id_antrian'] <=> $x['id_antrian']; });
        $total = count($cocok);
        list($hal, $offset, $totalHal) = hitungHalaman($total);
        $tampilAntrian = array_slice($cocok, $offset, PER_HALAMAN);

        $judulAntrian = ['admin' => 'Antrian Pasien', 'dokter' => 'Antrian Pasien Saya', 'user' => 'Antrian Saya'][$role] ?? 'Antrian';
        require __DIR__ . '/../views/antrian/index.php';
    }
}