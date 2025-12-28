# PANDUAN PENGEMBANG JAGAPADI
## Instruksi Setup, Kontribusi, dan Standar Coding

Dokumen ini ditujukan bagi programmer yang ingin melakukan instalasi lokal, pengembangan fitur, atau pemeliharaan kode JAGAPADI.

***

## 1. SETUP LINGKUNGAN PENGEMBANGAN

### Rekomendasi Stack
- **OS**: Windows (dengan Laragon) atau Linux (Ubuntu/Debian).
- **PHP**: Versi 8.1 ke atas. 
- **Database**: MySQL 8.0 atau MariaDB 10.6 ke atas.
- **Extensions**: `pdo_mysql`, `gd`, `mbstring`, `curl`.

### Langkah Instalasi (Lokal via Laragon)
1. Letakkan folder project di `C:\laragon\www\jagapadi`.
2. Nyalakan Apache dan MySQL melalui panel Laragon.
3. Buka **phpMyAdmin**, buat database baru `bpsjembe_jagapadi`.
4. Import file `database/schema.sql`.
5. Edit file `config/database.php` dan sesuaikan `DB_USER` & `DB_PASS`.
6. Akses di browser: `http://jagapadi.test` atau `http://localhost/jagapadi`.

***

## 2. STANDAR CODING

Untuk menjaga kualitas kode, pengembang wajib mengikuti aturan berikut:

### Penamaan (Naming Convention)
- **Class**: PascalCase (Contoh: `UserController`, `LaporanHama`).
- **Method/Function**: camelCase (Contoh: `getAllUsers`, `saveReport`).
- **Variables**: camelCase (Contoh: `userId`, `dataLaporan`).
- **Database Table/Column**: snake_case (Contoh: `laporan_hama`, `user_id`).

### Struktur Unit Dasar
- Setiap modul baru harus memiliki **Model**, **Controller**, dan folder **View** terkait.
- Gunakan `Security::sanitizeInput()` untuk semua input data dari pengguna.
- Gunakan `CSRF Token` pada setiap form POST.

***

## 3. PANDUAN MODIFIKASI FITUR

### Menambah Modul Baru
1. Buat Model di `app/models/` (Extend `Model.php`).
2. Buat Controller di `app/controllers/` (Extend `Controller.php`).
3. Daftarkan rute jika menggunakan API di `app/core/Router.php`.
4. Buat folder view di `app/views/[modul_name]/` dan file `.php` terkait.

### Manajemen Assets
- Tambahkan file CSS di `public/css/`.
- Tambahkan file JavaScript di `public/js/`.
- Referensikan menggunakan konstanta `BASE_URL` di View.

***

## 4. DEPLOYMENT

Jika melakukan push ke production:
1. Pastikan `display_errors` di `config/config.php` disetel ke `0`.
2. Ubah `SIMITRA_API_URL` dan `SIMITRA_API_TOKEN` ke environment production.
3. Lakukan pengujian unit testing pada form input krusial.

***

> [!IMPORTANT]
> Selalu tarik (Pull) perubahan terbaru dari repository utama sebelum mulai mengerjakan fitur baru untuk menghindari konflik kode.
