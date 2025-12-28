# RIWAYAT PERUBAHAN (CHANGELOG) JAGAPADI
## Pencatatan Pembaruan Versi dan Perbaikan Sistem

Daftar perubahan mayor dan minor pada aplikasi JAGAPADI.

***

### v2.2.5 (2025-12-28) - TERBARU
- **[NEW]**: Implementasi sistem dokumentasi komprehensif berbasis standar JAGAPADI v2.2.4.
- **[ENHANCEMENT]**: Peningkatan visual pada dashboard grafik menggunakan data real-time.
- **[FIX]**: Perbaikan tautan menu pada sidebar untuk navigasi Modul Irigasi.
- **[SECURITY]**: Penambahan validasi tipe file yang lebih ketat pada form laporan.

### v2.2.3 (2025-12-26)
- **[NEW]**: Modul Curah Hujan dengan fitur scraper otomatis dari BMKG.
- **[NEW]**: Fitur "Force Password Change" untuk user baru yang ditambahkan oleh Admin.
- **[DATABASE]**: Migrasi tabel `users` untuk menyertakan kolom `must_change_password` dan `last_password_change_at`.
- **[FIX]**: Perbaikan error `PDOException` pada query dengan Bound LIMIT/OFFSET.

### v2.2.1 (2025-12-20)
- **[NEW]**: Modul Manajemen Wilayah (Kabupaten, Kecamatan, Desa).
- **[ENHANCEMENT]**: Migrasi algoritma password dari MD5 ke BCRYPT untuk keamanan lebih tinggi.
- **[FIX]**: Perbaikan bug checkbox "Pilih Semua" pada manajemen user.
- **[FIX]**: Optimalisasi performa pemuatan peta sebaran untuk data di atas 1000 titik.

### v2.0.0 (2025-10-15)
- **[NEW]**: Rilis utama aplikasi JAGAPADI dengan arsitektur MVC.
- **[NEW]**: Integrasi API Simitra untuk sinkronisasi data mitra pertanian.
- **[NEW]**: Dashboard dengan statistik ringkasan serangan OPT.
- **[NEW]**: Fitur pelaporan serangan hama berbasis koordinat.

***

## KETERANGAN LABEL
- **NEW**: Fitur atau fungsionalitas baru.
- **FIX**: Perbaikan bug atau kesalahan sistem.
- **ENHANCEMENT**: Peningkatan pada fitur yang sudah ada.
- **BREAKING**: Perubahan yang tidak kompatibel dengan versi sebelumnya.
- **SECURITY**: Perbaikan pada celah keamanan.
- **DATABASE**: Perubahan skema atau struktur database.

***

> [!NOTE]
> Versi rilis JAGAPADI mengikuti standar Semantic Versioning (Major.Minor.Patch).
