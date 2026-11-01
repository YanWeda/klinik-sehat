<?php
require_once __DIR__ . '/../models/PasienModel.php';

class ProfilController {
    private $pasienModel;

    public function __construct($conn) {
        $this->pasienModel = new PasienModel($conn);
    }

    public function index() {
        $idPasien = idPasienSaatIni();
        $pasien   = $idPasien > 0 ? $this->pasienModel->getById($idPasien) : null;

        // Akun admin/dokter tidak punya data pasien
        if (!$pasien) {
            header("Location: index.php");
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_update'])) {
            $jk      = $_POST['jenis_kelamin'] ?? '';
            $keluhan = trim($_POST['keluhan'] ?? '');

            if (!in_array($jk, ['Laki-laki', 'Perempuan'], true)) {
                $error = "Pilih jenis kelamin.";
            } elseif ($keluhan === '' || strlen($keluhan) > 255) {
                $error = "Keluhan wajib diisi dan maksimal 255 karakter.";
            } else {
                try {
                    $this->pasienModel->updatePasien($idPasien, $pasien['nama'], $jk, $keluhan);
                    header("Location: index.php?action=profil&pesan=update");
                    exit;
                } catch (mysqli_sql_exception $e) {
                    $error = "Terjadi kesalahan database: " . $e->getMessage();
                }
            }
            $pasien = $this->pasienModel->getById($idPasien);
        }

        require __DIR__ . '/../views/profil/index.php';
    }
}