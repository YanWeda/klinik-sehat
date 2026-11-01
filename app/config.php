<?php
// Pengaturan umum aplikasi
date_default_timezone_set('Asia/Makassar');   // WITA (Bali)
define('NAMA_KLINIK', 'Klinik Sehat');         // ganti nama klinik di sini

// Kode rahasia untuk mendaftar sebagai DOKTER lewat halaman registrasi.
// Berikan kode ini hanya kepada dokter klinik, dan GANTI nilainya dengan kode Anda sendiri.
// Dikosongkan ('') = tanpa kode (tidak disarankan: siapa pun bisa mendaftar sebagai dokter).
define('KODE_REGISTRASI_DOKTER', 'KLINIK-DOKTER-2026');

// Jumlah data per halaman pada setiap daftar (tabel).
define('PER_HALAMAN', 10);