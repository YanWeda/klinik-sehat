<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PasienModel.php';
require_once __DIR__ . '/../models/DokterModel.php';

class AuthController {
    private $conn;
    private $model;
    private $pasienModel;
    private $dokterModel;

    public function __construct($conn) {
        $this->conn        = $conn;
        $this->model       = new UserModel($conn);
        $this->pasienModel = new PasienModel($conn);
        $this->dokterModel = new DokterModel($conn);
    }

    private function isiSesi($user) {
        $_SESSION['user_id']        = (int) $user['id'];
        $_SESSION['user_nama']      = $user['nama'];
        $_SESSION['user_role']      = $user['role'] ?? 'user';
        $_SESSION['user_id_dokter'] = (int) ($user['id_dokter'] ?? 0);
        $_SESSION['user_id_pasien'] = (int) ($user['id_pasien'] ?? 0);
    }

    // Dipanggil di setiap permintaan: peran & tautan selalu dibaca ulang dari database,
    // sehingga perubahan peran / penghapusan akun oleh admin langsung berlaku.
    public function segarkanSesi() {
        $u = $this->model->findById((int) ($_SESSION['user_id'] ?? 0));
        if (!$u) {
            $_SESSION = [];
            return false;
        }

        // Akun User wajib punya data pasien; buat otomatis bila belum ada
        if (($u['role'] ?? 'user') === 'user') {
            $idPasien = (int) ($u['id_pasien'] ?? 0);
            if ($idPasien <= 0 || !$this->pasienModel->getById($idPasien)) {
                $idPasien = $this->pasienModel->buatPasienBaru($u['nama'], '-', '-');
                $this->model->setPasien((int) $u['id'], $idPasien);
                $u['id_pasien'] = $idPasien;
            }
        }

        $this->isiSesi($u);
        return true;
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }

        $error = null;
        $namaInput = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_login'])) {
            $namaInput = trim($_POST['nama'] ?? '');
            $password  = $_POST['password'] ?? '';

            $user = $this->model->findByNama($namaInput);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $this->isiSesi($user);
                header("Location: index.php");
                exit;
            }

            $error = "Nama atau password salah.";
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    // Registrasi PASIEN: membuat akun berperan "user" + data pasien
    public function register() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }

        $error = null;
        $namaInput = '';
        $jkInput = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_register'])) {
            $namaInput  = trim($_POST['nama'] ?? '');
            $jkInput    = $_POST['jenis_kelamin'] ?? '';
            $password   = $_POST['password'] ?? '';
            $konfirmasi = $_POST['konfirmasi'] ?? '';

            if ($namaInput === '' || strlen($namaInput) > 50) {
                $error = "Nama wajib diisi dan maksimal 50 karakter.";
            } elseif (!in_array($jkInput, ['Laki-laki', 'Perempuan'], true)) {
                $error = "Pilih jenis kelamin.";
            } elseif (strlen($password) < 6) {
                $error = "Password minimal 6 karakter.";
            } elseif ($password !== $konfirmasi) {
                $error = "Konfirmasi password tidak sama.";
            } elseif ($this->model->findByNama($namaInput)) {
                $error = "Nama tersebut sudah terdaftar, gunakan nama lain.";
            } else {
                try {
                    $this->conn->begin_transaction();
                    $idPasien = $this->pasienModel->buatPasienBaru($namaInput, $jkInput, '-');
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->model->tambahUser($namaInput, $hash, 'user', null, $idPasien);
                    $this->conn->commit();
                    header("Location: index.php?action=login&status=registered");
                    exit;
                } catch (mysqli_sql_exception $e) {
                    $this->conn->rollback();
                    $error = "Registrasi gagal: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/auth/register.php';
    }

    // Registrasi DOKTER: wajib memakai kode registrasi dari klinik (lihat app/config.php)
    public function registerDokter() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }

        $error = null;
        $namaInput = '';
        $spesialisInput = '';
        $jkInput = '';
        $kodeBenar = defined('KODE_REGISTRASI_DOKTER') ? (string) KODE_REGISTRASI_DOKTER : '';
        $perluKode = ($kodeBenar !== '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_register_dokter'])) {
            $namaInput      = trim($_POST['nama'] ?? '');
            $spesialisInput = trim($_POST['spesialis'] ?? '');
            $jkInput        = $_POST['jenis_kelamin'] ?? '';
            $password       = $_POST['password'] ?? '';
            $konfirmasi     = $_POST['konfirmasi'] ?? '';
            $kode           = trim($_POST['kode'] ?? '');

            if ($namaInput === '' || strlen($namaInput) > 50) {
                $error = "Nama wajib diisi dan maksimal 50 karakter.";
            } elseif ($spesialisInput === '' || strlen($spesialisInput) > 100) {
                $error = "Spesialis wajib diisi dan maksimal 100 karakter.";
            } elseif (!in_array($jkInput, ['Laki-laki', 'Perempuan'], true)) {
                $error = "Pilih jenis kelamin.";
            } elseif (strlen($password) < 6) {
                $error = "Password minimal 6 karakter.";
            } elseif ($password !== $konfirmasi) {
                $error = "Konfirmasi password tidak sama.";
            } elseif ($perluKode && !hash_equals($kodeBenar, $kode)) {
                $error = "Kode registrasi dokter salah. Hubungi admin klinik untuk mendapatkan kodenya.";
            } elseif ($this->model->findByNama($namaInput)) {
                $error = "Nama tersebut sudah terdaftar, gunakan nama lain.";
            } else {
                try {
                    $this->conn->begin_transaction();
                    $idDokter = $this->dokterModel->buatDokterBaru($namaInput, $spesialisInput, $jkInput);
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->model->tambahUser($namaInput, $hash, 'dokter', $idDokter, null);
                    $this->conn->commit();
                    header("Location: index.php?action=login&status=registered_dokter");
                    exit;
                } catch (mysqli_sql_exception $e) {
                    $this->conn->rollback();
                    $error = "Registrasi gagal: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/auth/register_dokter.php';
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            header("Location: index.php?action=login&status=logout");
            exit;
        }
        header("Location: index.php");
        exit;
    }
}