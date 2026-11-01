<?php
require_once __DIR__ . '/../models/KonsultasiModel.php';
require_once __DIR__ . '/../models/PasienModel.php';
require_once __DIR__ . '/../models/DokterModel.php';

class KonsultasiController {
    private $model;
    private $pasienModel;
    private $dokterModel;

    public function __construct($conn) {
        $this->model       = new KonsultasiModel($conn);
        $this->pasienModel = new PasienModel($conn);
        $this->dokterModel = new DokterModel($conn);
    }

    // Admin: semua konsultasi. Dokter / User: hanya konsultasi miliknya.
    private function bolehLihat($k) {
        $role = roleSaatIni();
        if ($role === 'admin') return true;
        if ($role === 'dokter') return idDokterSaatIni() > 0 && (int) $k['id_dokter'] === idDokterSaatIni();
        if ($role === 'user')   return idPasienSaatIni() > 0 && (int) $k['id_pasien'] === idPasienSaatIni();
        return false;
    }

    public function index() {
        if (($_GET['mode'] ?? '') === 'chat' && isset($_GET['id'])) {
            $this->chat((int) $_GET['id']);
            return;
        }

        $role  = roleSaatIni();
        $error = null;
        $bisaMulai = in_array($role, ['admin', 'user'], true);
        $bisaHapus = $role === 'admin';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Mulai konsultasi baru
                if (isset($_POST['submit_mulai'])) {
                    $idPasien  = $role === 'user' ? idPasienSaatIni() : (int) ($_POST['id_pasien'] ?? 0);
                    $idDokter  = (int) ($_POST['id_dokter'] ?? 0);
                    $topik     = trim($_POST['topik'] ?? '');
                    $pesanAwal = trim($_POST['pesan_awal'] ?? '');

                    if (!$bisaMulai) {
                        $error = "Akun dokter tidak dapat memulai konsultasi baru.";
                    } elseif ($idPasien <= 0 || $idDokter <= 0) {
                        $error = "Pilih pasien dan dokter terlebih dahulu.";
                    } elseif ($topik === '' || strlen($topik) > 150) {
                        $error = "Topik wajib diisi dan maksimal 150 karakter.";
                    } elseif (strlen($pesanAwal) > 2000) {
                        $error = "Pesan awal terlalu panjang (maksimal 2000 karakter).";
                    } else {
                        $id = $this->model->mulai($idPasien, $idDokter, $topik);
                        if ($pesanAwal !== '') {
                            $this->model->kirimPesan($id, 'Pasien', $pesanAwal);
                        }
                        header("Location: index.php?action=konsultasi&mode=chat&id=$id&sbg=Pasien");
                        exit;
                    }
                }

                // Hapus (admin)
                if (isset($_POST['submit_hapus'])) {
                    if (!$bisaHapus) {
                        $error = "Hanya admin yang dapat menghapus konsultasi.";
                    } else {
                        $this->model->hapus((int) ($_POST['id_konsultasi'] ?? 0));
                        header("Location: index.php?action=konsultasi&pesan=hapus");
                        exit;
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }

        $filterDokter = $role === 'dokter' ? idDokterSaatIni() : null;
        $filterPasien = $role === 'user'   ? idPasienSaatIni() : null;

        // Pencarian & halaman
        $q     = kataKunciCari();
        $total = $this->model->hitung($q, $filterDokter, $filterPasien);
        list($hal, $offset, $totalHal) = hitungHalaman($total);
        $daftarKonsultasi = $this->model->cari($q, PER_HALAMAN, $offset, $filterDokter, $filterPasien);
        $daftarPasien     = $role === 'admin' ? $this->pasienModel->getAll() : [];
        $daftarDokter     = $this->dokterModel->getAll();
        require __DIR__ . '/../views/konsultasi/index.php';
    }

    private function chat($id) {
        $role       = roleSaatIni();
        $konsultasi = $this->model->getById($id);
        if (!$konsultasi || !$this->bolehLihat($konsultasi)) {
            header("Location: index.php?action=konsultasi");
            exit;
        }

        // Dokter selalu mengirim sebagai Dokter, User sebagai Pasien. Admin boleh memilih.
        $pengirimTetap = ['dokter' => 'Dokter', 'user' => 'Pasien'][$role] ?? null;
        $bisaAkhiri    = in_array($role, ['admin', 'dokter'], true);

        $error = null;
        $sbg   = $pengirimTetap ?? (in_array($_GET['sbg'] ?? '', KonsultasiModel::PENGIRIM, true) ? $_GET['sbg'] : 'Dokter');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Kirim pesan
                if (isset($_POST['submit_kirim'])) {
                    $pengirim = $pengirimTetap ?? ($_POST['pengirim'] ?? '');
                    $isi      = trim($_POST['isi'] ?? '');
                    $sbg      = in_array($pengirim, KonsultasiModel::PENGIRIM, true) ? $pengirim : $sbg;

                    if ($isi === '') {
                        $error = "Pesan tidak boleh kosong.";
                    } elseif (strlen($isi) > 2000) {
                        $error = "Pesan terlalu panjang (maksimal 2000 karakter).";
                    } elseif (!$this->model->kirimPesan($id, $pengirim, $isi)) {
                        $error = "Pesan tidak terkirim. Konsultasi ini sudah selesai.";
                    } else {
                        header("Location: index.php?action=konsultasi&mode=chat&id=$id&sbg=$sbg");
                        exit;
                    }
                }

                // Akhiri konsultasi (admin / dokter)
                if (isset($_POST['submit_selesai'])) {
                    if (!$bisaAkhiri) {
                        $error = "Hanya dokter atau admin yang dapat mengakhiri konsultasi.";
                    } else {
                        $this->model->selesai($id);
                        header("Location: index.php?action=konsultasi&mode=chat&id=$id&sbg=$sbg");
                        exit;
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
            $konsultasi = $this->model->getById($id);
        }

        $daftarPesan = $this->model->getPesan($id);
        require __DIR__ . '/../views/konsultasi/chat.php';
    }
}