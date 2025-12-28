# KEBIJAKAN KEAMANAN & PROTEKSI JAGAPADI
## Implementasi Keamanan Sistem dan Manajemen Akses

Dokumen ini menjelaskan lapisan keamanan yang diimplementasikan dalam aplikasi JAGAPADI untuk melindungi data sensitif dan integritas sistem.

***

## 1. PROTEKSI TERHADAP SERANGAN UMUM

Aplikasi menggunakan class `Security` (`app/helpers/Security.php`) sebagai pusat kendali keamanan.

| Jenis Serangan | Implementasi Proteksi |
|----------------|-----------------------|
| **SQL Injection** | Penggunaan `PDO Prepared Statements` pada semua query database. |
| **Cross-Site Scripting (XSS)** | Sanitasi input menggunakan `htmlspecialchars()` dan filter data. |
| **CSRF** | Token unik yang dihasilkan per sesi dan divalidasi pada setiap request POST. |
| **Brute Force** | Pembatasan percobaan login (Rate Limiting) dan logging IP address. |

***

## 2. MANAJEMEN SESI & KREDENSIAL

- **Password Hashing**: Menggunakan algoritma `PASSWORD_BCRYPT` dengan cost 12.
- **Session Timeout**: Sesi akan otomatis berakhir setelah 2 jam tidak ada aktivitas (konfigurasi `SESSION_LIFETIME`).
- **Session Regeneration**: ID Sesi diperbarui setiap kali pengguna berhasil login untuk mencegah *Session Fixation*.
- **Force Password Change**: Fitur yang mewajibkan pengguna mengganti password default pada saat login pertama kali.

***

## 3. ACCESS CONTROL MATRIX (ACM)

Sistem menggunakan Role Based Access Control (RBAC).

| Fitur | Admin | Operator | Petugas | Viewer |
|-------|:-----:|:--------:|:-------:|:------:|
| Dashboard & Grafik | ✅ | ✅ | ✅ | ✅ |
| Buat Laporan Hama | ✅ | ❌ | ✅ | ❌ |
| Verifikasi Laporan | ✅ | ✅ | ❌ | ❌ |
| Master Data OPT | ✅ | ✅ | ❌ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ | ❌ |
| Master Wilayah | ✅ | ❌ | ❌ | ❌ |
| Ekspor Data | ✅ | ✅ | ❌ | ✅ |

***

## 4. LOGGING & AUDIT TRAIL

Setiap aksi krusial dicatat dalam tabel `activity_log`:
- User ID yang melakukan aksi.
- Jenis aksi (Login, Tambah Laporan, Verifikasi, dll).
- Nama tabel dan ID record yang terdampak.
- Alamat IP dan User Agent perangkat.

***

## 5. KEAMANAN FILE UPLOAD

- **Validasi Ekstensi**: Hanya mengizinkan file gambar (`jpg`, `jpeg`, `png`).
- **Validasi Ukuran**: Maksimal 2MB per file.
- **Sanitasi Nama File**: Nama file diubah menggunakan hash unik untuk mencegah akses langsung yang dapat dieksploitasi.

***

> [!CAUTION]
> Jangan pernah membagikan file `.env` (jika ada) atau file konfigurasi di `config/` kepada pihak yang tidak berwenang karena berisi kredensial database dan API key.
