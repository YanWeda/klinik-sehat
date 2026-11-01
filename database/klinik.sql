-- =====================================================================
--  KLINIK SEHAT - skema database lengkap (instalasi baru)
--
--  Cara pakai (phpMyAdmin): menu Import -> pilih file ini -> Go.
--  Database `klinik` dibuat otomatis. Jalankan SEKALI pada database kosong.
--
--  Data contoh: setiap tabel berisi 11 data (siap dipakai untuk demo).
--
--  Akun contoh (nama -> password):
--     admin                          -> admin123    (peran Admin)
--     dr.ayu / dr.made / dr.komang   -> dokter123   (peran Dokter)
--     kadek, luhayu, putu, wayan,
--     gede, komang, made             -> pasien123   (peran User / pasien)
--  GANTI password ini jika aplikasi dipasang online (menu Kelola User -> Edit).
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
--  Data contoh (masing-masing 11 data)
-- ---------------------------------------------------------------------

-- 11 dokter
INSERT INTO `tb_dokter` (`id_dokter`, `nama_dokter`, `spesialis`, `jenis_kelamin`) VALUES
(1,  'dr. Ayu Lestari',       'Dokter Umum',        'Perempuan'),
(2,  'dr. Made Putra',        'Anak',               'Laki-laki'),
(3,  'dr. Komang Sari',       'Gigi',               'Perempuan'),
(4,  'dr. Wayan Adnyana',     'Penyakit Dalam',     'Laki-laki'),
(5,  'dr. Ketut Dewi',        'Kandungan',          'Perempuan'),
(6,  'dr. Nyoman Arta',       'Bedah',              'Laki-laki'),
(7,  'dr. Gusti Ayu Mirah',   'Kulit dan Kelamin',  'Perempuan'),
(8,  'dr. Putu Wijaya',       'THT',                'Laki-laki'),
(9,  'dr. Kadek Sintya',      'Mata',               'Perempuan'),
(10, 'dr. Agus Pranata',      'Saraf',              'Laki-laki'),
(11, 'dr. Dewa Ayu Rani',     'Gizi Klinik',        'Perempuan');

-- 11 pasien
INSERT INTO `tb_pasien` (`id_pasien`, `nama`, `jenis_kelamin`, `keluhan`) VALUES
(1,  'Kadek Arya',      'Laki-laki', 'Demam sejak kemarin'),
(2,  'Ni Luh Ayu',      'Perempuan', 'Batuk dan pilek'),
(3,  'Putu Gede',       'Laki-laki', 'Sakit kepala'),
(4,  'Wayan Sri',       'Perempuan', 'Nyeri gigi'),
(5,  'Gede Mahesa',     'Laki-laki', 'Kontrol rutin'),
(6,  'Komang Ratna',    'Perempuan', 'Sakit perut'),
(7,  'Made Surya',      'Laki-laki', 'Nyeri punggung'),
(8,  'Ni Nyoman Dewi',  'Perempuan', 'Gatal-gatal pada kulit'),
(9,  'I Ketut Budi',    'Laki-laki', 'Sesak napas ringan'),
(10, 'Luh Sari',        'Perempuan', 'Pusing dan mual'),
(11, 'Bagus Wirawan',   'Laki-laki', 'Batuk berdahak');

-- 11 akun: 1 admin, 3 dokter (terhubung ke dokter 1-3), 7 pasien (terhubung ke pasien 1-7)
INSERT INTO `tb_user` (`id`, `nama`, `password`, `role`, `id_dokter`, `id_pasien`) VALUES
(1,  'admin',     '$2y$10$dVfdt6UXhGnLrBF/rdE1yOFoRJouhcC.wtCkKRJQ9nR/z41JR29fW',  'admin',  NULL, NULL),
(2,  'dr.ayu',    '$2y$10$tr/RiZwhi9pKaIZMWTweseTtqj8xagtU5RwrtaPadJUXBV5SIJeLS', 'dokter', 1,    NULL),
(3,  'dr.made',   '$2y$10$tr/RiZwhi9pKaIZMWTweseTtqj8xagtU5RwrtaPadJUXBV5SIJeLS', 'dokter', 2,    NULL),
(4,  'dr.komang', '$2y$10$tr/RiZwhi9pKaIZMWTweseTtqj8xagtU5RwrtaPadJUXBV5SIJeLS', 'dokter', 3,    NULL),
(5,  'kadek',     '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 1),
(6,  'luhayu',    '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 2),
(7,  'putu',      '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 3),
(8,  'wayan',     '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 4),
(9,  'gede',      '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 5),
(10, 'komang',    '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 6),
(11, 'made',      '$2y$10$J0txn0MNAt8cX77IQrzOx.hCp6BpV0oiY/vndCiZ.gBYJfy2sWErG', 'user',   NULL, 7);

-- 11 antrian (tanggal = hari saat database diimpor, nomor mulai dari 1 untuk tiap dokter)
INSERT INTO `tb_antrian` (`id_antrian`, `id_pasien`, `id_dokter`, `tanggal`, `no_antrian`, `status`) VALUES
(1,  1,  1, CURDATE(), 1, 'Selesai'),
(2,  2,  1, CURDATE(), 2, 'Selesai'),
(3,  3,  1, CURDATE(), 3, 'Dipanggil'),
(4,  4,  1, CURDATE(), 4, 'Menunggu'),
(5,  5,  2, CURDATE(), 1, 'Selesai'),
(6,  6,  2, CURDATE(), 2, 'Dipanggil'),
(7,  7,  2, CURDATE(), 3, 'Menunggu'),
(8,  8,  2, CURDATE(), 4, 'Batal'),
(9,  9,  3, CURDATE(), 1, 'Selesai'),
(10, 10, 3, CURDATE(), 2, 'Menunggu'),
(11, 11, 3, CURDATE(), 3, 'Menunggu');

-- 11 konsultasi online
INSERT INTO `tb_konsultasi` (`id_konsultasi`, `id_pasien`, `id_dokter`, `topik`, `status`, `dibuat`, `selesai_pada`) VALUES
(1,  1,  1,  'Demam tinggi sejak 3 hari',                'Selesai',     DATE_SUB(NOW(), INTERVAL 7200 MINUTE), DATE_SUB(NOW(), INTERVAL 7140 MINUTE)),
(2,  2,  1,  'Batuk berdahak dan pilek',                 'Berlangsung', DATE_SUB(NOW(), INTERVAL 4300 MINUTE), NULL),
(3,  3,  10, 'Sakit kepala berulang',                    'Selesai',     DATE_SUB(NOW(), INTERVAL 3000 MINUTE), DATE_SUB(NOW(), INTERVAL 2950 MINUTE)),
(4,  4,  3,  'Gigi berlubang dan terasa nyeri',          'Berlangsung', DATE_SUB(NOW(), INTERVAL 1500 MINUTE), NULL),
(5,  5,  4,  'Konsultasi hasil cek tekanan darah',       'Menunggu',    DATE_SUB(NOW(), INTERVAL 900 MINUTE),  NULL),
(6,  6,  4,  'Sakit perut dan mual',                     'Menunggu',    DATE_SUB(NOW(), INTERVAL 700 MINUTE),  NULL),
(7,  7,  6,  'Nyeri punggung setelah mengangkat beban',  'Menunggu',    DATE_SUB(NOW(), INTERVAL 500 MINUTE),  NULL),
(8,  8,  7,  'Gatal dan ruam pada lengan',               'Menunggu',    DATE_SUB(NOW(), INTERVAL 300 MINUTE),  NULL),
(9,  9,  4,  'Sesak napas saat beraktivitas',            'Menunggu',    DATE_SUB(NOW(), INTERVAL 200 MINUTE),  NULL),
(10, 10, 1,  'Pusing dan mual di pagi hari',             'Menunggu',    DATE_SUB(NOW(), INTERVAL 100 MINUTE),  NULL),
(11, 11, 1,  'Batuk berdahak sudah seminggu',            'Menunggu',    DATE_SUB(NOW(), INTERVAL 30 MINUTE),   NULL);

-- 11 pesan chat (konsultasi 1 = 4 pesan, 2 = 3 pesan, 3 = 2 pesan, 4 = 2 pesan)
INSERT INTO `tb_pesan_konsultasi` (`id_pesan`, `id_konsultasi`, `pengirim`, `isi`, `dibuat`) VALUES
(1,  1, 'Pasien', 'Dok, saya demam tinggi sejak tiga hari lalu dan badan terasa lemas. Sudah minum paracetamol tetapi belum turun.', DATE_SUB(NOW(), INTERVAL 7200 MINUTE)),
(2,  1, 'Dokter', 'Selamat pagi. Apakah ada batuk, nyeri tenggorokan, atau muntah? Berapa suhu tertinggi yang terukur?',                  DATE_SUB(NOW(), INTERVAL 7190 MINUTE)),
(3,  1, 'Pasien', 'Tidak ada batuk dan tidak muntah. Suhu tertinggi 38,7 derajat.',                                                       DATE_SUB(NOW(), INTERVAL 7180 MINUTE)),
(4,  1, 'Dokter', 'Perbanyak minum air putih dan istirahat. Lanjutkan paracetamol tiap 6 jam bila demam. Jika lebih dari 3 hari atau muncul bintik merah, segera periksa ke klinik.', DATE_SUB(NOW(), INTERVAL 7170 MINUTE)),
(5,  2, 'Pasien', 'Dok, saya batuk berdahak dan pilek sudah lima hari. Apakah perlu minum antibiotik?',                                    DATE_SUB(NOW(), INTERVAL 4300 MINUTE)),
(6,  2, 'Dokter', 'Belum tentu. Apakah dahaknya berwarna kuning kehijauan dan disertai demam?',                                            DATE_SUB(NOW(), INTERVAL 4290 MINUTE)),
(7,  2, 'Pasien', 'Dahaknya agak kuning dan tidak demam. Kadang sesak ringan kalau malam.',                                               DATE_SUB(NOW(), INTERVAL 4280 MINUTE)),
(8,  3, 'Pasien', 'Dok, saya sering sakit kepala sebelah terutama pada sore hari.',                                                       DATE_SUB(NOW(), INTERVAL 3000 MINUTE)),
(9,  3, 'Dokter', 'Kurangi begadang dan waktu menatap layar, serta cukupi minum air. Bila disertai muntah atau pandangan kabur, segera ke klinik.', DATE_SUB(NOW(), INTERVAL 2990 MINUTE)),
(10, 4, 'Pasien', 'Gigi geraham kiri saya berlubang dan nyeri saat minum yang dingin.',                                                     DATE_SUB(NOW(), INTERVAL 1500 MINUTE)),
(11, 4, 'Dokter', 'Sebaiknya segera diperiksa. Sementara itu hindari makanan terlalu dingin atau manis dan sikat gigi dengan lembut.',    DATE_SUB(NOW(), INTERVAL 1490 MINUTE));
