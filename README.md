# Klinik Sehat — Sistem Informasi Klinik

Aplikasi web sistem informasi klinik berbasis **PHP native dengan pola MVC** dan database **MySQL/MariaDB**.
Mendukung antrian pasien, konsultasi online (chat dengan dokter), pengelolaan data pasien dan dokter, serta
tiga peran akun (Admin, Dokter, User/Pasien) dengan tampilan dan hak akses yang berbeda.

## Fitur

- **Login & registrasi** — registrasi terpisah untuk **Pasien** dan **Dokter** (dokter memakai kode registrasi).
- **Antrian** — ambil nomor per dokter per tanggal, panggil / selesai / batal, papan nomor yang sedang dipanggil.
- **Konsultasi online** — chat pasien–dokter, status Menunggu → Berlangsung → Selesai.
- **Data pasien & dokter** — tambah, ubah, hapus.
- **Kelola User** (admin) — atur akun, peran, dan menghubungkan akun dokter ke data dokter.
- **Pencarian & halaman** — setiap daftar punya kotak cari, 10 data per halaman, urutan terbaru di atas.
- **Tampilan per peran** — warna dan menu berbeda untuk Admin, Dokter, dan User.

### Hak akses

| Fitur | Admin | Dokter | User (pasien) |
|---|---|---|---|
| Beranda | Statistik seluruh klinik | Ringkasan praktik sendiri | Ringkasan pribadi |
| Antrian | Semua, penuh | Antrian pasien dokter itu | Ambil nomor & batalkan miliknya |
| Konsultasi | Semua | Miliknya (membalas, mengakhiri) | Miliknya (mulai & membalas) |
| Pasien | Tambah / ubah / hapus | Hanya lihat (pasien terkait) | — (hanya Profil Saya) |
| Dokter | Penuh | — | — |
| Kelola User | Ya | — | — |

## Teknologi

- PHP 8.1 atau lebih baru (ekstensi `mysqli`)
- MySQL 5.7+ / MariaDB 10.3+
- HTML & CSS (tanpa framework)
- Server lokal: Laragon atau XAMPP

## Cara menjalankan

1. **Salin proyek** ke folder web server, misalnya `C:\laragon\www\klinik` (Laragon) atau `C:\xampp\htdocs\klinik` (XAMPP).
   ```bash
   git clone https://github.com/USERNAME/NAMA-REPO.git klinik
   ```
2. **Buat database.** Buka phpMyAdmin → menu **Import** → pilih `database/klinik.sql` → **Go**.
   Database `klinik` beserta semua tabel dan data awal akan dibuat otomatis.
3. **Atur koneksi.** Salin `koneksi.example.php` menjadi `koneksi.php`, lalu sesuaikan host, user, dan password MySQL
   jika berbeda dari bawaan (`root`, tanpa password).
4. **Buka di browser:** `http://localhost/klinik/`

### Akun bawaan

| Peran | Nama | Password |
|---|---|---|
| Admin | `admin` | `admin123` |

> **Ganti password admin** segera setelah login pertama (menu **Kelola User → Edit**).

- **Pasien** — daftar sendiri lewat tombol *Pasien* di halaman login.
- **Dokter** — daftar lewat tombol *Dokter* di halaman login dan masukkan **kode registrasi dokter**.
  Kode diatur di `app/config.php` (`KODE_REGISTRASI_DOKTER`, bawaan `KLINIK-DOKTER-2026`) — **ganti dengan kode Anda sendiri**.
  Akun dokter juga bisa dibuat oleh admin lewat menu Kelola User.

## Pengaturan

Semua ada di `app/config.php`:

| Konstanta | Fungsi |
|---|---|
| `NAMA_KLINIK` | Nama klinik yang tampil di menu dan judul halaman |
| `KODE_REGISTRASI_DOKTER` | Kode rahasia untuk mendaftar sebagai dokter (kosongkan = tanpa kode) |
| `PER_HALAMAN` | Jumlah data per halaman pada setiap daftar |

## Struktur folder

```
.
├── index.php                  # titik masuk & routing (?action=...)
├── koneksi.example.php        # contoh koneksi database (salin menjadi koneksi.php)
├── assets/css/style.css       # tampilan
├── database/
│   └── klinik.sql             # skema + data awal (untuk instalasi baru)
└── app/
    ├── config.php             # pengaturan aplikasi
    ├── helpers.php            # fungsi bantu (pencarian, halaman, format)
    ├── auth.php               # peta hak akses per peran
    ├── controllers/           # logika per halaman
    ├── models/                # akses database
    └── views/                 # tampilan (HTML)
```

File lain di `database/` (jika ada) adalah skrip migrasi untuk memperbarui database versi lama dan
**tidak perlu dijalankan** pada instalasi baru.

## Catatan keamanan

- Password disimpan dalam bentuk hash (`password_hash`), semua query memakai *prepared statement*,
  dan hak akses diperiksa di sisi server pada setiap permintaan.
- `koneksi.php` tidak disertakan di repositori (lihat `.gitignore`); jangan mengunggah kata sandi database asli.