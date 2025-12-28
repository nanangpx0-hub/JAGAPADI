# REFERENSI API JAGAPADI
## Dokumentasi Endpoint untuk Integrasi dan Mobile App

Aplikasi JAGAPADI menyediakan antarmuka API RESTful untuk sinkronisasi data dengan sistem eksternal (Simitra) dan mendukung fungsionalitas aplikasi mobile (future development).

***

## 1. KEBIJAKAN AUTENTIKASI

Semua request API (kecuali Login) memerlukan header autentikasi.

- **Metode**: Token-based (Bearer Token atau API-Key kustom).
- **Header**: `Authorization: Bearer <your_token>` atau `X-API-Key: <your_key>`.

***

## 2. ENDPOINT WILAYAH (READ-ONLY)

Digunakan untuk mengambil data referensi wilayah administrasi.

| Method | Endpoint | Deskripsi |
|-------|----------|-----------|
| `GET` | `/api/wilayah/kabupaten` | List semua kabupaten. |
| `GET` | `/api/wilayah/kecamatan/<kab_id>` | List kecamatan per kabupaten. |
| `GET` | `/api/wilayah/desa/<kec_id>` | List desa per kecamatan. |

**Contoh Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {"id": 1, "kode_kabupaten": "35.09", "nama_kabupaten": "JEMBER"},
    ...
  ]
}
```

***

## 3. ENDPOINT LAPORAN HAMA

| Method | Endpoint | Deskripsi | Auth |
|-------|----------|-----------|------|
| `GET` | `/api/laporan` | List laporan (filter per user). | YES |
| `POST`| `/api/laporan` | Submit laporan baru dari lapangan. | YES |
| `GET` | `/api/laporan/stats` | Statistik ringkasan untuk dashboard. | YES |

### POST /api/laporan
**Request Body (JSON):**
```json
{
  "master_opt_id": 1,
  "tanggal": "2025-12-28",
  "lokasi": "Blok A, Desa Sukorejo",
  "latitude": -8.123456,
  "longitude": 113.123456,
  "tingkat_keparahan": "Sedang",
  "populasi": 100,
  "foto_base64": "..." 
}
```

***

## 4. ENDPOINT CURAH HUJAN (SINKRONISASI)

Digunakan oleh layanan scraper atau sensor otomatis.

| Method | Endpoint | Deskripsi |
|-------|----------|-----------|
| `POST` | `/api/curahHujan/sync` | Mengirim data curah hujan terbaru. |
| `GET`  | `/api/curahHujan/latest` | Mengambil data curah hujan terakhir. |

***

## 5. KODE STATUS HTTP

| Kode | Arti | Deskripsi |
|------|------|-----------|
| `200` | OK | Request berhasil. |
| `201` | Created | Resource berhasil dibuat. |
| `400` | Bad Request | Parameter tidak lengkap atau format salah. |
| `401` | Unauthorized | Token tidak valid atau expired. |
| `403` | Forbidden | Role tidak diizinkan mengakses resource. |
| `404` | Not Found | Endpoint tidak ditemukan. |
| `500` | Server Error | Masalah pada server aplikasi. |

***

> [!WARNING]
> Jangan publikasikan API Key atau Bearer Token Anda. Gunakan environment variables untuk penyimpanan token di sisi klien.
