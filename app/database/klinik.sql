-- =====================================================================
--  KLINIK SEHAT - skema database lengkap (instalasi baru)
--
--  Cara pakai (phpMyAdmin): menu Import -> pilih file ini -> Go.
--  Database `klinik` dibuat otomatis. Jalankan SEKALI pada database kosong.
--
--  Akun admin bawaan :  nama = admin   |   password = admin123
--  (GANTI password ini setelah login pertama: menu Kelola User -> Edit)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `klinik` DEFAULT CHARACTER SET utf8mb4;
USE `klinik`;

-- Akun pengguna (peran: admin / dokter / user)
CREATE TABLE IF NOT EXISTS `tb_user` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `nama`      varchar(50)  NOT NULL,
  `password`  varchar(255) NOT NULL,
  `role`      enum('admin','dokter','user') NOT NULL DEFAULT 'user',
  `id_dokter` int(11)      DEFAULT NULL,
  `id_pasien` int(11)      DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data dokter
CREATE TABLE IF NOT EXISTS `tb_dokter` (
  `id_dokter`     int(11)      NOT NULL AUTO_INCREMENT,
  `nama_dokter`   varchar(100) NOT NULL,
  `spesialis`     varchar(100) NOT NULL,
  `jenis_kelamin` varchar(20)  NOT NULL,
  PRIMARY KEY (`id_dokter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data pasien
CREATE TABLE IF NOT EXISTS `tb_pasien` (
  `id_pasien`     int(11)      NOT NULL AUTO_INCREMENT,
  `nama`          varchar(100) NOT NULL,
  `jenis_kelamin` varchar(20)  NOT NULL,
  `keluhan`       varchar(255) NOT NULL,
  PRIMARY KEY (`id_pasien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Antrian pasien (nomor berurutan per dokter per tanggal)
CREATE TABLE IF NOT EXISTS `tb_antrian` (
  `id_antrian` int(11) NOT NULL AUTO_INCREMENT,
  `id_pasien`  int(11) NOT NULL,
  `id_dokter`  int(11) NOT NULL,
  `tanggal`    date    NOT NULL,
  `no_antrian` int(11) NOT NULL,
  `status`     enum('Menunggu','Dipanggil','Selesai','Batal') NOT NULL DEFAULT 'Menunggu',
  `dibuat`     datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_antrian`),
  UNIQUE KEY `uq_nomor` (`id_dokter`, `tanggal`, `no_antrian`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_pasien` (`id_pasien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Konsultasi online
CREATE TABLE IF NOT EXISTS `tb_konsultasi` (
  `id_konsultasi` int(11)      NOT NULL AUTO_INCREMENT,
  `id_pasien`     int(11)      NOT NULL,
  `id_dokter`     int(11)      NOT NULL,
  `topik`         varchar(150) NOT NULL,
  `status`        enum('Menunggu','Berlangsung','Selesai') NOT NULL DEFAULT 'Menunggu',
  `dibuat`        datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `selesai_pada`  datetime     DEFAULT NULL,
  PRIMARY KEY (`id_konsultasi`),
  KEY `idx_status` (`status`),
  KEY `idx_pasien` (`id_pasien`),
  KEY `idx_dokter` (`id_dokter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pesan (chat) di dalam konsultasi
CREATE TABLE IF NOT EXISTS `tb_pesan_konsultasi` (
  `id_pesan`      int(11)  NOT NULL AUTO_INCREMENT,
  `id_konsultasi` int(11)  NOT NULL,
  `pengirim`      enum('Pasien','Dokter') NOT NULL,
  `isi`           text     NOT NULL,
  `dibuat`        datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pesan`),
  KEY `idx_konsultasi` (`id_konsultasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Data awal
-- ---------------------------------------------------------------------
INSERT INTO `tb_user` (`nama`, `password`, `role`) VALUES
('admin', '$2y$10$DYrb3W8xI.uORQarkvlHpOSAZzka2j/wtGot5kov4aHAx.xfbE11W', 'admin');

INSERT INTO `tb_dokter` (`nama_dokter`, `spesialis`, `jenis_kelamin`) VALUES
('dr. Ayu Lestari',  'Dokter Umum', 'Perempuan'),
('dr. Made Putra',   'Anak',        'Laki-laki'),
('dr. Komang Sari',  'Gigi',        'Perempuan');

INSERT INTO `tb_pasien` (`nama`, `jenis_kelamin`, `keluhan`) VALUES
('Kadek Arya',  'Laki-laki', 'Demam sejak kemarin'),
('Ni Luh Ayu',  'Perempuan', 'Batuk dan pilek'),
('Putu Gede',   'Laki-laki', 'Sakit kepala'),
('Wayan Sri',   'Perempuan', 'Nyeri gigi'),
('Gede Mahesa', 'Laki-laki', 'Kontrol rutin');