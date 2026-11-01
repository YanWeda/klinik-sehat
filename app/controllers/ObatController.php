<?php
require_once __DIR__ . '/../models/ObatModel.php';

class ObatController {
    private $model;

    public function __construct($conn) {
        $this->model = new ObatModel($conn);
    }

    public function index() {
        $bisaUbah = adalahAdmin();   // dokter hanya boleh melihat
        $error = null;

        if ($bisaUbah && $_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (isset($_POST['submit_tambah'])) {
                    $this->model->tambahObat($this->model->ambilDariForm($_POST, false));
                    header("Location: index.php?action=obat&status=tambah");
                    exit;
                }

                if (isset($_POST['submit_update'])) {
                    $this->model->updateObat($_POST['kunci'], $this->model->ambilDariForm($_POST, true));
                    header("Location: index.php?action=obat&status=update");
                    exit;
                }

                if (isset($_POST['submit_hapus'])) {
                    $this->model->hapusObat($_POST['kunci']);
                    header("Location: index.php?action=obat&status=hapus");
                    exit;
                }
            } catch (mysqli_sql_exception $e) {
                $error = "Gagal menyimpan data: " . $e->getMessage();
            }
        }

        $obatEdit = null;
        if ($bisaUbah && isset($_GET['mode']) && $_GET['mode'] === 'edit' && isset($_GET['id'])) {
            $obatEdit = $this->model->getById($_GET['id']);
        }

        $kolomObat  = $this->model->getColumns();
        $kolomForm  = $this->model->getInputColumns($obatEdit !== null);
        $kunciObat  = $this->model->getPrimaryKey();
        $daftarObat = $this->model->getAll();
        require __DIR__ . '/../views/obat/index.php';
    }
}