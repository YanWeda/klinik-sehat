<?php
class PasienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM tb_pasien ORDER BY id_pasien ASC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Pasien yang pernah antri atau berkonsultasi dengan dokter tertentu
    public function getUntukDokter($idDokter) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tb_pasien
             WHERE id_pasien IN (SELECT id_pasien FROM tb_antrian WHERE id_dokter = ?)
                OR id_pasien IN (SELECT id_pasien FROM tb_konsultasi WHERE id_dokter = ?)
             ORDER BY id_pasien ASC"
        );
        $stmt->bind_param("ii", $idDokter, $idDokter);
        $stmt->execute();
        $data = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM tb_pasien WHERE id_pasien = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tambahPasien($nama, $jk, $keluhan) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tb_pasien (nama, jenis_kelamin, keluhan) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $nama, $jk, $keluhan);
        return $stmt->execute();
    }

    // Sama dengan tambahPasien, tetapi mengembalikan id baru
    public function buatPasienBaru($nama, $jk, $keluhan) {
        $this->tambahPasien($nama, $jk, $keluhan);
        return (int) $this->conn->insert_id;
    }

    public function updatePasien($id, $nama, $jk, $keluhan) {
        $stmt = $this->conn->prepare(
            "UPDATE tb_pasien SET nama = ?, jenis_kelamin = ?, keluhan = ? WHERE id_pasien = ?"
        );
        $stmt->bind_param("sssi", $nama, $jk, $keluhan, $id);
        return $stmt->execute();
    }

    public function hapusPasien($id) {
        $stmt = $this->conn->prepare("DELETE FROM tb_pasien WHERE id_pasien = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ---- pencarian + halaman (urutan: terbaru di atas) ----
    private function filterCari($q, $idDokter, &$tipe, &$param) {
        $w = [];
        if ($idDokter !== null) {
            $w[] = "(id_pasien IN (SELECT id_pasien FROM tb_antrian WHERE id_dokter = ?)
                  OR id_pasien IN (SELECT id_pasien FROM tb_konsultasi WHERE id_dokter = ?))";
            $tipe .= 'ii';
            $param[] = (int) $idDokter;
            $param[] = (int) $idDokter;
        }
        if ($q !== '') {
            $w[] = "(nama LIKE ? OR jenis_kelamin LIKE ? OR keluhan LIKE ? OR CAST(id_pasien AS CHAR) LIKE ?)";
            $like = likeCari($q);
            $tipe .= 'ssss';
            array_push($param, $like, $like, $like, $like);
        }
        return $w ? ' WHERE ' . implode(' AND ', $w) : '';
    }

    public function hitung($q = '', $idDokter = null) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $idDokter, $tipe, $param);
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tb_pasien" . $where);
        if ($tipe !== '') $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function cari($q, $limit, $offset, $idDokter = null) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $idDokter, $tipe, $param);
        $stmt = $this->conn->prepare("SELECT * FROM tb_pasien" . $where . " ORDER BY id_pasien DESC LIMIT ? OFFSET ?");
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