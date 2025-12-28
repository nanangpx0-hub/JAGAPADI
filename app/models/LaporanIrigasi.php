<?php
/**
 * LaporanIrigasi Model
 * * Menangani operasi database untuk fitur sebaran irigasi.
 * Menggunakan QueryBuilder dan JOIN untuk performa optimal.
 * * @package app/models
 */
class LaporanIrigasi extends Model {
    protected $table = 'laporan_irigasi';

    /**
     * Get all reports with details (User, Wilayah)
     * Menggunakan JOIN untuk menghindari N+1 Query Problem
     * * @param int|null $userId Optional: Filter by User ID (untuk Petugas)
     * @return array
     */
    public function getAllWithDetails(?int $userId = null): array {
        $qb = new QueryBuilder();
        $qb->table('laporan_irigasi li')
           ->select([
               'li.*',
               'u.nama_lengkap as pelapor_nama',
               'u.role as pelapor_role',
               'kab.nama_kabupaten',
               'kec.nama_kecamatan',
               'des.nama_desa',
               'v.nama_lengkap as verifikator_nama'
           ])
           ->leftJoin('users u', 'li.user_id = u.id')
           ->leftJoin('master_kabupaten kab', 'li.kabupaten_id = kab.id')
           ->leftJoin('master_kecamatan kec', 'li.kecamatan_id = kec.id')
           ->leftJoin('master_desa des', 'li.desa_id = des.id')
           ->leftJoin('users v', 'li.verified_by = v.id');

        if ($userId !== null) {
            $qb->where('li.user_id', $userId);
        }

        return $qb->orderBy('li.tanggal', 'DESC')
                  ->orderBy('li.created_at', 'DESC')
                  ->get();
    }

    /**
     * Verify laporan (approve atau reject)
     * * @param int $id ID Laporan
     * @param string $status Status baru
     * @param int $verifiedBy User ID verifikator
     * @param string|null $catatan Catatan verifikasi
     * @return bool
     */
    public function verify(int $id, string $status, int $verifiedBy, ?string $catatan = null): bool {
        if (!in_array($status, ['Diverifikasi', 'Ditolak'])) {
            throw new InvalidArgumentException('Status tidak valid');
        }

        $data = [
            'status' => $status,
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s'),
            'catatan_verifikasi' => $catatan
        ];

        return $this->update($id, $data);
    }

    /**
     * Get single report with all details
     * 
     * @param int $id Report ID
     * @return array|null
     */
    public function getDetailById(int $id): ?array {
        $qb = new QueryBuilder();
        $result = $qb->table('laporan_irigasi li')
           ->select([
               'li.*',
               'u.nama_lengkap as pelapor_nama',
               'u.role as pelapor_role',
               'u.email as pelapor_email',
               'kab.nama_kabupaten',
               'kec.nama_kecamatan',
               'des.nama_desa',
               'v.nama_lengkap as verifikator_nama'
           ])
           ->leftJoin('users u', 'li.user_id = u.id')
           ->leftJoin('master_kabupaten kab', 'li.kabupaten_id = kab.id')
           ->leftJoin('master_kecamatan kec', 'li.kecamatan_id = kec.id')
           ->leftJoin('master_desa des', 'li.desa_id = des.id')
           ->leftJoin('users v', 'li.verified_by = v.id')
           ->where('li.id', $id)
           ->limit(1)
           ->get();
        
        return !empty($result) ? $result[0] : null;
    }
}

