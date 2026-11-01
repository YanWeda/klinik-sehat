<?php
require_once __DIR__ . '/../models/PasienModel.php';

class PasienController {
    private $model;

    public function __construct($conn) {
        $this->model = new PasienModel($conn);
    }

    public function index() {
        $bisaUbah = adalahAdmin();   // dokter hanya boleh melihat

        if ($bisaUbah && $_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['submit_tambah'])) {
                $this->model->tambahPasien($_POST['nama'], $_POST['jenis_kelamin'], $_POST['keluhan']);
                header("Location: index.php?action=pasien&status=tambah");
                exit;
            }

            if (isset($_POST['submit_update'])) {
                $this->model->updatePasien(
                    (int) $_POST['id_pasien'], $_POST['nama'], $_POST['jenis_kelamin'], $_POST['keluhan']
                );
                header("Location: index.php?action=pasien&status=update");
                exit;
            }

            if (isset($_POST['submit_hapus'])) {
                $this->model->hapusPasien((int) $_POST['id_pasien']);
                header("Location: index.php?action=pasien&status=hapus");
                exit;
            }
        }

        $pasienEdit = null;
        if ($bisaUbah && isset($_GET['mode']) && $_GET['mode'] === 'edit' && isset($_GET['id'])) {
            $pasienEdit = $this->model->getById((int) $_GET['id']);
        }

        // Pencarian & halaman (admin: semua pasien, dokter: pasien terkait saja)
        $q            = kataKunciCari();
        $filterDokter = $bisaUbah ? null : idDokterSaatIni();
        $total        = $this->model->hitung($q, $filterDokter);
        list($hal, $offset, $totalHal) = hitungHalaman($total);
        $daftarPasien = $this->model->cari($q, PER_HALAMAN, $offset, $filterDokter);
        require __DIR__ . '/../views/pasien/index.php';
    }
}