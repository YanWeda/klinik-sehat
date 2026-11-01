<?php
class KonsultasiModel {
    private $conn;
    const PENGIRIM = ['Pasien', 'Dokter'];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // $idDokter / $idPasien = null berarti tanpa filter
    public function getAll($idDokter = null, $idPasien = null) {
        $sql = "SELECT k.*, p.nama AS nama_pasien, d.nama_dokter, d.spesialis,
                       (SELECT COUNT(*) FROM tb_pesan_konsultasi m WHERE m.id_konsultasi = k.id_konsultasi) AS jumlah_pesan
                FROM tb_konsultasi k
                LEFT JOIN tb_pasien p ON p.id_pasien = k.id_pasien
                LEFT JOIN tb_dokter d ON d.id_dokter = k.id_dokter
                WHERE 1 = 1";
        $tipe  = '';
        $param = [];
        if ($idDokter !== null) { $sql .= " AND k.id_dokter = ?"; $tipe .= 'i'; $param[] = (int) $idDokter; }
        if ($idPasien !== null) { $sql .= " AND k.id_pasien = ?"; $tipe .= 'i'; $param[] = (int) $idPasien; }
        $sql .= " ORDER BY k.id_konsultasi DESC";

        $stmt = $this->conn->prepare($sql);
        if ($tipe !== '') {
            $stmt->bind_param($tipe, ...$param);
        }
        $stmt->execute();
        $data = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT k.*, p.nama AS nama_pasien, d.nama_dokter, d.spesialis
             FROM tb_konsultasi k
             LEFT JOIN tb_pasien p ON p.id_pasien = k.id_pasien
             LEFT JOIN tb_dokter d ON d.id_dokter = k.id_dokter
             WHERE k.id_konsultasi = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPesan($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM tb_pesan_konsultasi WHERE id_konsultasi = ? ORDER BY id_pesan ASC"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $data = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Mulai sesi konsultasi baru, mengembalikan id-nya
    public function mulai($idPasien, $idDokter, $topik) {
        $stmt = $this->conn->prepare(
            "INSERT INTO tb_konsultasi (id_pasien, id_dokter, topik) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $idPasien, $idDokter, $topik);
        $stmt->execute();
        return (int) $this->conn->insert_id;
    }

    // Kirim pesan. False bila konsultasi tidak ada / sudah selesai.
    public function kirimPesan($id, $pengirim, $isi) {
        if (!in_array($pengirim, self::PENGIRIM, true)) {
            return false;
        }
        $k = $this->getById($id);
        if (!$k || $k['status'] === 'Selesai') {
            return false;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO tb_pesan_konsultasi (id_konsultasi, pengirim, isi) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $id, $pengirim, $isi);
        $stmt->execute();

        // Balasan pertama dari dokter = konsultasi mulai berlangsung
        if ($pengirim === 'Dokter' && $k['status'] === 'Menunggu') {
            $up = $this->conn->prepare("UPDATE tb_konsultasi SET status = 'Berlangsung' WHERE id_konsultasi = ?");
            $up->bind_param("i", $id);
            $up->execute();
        }
        return true;
    }

    public function selesai($id) {
        $stmt = $this->conn->prepare(
            "UPDATE tb_konsultasi SET status = 'Selesai', selesai_pada = NOW()
             WHERE id_konsultasi = ? AND status <> 'Selesai'"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function hapus($id) {
        $this->conn->begin_transaction();
        try {
            $a = $this->conn->prepare("DELETE FROM tb_pesan_konsultasi WHERE id_konsultasi = ?");
            $a->bind_param("i", $id);
            $a->execute();
            $b = $this->conn->prepare("DELETE FROM tb_konsultasi WHERE id_konsultasi = ?");
            $b->bind_param("i", $id);
            $b->execute();
            $this->conn->commit();
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // ---- pencarian + halaman (urutan: terbaru di atas) ----
    private function filterCari($q, $idDokter, $idPasien, &$tipe, &$param) {
        $w = [];
        if ($idDokter !== null) { $w[] = "k.id_dokter = ?"; $tipe .= 'i'; $param[] = (int) $idDokter; }
        if ($idPasien !== null) { $w[] = "k.id_pasien = ?"; $tipe .= 'i'; $param[] = (int) $idPasien; }
        if ($q !== '') {
            $w[] = "(p.nama LIKE ? OR d.nama_dokter LIKE ? OR k.topik LIKE ? OR k.status LIKE ?)";
            $like = likeCari($q);
            $tipe .= 'ssss';
            array_push($param, $like, $like, $like, $like);
        }
        return $w ? ' WHERE ' . implode(' AND ', $w) : '';
    }

    public function hitung($q = '', $idDokter = null, $idPasien = null) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $idDokter, $idPasien, $tipe, $param);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM tb_konsultasi k
             LEFT JOIN tb_pasien p ON p.id_pasien = k.id_pasien
             LEFT JOIN tb_dokter d ON d.id_dokter = k.id_dokter" . $where
        );
        if ($tipe !== '') $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function cari($q, $limit, $offset, $idDokter = null, $idPasien = null) {
        $tipe = ''; $param = [];
        $where = $this->filterCari($q, $idDokter, $idPasien, $tipe, $param);
        $stmt = $this->conn->prepare(
            "SELECT k.*, p.nama AS nama_pasien, d.nama_dokter, d.spesialis,
                    (SELECT COUNT(*) FROM tb_pesan_konsultasi m WHERE m.id_konsultasi = k.id_konsultasi) AS jumlah_pesan
             FROM tb_konsultasi k
             LEFT JOIN tb_pasien p ON p.id_pasien = k.id_pasien
             LEFT JOIN tb_dokter d ON d.id_dokter = k.id_dokter" . $where . "
             ORDER BY k.id_konsultasi DESC LIMIT ? OFFSET ?"
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