<?php
class DokterModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM tb_dokter ORDER BY id_dokter ASC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM tb_dokter WHERE id_dokter = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tambahDokter($nama, $spesialis, $jk) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tb_dokter (nama_dokter, spesialis, jenis_kelamin) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $nama, $spesialis, $jk);
        return $stmt->execute();
    }

    // Sama dengan tambahDokter, tetapi mengembalikan id baru
    public function buatDokterBaru($nama, $spesialis, $jk) {
        $this->tambahDokter($nama, $spesialis, $jk);
        return (int) $this->conn->insert_id;
    }

    public function updateDokter($id, $nama, $spesialis, $jk) {
        $stmt = $this->conn->prepare(
            "UPDATE tb_dokter SET nama_dokter = ?, spesialis = ?, jenis_kelamin = ? WHERE id_dokter = ?"
        );
        $stmt->bind_param("sssi", $nama, $spesialis, $jk, $id);
        return $stmt->execute();
    }

    public function hapusDokter($id) {
        $stmt = $this->conn->prepare("DELETE FROM tb_dokter WHERE id_dokter = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ---- pencarian + halaman (urutan: terbaru di atas) ----
    private function filterCari($q, &$tipe, &$param) {
        if ($q === '') return '';
        $like = likeCari($q);
        $tipe .= 'ssss';
        array_push($param, $like, $like, $like, $like);
        return " WHERE (nama_dokter LIKE ? OR spesialis LIKE ? OR jenis_kelamin LIKE ? OR CAST(id_dokter AS CHAR) LIKE ?)";
    }

    public function hitung($q = '') {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $tipe, $param);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tb_dokter" . $where);
        if ($tipe !== '') $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function cari($q, $limit, $offset) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $tipe, $param);
        $stmt = $this->conn->prepare("SELECT * FROM tb_dokter" . $where . " ORDER BY id_dokter DESC LIMIT ? OFFSET ?");
        $tipe .= 'ii';
        $param[] = (int) $limit;
        $param[] = (int) $offset;
        $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        $data = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $data[] = $row;
        return $data;
    }
}