<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PasienModel.php';
require_once __DIR__ . '/../models/DokterModel.php';

class UserController {
    private $model;
    private $pasienModel;
    private $dokterModel;

    public function __construct($conn) {
        $this->model       = new UserModel($conn);
        $this->pasienModel = new PasienModel($conn);
        $this->dokterModel = new DokterModel($conn);
    }

    // Cek tautan sesuai peran. Mengembalikan [pesanError, idDokter, idPasien]
    private function validasiRelasi($role, $idDokter, $idPasien) {
        if ($role === 'dokter') {
            if ($idDokter <= 0 || !$this->dokterModel->getById($idDokter)) {
                return ["Akun dokter harus dihubungkan ke data dokter.", null, null];
            }
            return [null, $idDokter, null];
        }
        if ($role === 'user') {
            if ($idPasien > 0 && !$this->pasienModel->getById($idPasien)) {
                return ["Data pasien yang dipilih tidak ditemukan.", null, null];
            }
            return [null, null, $idPasien > 0 ? $idPasien : null];   // kosong = dibuat otomatis
        }
        return [null, null, null];                                     // admin
    }

    public function index() {
        if (!adalahAdmin()) {
            header("Location: index.php");
            exit;
        }

        $error = null;
        $diriId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Tambah akun
                if (isset($_POST['submit_tambah'])) {
                    $nama     = trim($_POST['nama'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role     = $_POST['role'] ?? '';
                    list($err, $idD, $idP) = $this->validasiRelasi($role, (int) ($_POST['id_dokter'] ?? 0), (int) ($_POST['id_pasien'] ?? 0));

                    if ($nama === '' || strlen($nama) > 50) {
                        $error = "Nama wajib diisi dan maksimal 50 karakter.";
                    } elseif (strlen($password) < 6) {
                        $error = "Password minimal 6 karakter.";
                    } elseif (!in_array($role, UserModel::ROLE, true)) {
                        $error = "Peran tidak valid.";
                    } elseif ($this->model->findByNama($nama)) {
                        $error = "Nama tersebut sudah terdaftar.";
                    } elseif ($err) {
                        $error = $err;
                    } else {
                        $this->model->tambahUser($nama, password_hash($password, PASSWORD_DEFAULT), $role, $idD, $idP);
                        header("Location: index.php?action=user&pesan=tambah");
                        exit;
                    }
                }

                // Ubah peran / tautan / password
                if (isset($_POST['submit_update'])) {
                    $id     = (int) ($_POST['id'] ?? 0);
                    $target = $this->model->findById($id);
                    $role   = $_POST['role'] ?? '';
                    $passBaru = $_POST['password'] ?? '';
                    list($err, $idD, $idP) = $this->validasiRelasi($role, (int) ($_POST['id_dokter'] ?? 0), (int) ($_POST['id_pasien'] ?? 0));

                    if (!$target) {
                        $error = "Akun tidak ditemukan.";
                    } elseif (!in_array($role, UserModel::ROLE, true)) {
                        $error = "Peran tidak valid.";
                    } elseif ($id === $diriId && $role !== ($target['role'] ?? 'user')) {
                        $error = "Anda tidak dapat mengubah peran akun Anda sendiri.";
                    } elseif ($passBaru !== '' && strlen($passBaru) < 6) {
                        $error = "Password baru minimal 6 karakter.";
                    } elseif ($err) {
                        $error = $err;
                    } else {
                        $hash = $passBaru !== '' ? password_hash($passBaru, PASSWORD_DEFAULT) : null;
                        $this->model->updateUser($id, $role, $idD, $idP, $hash);
                        header("Location: index.php?action=user&pesan=update");
                        exit;
                    }
                }

                // Hapus akun
                if (isset($_POST['submit_hapus'])) {
                    $id = (int) ($_POST['id'] ?? 0);
                    if ($id === $diriId) {
                        $error = "Anda tidak dapat menghapus akun Anda sendiri.";
                    } else {
                        $this->model->hapusUser($id);
                        header("Location: index.php?action=user&pesan=hapus");
                        exit;
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }

        $userEdit = null;
        if (isset($_GET['mode']) && $_GET['mode'] === 'edit' && isset($_GET['id'])) {
            $userEdit = $this->model->findById((int) $_GET['id']);
        }

        // Pencarian & halaman
        $q     = kataKunciCari();
        $total = $this->model->hitung($q);
        list($hal, $offset, $totalHal) = hitungHalaman($total);
        $daftarUser   = $this->model->cari($q, PER_HALAMAN, $offset);
        $daftarDokter = $this->dokterModel->getAll();
        $daftarPasien = $this->pasienModel->getAll();
        require __DIR__ . '/../views/user/index.php';
    }
}