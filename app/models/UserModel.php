<?php
class UserModel {
    private $conn;
    const ROLE = ['admin', 'dokter', 'user'];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function findByNama($nama) {
        $stmt = $this->conn->prepare("SELECT * FROM tb_user WHERE nama = ? LIMIT 1");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM tb_user WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAll() {
        $result = $this->conn->query(
            "SELECT u.*, d.nama_dokter, p.nama AS nama_pasien
             FROM tb_user u
             LEFT JOIN tb_dokter d ON d.id_dokter = u.id_dokter
             LEFT JOIN tb_pasien p ON p.id_pasien = u.id_pasien
             ORDER BY u.id ASC"
        );
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function tambahUser($nama, $passwordHash, $role = 'user', $idDokter = null, $idPasien = null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tb_user (nama, password, role, id_dokter, id_pasien) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssii", $nama, $passwordHash, $role, $idDokter, $idPasien);
        return $stmt->execute();
    }

    // Ubah peran & tautan; password hanya diganti bila $passwordHash diisi
    public function updateUser($id, $role, $idDokter, $idPasien, $passwordHash = null) {
        if ($passwordHash !== null) {
            $stmt = $this->conn->prepare(
                "UPDATE tb_user SET role = ?, id_dokter = ?, id_pasien = ?, password = ? WHERE id = ?"
            );
            $stmt->bind_param("siisi", $role, $idDokter, $idPasien, $passwordHash, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE tb_user SET role = ?, id_dokter = ?, id_pasien = ? WHERE id = ?"
            );
            $stmt->bind_param("siii", $role, $idDokter, $idPasien, $id);
        }
        return $stmt->execute();
    }

    public function setPasien($id, $idPasien) {
        $stmt = $this->conn->prepare("UPDATE tb_user SET id_pasien = ? WHERE id = ?");
        $stmt->bind_param("ii", $idPasien, $id);
        return $stmt->execute();
    }

    public function hapusUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM tb_user WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ---- pencarian + halaman (urutan: terbaru di atas) ----
    private function filterCari($q, &$tipe, &$param) {
        if ($q === '') return '';
        $like = likeCari($q);
        $tipe .= 'ssss';
        array_push($param, $like, $like, $like, $like);
        return " WHERE (u.nama LIKE ? OR u.role LIKE ? OR d.nama_dokter LIKE ? OR p.nama LIKE ?)";
    }

    public function hitung($q = '') {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $tipe, $param);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM tb_user u
             LEFT JOIN tb_dokter d ON d.id_dokter = u.id_dokter
             LEFT JOIN tb_pasien p ON p.id_pasien = u.id_pasien" . $where
        );
        if ($tipe !== '') $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function cari($q, $limit, $offset) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $tipe, $param);
        $stmt = $this->conn->prepare(
            "SELECT u.*, d.nama_dokter, p.nama AS nama_pasien
             FROM tb_user u
             LEFT JOIN tb_dokter d ON d.id_dokter = u.id_dokter
             LEFT JOIN tb_pasien p ON p.id_pasien = u.id_pasien" . $where . "
             ORDER BY u.id DESC LIMIT ? OFFSET ?"
        );
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