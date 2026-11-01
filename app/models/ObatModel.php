<?php
/**
 * Model Obat (tb_obat)
 * Kolom dibaca otomatis dari struktur tabel (SHOW COLUMNS), jadi tidak perlu
 * menulis nama kolom satu per satu.
 */
class ObatModel {
    private $conn;
    private $table = 'tb_obat';
    private $columns = null;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Struktur kolom: Field, Type, Null, Key, Extra
    public function getColumns() {
        if ($this->columns === null) {
            $this->columns = [];
            $result = $this->conn->query("SHOW COLUMNS FROM `{$this->table}`");
            while ($row = $result->fetch_assoc()) {
                $this->columns[] = $row;
            }
        }
        return $this->columns;
    }

    // Nama kolom primary key (kalau tidak ada, pakai kolom pertama)
    public function getPrimaryKey() {
        foreach ($this->getColumns() as $col) {
            if ($col['Key'] === 'PRI') {
                return $col['Field'];
            }
        }
        return $this->getColumns()[0]['Field'];
    }

    public function isAutoIncrement($field) {
        foreach ($this->getColumns() as $col) {
            if ($col['Field'] === $field) {
                return stripos($col['Extra'], 'auto_increment') !== false;
            }
        }
        return false;
    }

    // Kolom yang diisi lewat form. Saat tambah: semua kecuali auto_increment.
    // Saat edit: semua kecuali primary key.
    public function getInputColumns($modeEdit = false) {
        $pk = $this->getPrimaryKey();
        $hasil = [];
        foreach ($this->getColumns() as $col) {
            if ($modeEdit && $col['Field'] === $pk) continue;
            if (!$modeEdit && $this->isAutoIncrement($col['Field'])) continue;
            $hasil[] = $col;
        }
        return $hasil;
    }

    public function getAll() {
        $pk = $this->getPrimaryKey();
        $result = $this->conn->query("SELECT * FROM `{$this->table}` ORDER BY `$pk` ASC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id) {
        $pk = $this->getPrimaryKey();
        $stmt = $this->conn->prepare("SELECT * FROM `{$this->table}` WHERE `$pk` = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Ambil nilai dari form sesuai kolom yang diizinkan
    public function ambilDariForm($post, $modeEdit = false) {
        $nilai = [];
        foreach ($this->getInputColumns($modeEdit) as $col) {
            $v = $post[$col['Field']] ?? '';
            // Kolom yang boleh NULL dan dikosongkan -> simpan sebagai NULL
            if ($v === '' && $col['Null'] === 'YES') {
                $v = null;
            }
            $nilai[$col['Field']] = $v;
        }
        return $nilai;
    }

    public function tambahObat($nilai) {
        $kolom = '`' . implode('`, `', array_keys($nilai)) . '`';
        $tanda = implode(', ', array_fill(0, count($nilai), '?'));
        $stmt = $this->conn->prepare("INSERT INTO `{$this->table}` ($kolom) VALUES ($tanda)");
        $values = array_values($nilai);
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        return $stmt->execute();
    }

    public function updateObat($id, $nilai) {
        $pk = $this->getPrimaryKey();
        $set = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($nilai)));
        $stmt = $this->conn->prepare("UPDATE `{$this->table}` SET $set WHERE `$pk` = ?");
        $values = array_values($nilai);
        $values[] = $id;
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        return $stmt->execute();
    }

    public function hapusObat($id) {
        $pk = $this->getPrimaryKey();
        $stmt = $this->conn->prepare("DELETE FROM `{$this->table}` WHERE `$pk` = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
}