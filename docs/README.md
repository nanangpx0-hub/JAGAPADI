# DOKUMENTASI KOMPREHENSIF APLIKASI JAGAPADI v2.2.5
## Panduan Utama untuk Pengembangan, Operasional, dan Pemeliharaan Sistem

**Versi Dokumen**: 2.2.5  
**Tanggal**: 28 Desember 2025  
**Status**: Production Ready  
**Penyusun**: Nanang Pamungkas

***

## DAFTAR ISI

1. [Ringkasan Eksekutif](#ringkasan-eksekutif)
2. [Panduan Cepat (Quick Start)](#panduan-cepat)
3. [Daftar Dokumen](#daftar-dokumen)
4. [Role User & Kebijakan Akses](#role-user--kebijakan-akses)
5. [Kontak & Dukungan](#kontak--dukungan)

***

## RINGKASAN EKSEKUTIF

**JAGAPADI** (Jember Agrikultur Gapai Prestasi Digital) adalah platform pemetaan dan pelaporan terintegrasi untuk sektor pertanian di Kabupaten Jember. Aplikasi ini dirancang untuk memantau serangan Organisme Pengganggu Tumbuhan (OPT), kondisi irigasi, dan data curah hujan secara real-time guna mendukung pengambilan keputusan strategis.

### Visi & Tujuan
- **Digitalisasi Pertanian**: Transformasi pelaporan manual menjadi sistem digital yang akurat.
- **Monitoring Real-time**: Visualisasi sebaran hama dan kondisi infrastruktur pertanian melalui peta interaktif.
- **Efisiensi Kerja**: Sinkronisasi data mitra dan kegiatan melalui integrasi API Simitra BPS.

### Teknologi Utama
- **Frontend**: AdminLTE 3.2, Chart.js, Leaflet.js
- **Backend**: PHP 8.x (Custom MVC Framework)
- **Database**: MySQL 8.x
- **Integrasi**: Simitra API v2

***

## PANDUAN CEPAT

### Persyaratan Sistem
- Web Server (Apache/Nginx)
- PHP >= 8.0
- MySQL >= 5.7
- Koneksi Internet (untuk peta dan sinkronisasi API)

### Instalasi Singkat
1. Clone repository ke direktori web root.
2. Impor database dari `database/schema.sql`.
3. Sesuaikan konfigurasi di `config/config.php` dan `config/database.php`.
4. Akses melalui browser: `http://localhost/jagapadi/`.

***

## DAFTAR DOKUMEN

| Dokumen | Deskripsi |
|---------|-----------|
| [ARCHITECTURE.md](file:///c:/laragon/www/jagapadi/docs/ARCHITECTURE.md) | Penjelasan pola MVC dan struktur folder. |
| [API_REFERENCE.md](file:///c:/laragon/www/jagapadi/docs/API_REFERENCE.md) | Definisi endpoint API dan format data. |
| [DATABASE_SCHEMA.md](file:///c:/laragon/www/jagapadi/docs/DATABASE_SCHEMA.md) | Detail tabel dan relasi database. |
| [USER_GUIDE.md](file:///c:/laragon/www/jagapadi/docs/USER_GUIDE.md) | Panduan penggunaan untuk setiap role user. |
| [DEVELOPER_GUIDE.md](file:///c:/laragon/www/jagapadi/docs/DEVELOPER_GUIDE.md) | Panduan setup lingkungan pengembangan. |
| [SECURITY.md](file:///c:/laragon/www/jagapadi/docs/SECURITY.md) | Implementasi sistem keamanan dan proteksi. |
| [CHANGELOG.md](file:///c:/laragon/www/jagapadi/docs/CHANGELOG.md) | Riwayat perubahan versi aplikasi. |

***

## ROLE USER & KEBIJAKAN AKSES

| Role | Kode | Level | Fokus Utama |
|------|------|-------|-------------|
| **Admin** | `admin` | 5 | Manajemen sistem, user, dan konfigurasi master. |
| **Operator** | `operator` | 4 | Verifikasi laporan dan manajemen data operasional. |
| **Petugas** | `petugas` | 3 | Input laporan lapangan dan monitoring wilayah tugas. |
| **Viewer** | `viewer` | 1 | Monitoring visual dan ekspor data (Read-only). |

***

## KONTAK & DUKUNGAN

Jika Anda menemukan kendala atau membutuhkan akses tambahan, silakan hubungi:

- **Lead Developer**: Nanang Pamungkas
- **Email**: nanangpx@gmail.com
- **Repository**: [GitHub/Laragon JAGAPADI]

> [!NOTE]
> Dokumentasi ini diperbarui secara berkala mengikuti rilis fitur terbaru JAGAPADI.
