<?php
class AntrianModel {
    private $conn;
    const STATUS = ['Menunggu', 'Dipanggil', 'Selesai', 'Batal'];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Antrian pada satu tanggal (urut per dokter lalu nomor).
    // $idDokter / $idPasien = null berarti tanpa filter.
    public function getByTanggal($tanggal, $idDokter = null, $idPasien = null) {
        $sql = "SELECT a.*, p.nama AS nama_pasien, d.nama_dokter, d.spesialis
                FROM tb_antrian a
                LEFT JOIN tb_pasien p ON p.id_pasien = a.id_pasien
                LEFT JOIN tb_dokter d ON d.id_dokter = a.id_dokter
                WHERE a.tanggal = ?";
        $tipe  = 's';
        $param = [$tanggal];
        if ($idDokter !== null) { $sql .= " AND a.id_dokter = ?"; $tipe .= 'i'; $param[] = (int) $idDokter; }
        if ($idPasien !== null) { $sql .= " AND a.id_pasien = ?"; $tipe .= 'i'; $param[] = (int) $idPasien; }
        $sql .= " ORDER BY d.nama_dokter ASC, a.no_antrian ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($tipe, ...$param);
        $stmt->execute();
        $data = [];
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM tb_antrian WHERE id_antrian = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Pasien yang sama tidak boleh punya 2 antrian aktif ke dokter yang sama di hari yang sama
    public function sudahAntri($idPasien, $idDokter, $tanggal) {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM tb_antrian
             WHERE id_pasien = ? AND id_dokter = ? AND tanggal = ? AND status IN ('Menunggu','Dipanggil')"
        );
        $stmt->bind_param("iis", $idPasien, $idDokter, $tanggal);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0] > 0;
    }

    // Membuat nomor antrian berikutnya untuk dokter + tanggal. Mengembalikan nomornya.
    public function tambah($idPasien, $idDokter, $tanggal) {
        $terakhir = null;
        for ($percobaan = 0; $percobaan < 3; $percobaan++) {
            try {
                $this->conn->begin_transaction();

                $stmt = $this->conn->prepare(
                    "SELECT COALESCE(MAX(no_antrian), 0) + 1 FROM tb_antrian
                     WHERE id_dokter = ? AND tanggal = ? FOR UPDATE"
                );
                $stmt->bind_param("is", $idDokter, $tanggal);
                $stmt->execute();
                $no = (int) $stmt->get_result()->fetch_row()[0];

                $ins = $this->conn->prepare(
                    "INSERT INTO tb_antrian (id_pasien, id_dokter, tanggal, no_antrian, status)
                     VALUES (?, ?, ?, ?, 'Menunggu')"
                );
                $ins->bind_param("iisi", $idPasien, $idDokter, $tanggal, $no);
                $ins->execute();

                $this->conn->commit();
                return $no;
            } catch (mysqli_sql_exception $e) {
                $this->conn->rollback();
                $terakhir = $e;
                if ((int) $e->getCode() !== 1062) {   // 1062 = nomor bentrok, coba lagi
                    throw $e;
                }
            }
        }
        throw $terakhir;
    }

    public function ubahStatus($id, $status) {
        if (!in_array($status, self::STATUS, true)) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE tb_antrian SET status = ? WHERE id_antrian = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function hapus($id) {
        $stmt = $this->conn->prepare("DELETE FROM tb_antrian WHERE id_antrian = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}