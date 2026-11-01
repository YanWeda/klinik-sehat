<?php
class DashboardModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function hitung($sql, $tipe = '', ...$param) {
        $stmt = $this->conn->prepare($sql);
        if ($tipe !== '') {
            $stmt->bind_param($tipe, ...$param);
        }
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function getStatistik($tanggal) {
        return [
            'pasien'      => $this->hitung("SELECT COUNT(*) FROM tb_pasien"),
            'dokter'      => $this->hitung("SELECT COUNT(*) FROM tb_dokter"),
            'antrian'     => $this->hitung("SELECT COUNT(*) FROM tb_antrian WHERE tanggal = ?", "s", $tanggal),
            'menunggu'    => $this->hitung("SELECT COUNT(*) FROM tb_antrian WHERE tanggal = ? AND status = 'Menunggu'", "s", $tanggal),
            'konsultasi'  => $this->hitung("SELECT COUNT(*) FROM tb_konsultasi WHERE status IN ('Menunggu','Berlangsung')"),
        ];
    }
}