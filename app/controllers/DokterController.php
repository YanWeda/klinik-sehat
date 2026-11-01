<?php
require_once __DIR__ . '/../models/DokterModel.php';

class DokterController {
    private $model;

    public function __construct($conn) {
        $this->model = new DokterModel($conn);
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Tambah
            if (isset($_POST['submit_tambah'])) {
                $this->model->tambahDokter(
                    $_POST['nama_dokter'], $_POST['spesialis'], $_POST['jenis_kelamin']
                );
                header("Location: index.php?action=dokter&status=tambah");
                exit;
            }

            // Update
            if (isset($_POST['submit_update'])) {
                $this->model->updateDokter(
                    (int) $_POST['id_dokter'],
                    $_POST['nama_dokter'], $_POST['spesialis'], $_POST['jenis_kelamin']
                );
                header("Location: index.php?action=dokter&status=update");
                exit;
            }

            // Hapus
            if (isset($_POST['submit_hapus'])) {
                $this->model->hapusDokter((int) $_POST['id_dokter']);
                header("Location: index.php?action=dokter&status=hapus");
                exit;
            }
        }

        // Mode edit: ambil data dokter yang dipilih untuk mengisi form
        $dokterEdit = null;
        if (isset($_GET['mode']) && $_GET['mode'] === 'edit' && isset($_GET['id'])) {
            $dokterEdit = $this->model->getById((int) $_GET['id']);
        }

        // Pencarian & halaman
        $q     = kataKunciCari();
        $total = $this->model->hitung($q);
        list($hal, $offset, $totalHal) = hitungHalaman($total);
        $daftarDokter = $this->model->cari($q, PER_HALAMAN, $offset);
        require __DIR__ . '/../views/dokter/index.php';
    }
}