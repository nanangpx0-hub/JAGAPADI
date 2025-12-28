# PANDUAN PENGGUNA JAGAPADI
## Instruksi Operasional Berdasarkan Role User

Dokumen ini berisi panduan langkah demi langkah penggunaan fitur utama di JAGAPADI.

***

## 1. PANDUAN UMUM

### LOGIN KE SISTEM
1. Akses halaman login via browser.
2. Masukkan **Username** dan **Password** Anda.
3. Klik **Login**. 
4. Jika akun Anda diatur untuk "Force Change Password", sistem akan meminta Anda mengganti password terlebih dahulu sebelum masuk ke dashboard.

***

## 2. PANDUAN PER ROLE

### 2.1 Role: ADMIN
**Tanggung Jawab**: Manajemen infrastruktur data dan pengguna.

- **Manajemen User**:
  - Menu: `Manajemen User`.
  - Fungsi: Tambah, edit, nonaktifkan, atau hapus user. Atur role (Admin/Operator/Viewer/Petugas).
- **Master Wilayah**:
  - Menu: `Master Wilayah`.
  - Fungsi: Update data Kabupaten, Kecamatan, dan Desa sebagai referensi laporan.
- **Monitoring Sistem**:
  - Cek `Activity Log` untuk memantau aktivitas mencurigakan.

### 2.2 Role: OPERATOR
**Tanggung Jawab**: Verifikasi data lapangan dan operasional harian.

- **Verifikasi Laporan Hama**:
  - Masuk ke menu `Laporan Hama`.
  - Lihat laporan dengan status `Submitted`.
  - Klik `Detail` > `Verifikasi`. Beri catatan jika diterima atau alasan jika ditolak.
- **Manajemen Master OPT**:
  - Tambah jenis hama/penyakit baru beserta deskripsi dan ambang batas ekonomi (ETL).

### 2.3 Role: PETUGAS (LAPANGAN)
**Tanggung Jawab**: Pelapor utama kondisi riil pertanian.

- **Input Laporan Hama**:
  - Menu: `Laporan Hama` > `Tambah Laporan`.
  - Isi form lengkap, pastikan koordinat GPS akurat.
  - Upload foto kondisi lapangan.
  - Klik `Simpan sebagai Draf` jika ingin mengedit nanti, atau `Submit` untuk mengirim ke Operator.

### 2.4 Role: VIEWER
**Tanggung Jawab**: Pemantauan data (Bimpinan/Stakeholder).

- **Dashboard & Map**: Analisis visual persebaran hama melalui titik-titik di peta.
- **Grafik & Statistik**: Pantau tren bulanan dan perbandingan antar kecamatan.
- **Ekspor Data**: Download data ke format CSV/Excel/PDF untuk keperluan laporan eksternal.

***

## 3. WORKFLOW UTAMA

### PROSES PELAPORAN HINGGA VERIFIKASI
```
[Petugas] Input Data -> Submit
    │
    ├─► [Sistem] Cek Validasi & Notifikasi ke Operator
    │
    ├─► [Operator] Review Foto & Koordinat
    │       ├─ [Valid] -> Status: Diverifikasi
    │       └─ [Invalid] -> Status: Ditolak (Input alasan)
    │
    └─► [Sistem] Update Dashboard & Grafik Real-time
```

***

## 4. TROUBLESHOOTING UMUM

- **Lupa Password**: Hubungi Admin untuk melakukan reset password sementara.
- **Peta Tidak Muncul**: Pastikan Anda memiliki koneksi internet aktif karena sistem memuat base-layer peta dari provider eksternal.
- **Gagal Upload Foto**: Pastikan format file adalah JPG/PNG dan ukuran tidak melebihi 2MB.

***

> [!NOTE]
> Panduan ini dapat diakses kapan saja dari menu "Help" (dalam pengembangan) di aplikasi.
