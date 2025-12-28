# DOKUMENTASI KOMPREHENSIF JAGAPADI v2.2.5
## Panduan Lengkap Pengembangan, Operasional, dan Pemeliharaan Sistem

**Versi Dokumen**: 2.2.5  
**Tanggal**: 28 Desember 2025  
**Status**: Production Ready  
**Penyusun**: Nanang Pamungkas

***

## 1. PENDAHULUAN & RINGKASAN EKSEKUTIF

**JAGAPADI** (Jember Agrikultur Gapai Prestasi Digital) adalah platform pemetaan dan pelaporan terintegrasi untuk sektor pertanian di Kabupaten Jember. Aplikasi ini dirancang untuk memantau serangan Organisme Pengganggu Tumbuhan (OPT), kondisi irigasi, dan data curah hujan secara real-time guna mendukung pengambilan keputusan strategis.

### Visi & Teknologi Utama
- **Frontend**: AdminLTE 3.2, Chart.js, Leaflet.js
- **Backend**: PHP 8.x (Custom MVC Framework)
- **Database**: MySQL 8.x
- **Integrasi**: Simitra API v2

***

## 2. ARSITEKTUR SISTEM

JAGAPADI menggunakan pola arsitektur **Model-View-Controller (MVC)** kustom.

### Struktur Direktori Inti:
- `app/controllers/`: Logika aplikasi per modul.
- `app/models/`: Interaksi database PDO.
- `app/views/`: Template presentasi UI.
- `app/core/`: Kernel aplikasi (Router & Base classes).

### Alur Request:
Request masuk melalui `index.php` -> Router memanggil Controller -> Controller meminta data ke Model -> Model mengambil data dari DB -> Controller memuat View dengan data tersebut.

***

## 3. STRUKTUR DATABASE

### Tabel Utama:
1. **users**: Otentikasi dan role (Admin, Operator, Petugas, Viewer).
2. **laporan_hama**: Data serangan OPT beserta koordinat GPS dan foto.
3. **master_opt**: Referensi jenis hama, penyakit, dan gulma.
4. **curah_hujan**: Data monitoring curah hujan harian.
5. **activity_log**: Audit trail aktivitas pengguna.

***

## 4. PANDUAN PENGGUNA (USER GUIDE)

### Role User:
- **Admin**: Manajemen sistem dan user.
- **Operator**: Verifikasi laporan lapangan.
- **Petugas**: Input data laporan lapangan.
- **Viewer**: Monitoring dashboard dan ekspor data.

### Workflow Pelaporan:
Petugas Submit Laporan -> Operator Review & Verifikasi -> Data muncul di Dashboard Real-time.

***

## 5. KEAMANAN & PROTEKSI

- **SQL Injection**: Proteksi via PDO Prepared Statements.
- **XSS & CSRF**: Proteksi via sanitasi input dan validasi token sesi.
- **Password**: Hashing menggunakan algoritma `BCRYPT`.
- **Akses**: Role Based Access Control (RBAC).

***

## 6. PANDUAN PENGEMBANG (DEVELOPER GUIDE)

### Setup Lokal:
1. Clone repo ke `C:\laragon\www\jagapadi`.
2. Impor `database/schema.sql` ke MySQL.
3. Konfigurasi `config/database.php`.
4. Akses `http://jagapadi.test`.

***

## 7. REFERENSI API

Endpoint API tersedia untuk integrasi data wilayah dan pengiriman laporan secara programatik menggunakan Token-based authentication.

***

## 8. RIWAYAT PERUBAHAN (CHANGELOG)

- **v2.2.5**: Rilis dokumentasi lengkap dan perbaikan navigasi irigasi.
- **v2.2.3**: Penambahan modul Curah Hujan dan fitur Force Password Change.
- **v2.2.1**: Migrasi keamanan password ke BCRYPT.
- **v2.0.0**: Rilis awal arsitektur MVC dan integrasi Simitra.

***

> [!NOTE]
> Dokumentasi ini adalah single source of truth untuk seluruh stakeholder aplikasi JAGAPADI.
