<?php
class LaporanHama extends Model {
    protected $table = 'laporan_hama';

    /**
     * Get reports with pagination using QueryBuilder
     */
    public function getWithPagination(array $filters = [], int $page = 1, int $limit = 20): array {
        $qb = new QueryBuilder();
        $qb->table('laporan_hama lh')
           ->select([
               'lh.*',
               'u.nama_lengkap as pelapor',
               'mo.nama_opt'
           ])
           ->leftJoin('users u', 'lh.user_id = u.id')
           ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id');

        // Apply filters safely
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $qb->where('lh.lokasi', $searchTerm, 'LIKE');
        }

        if (!empty($filters['status'])) {
            $qb->where('lh.status', $filters['status']);
        }

        if (!empty($filters['kabupaten_id'])) {
            $qb->where('lh.kabupaten_id', $filters['kabupaten_id']);
        }

        if (!empty($filters['kecamatan_id'])) {
            $qb->where('lh.kecamatan_id', $filters['kecamatan_id']);
        }

        if (!empty($filters['desa_id'])) {
            $qb->where('lh.desa_id', $filters['desa_id']);
        }

        if (!empty($filters['master_opt_id'])) {
            $qb->where('lh.master_opt_id', $filters['master_opt_id']);
        }

        $offset = ($page - 1) * $limit;
        $qb->orderBy('lh.created_at', 'DESC')
           ->limit($limit)
           ->offset($offset);

        return $qb->get();
    }

    /**
     * Get reports by status using QueryBuilder
     */
    public function getByStatus(string $status): array {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama lh')
                  ->select([
                      'lh.*',
                      'u.nama_lengkap as pelapor_nama',
                      'u.username as pelapor_username',
                      'u.role as pelapor_role',
                      'mo.nama_opt',
                      'mo.jenis',
                      'mo.etl_acuan'
                  ])
                  ->leftJoin('users u', 'lh.user_id = u.id')
                  ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id')
                  ->where('lh.status', $status)
                  ->orderBy('lh.created_at', 'DESC')
                  ->get();
    }

    /**
     * Get reports by status and user using QueryBuilder
     */
    public function getByStatusAndUser(string $status, int $userId): array {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama lh')
                  ->select([
                      'lh.*',
                      'u.nama_lengkap as pelapor_nama',
                      'u.username as pelapor_username',
                      'u.role as pelapor_role',
                      'mo.nama_opt',
                      'mo.jenis',
                      'mo.etl_acuan'
                  ])
                  ->leftJoin('users u', 'lh.user_id = u.id')
                  ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id')
                  ->where('lh.status', $status)
                  ->where('lh.user_id', $userId)
                  ->orderBy('lh.created_at', 'DESC')
                  ->get();
    }

    /**
     * Get all reports with details using QueryBuilder
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getAllWithDetails(?int $userId = null): array {
        $qb = new QueryBuilder();
        $qb->table('laporan_hama lh')
           ->select([
               'lh.*',
               'u.nama_lengkap as pelapor_nama',
               'u.username as pelapor_username',
               'u.role as pelapor_role',
               'mo.nama_opt',
               'mo.jenis',
               'mo.etl_acuan',
               'kab.nama_kabupaten',
               'kec.nama_kecamatan',
               'des.nama_desa'
           ])
           ->leftJoin('users u', 'lh.user_id = u.id')
           ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id')
           ->leftJoin('master_kabupaten kab', 'lh.kabupaten_id = kab.id')
           ->leftJoin('master_kecamatan kec', 'lh.kecamatan_id = kec.id')
           ->leftJoin('master_desa des', 'lh.desa_id = des.id');
        
        // Filter by user if provided
        if ($userId !== null) {
            $qb->where('lh.user_id', $userId);
        }
        
        return $qb->orderBy('lh.created_at', 'DESC')->get();
    }

    /**
     * Get all reports with details by user using QueryBuilder
     */
    public function getAllWithDetailsByUser(int $userId): array {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama lh')
                  ->select([
                      'lh.*',
                      'u.nama_lengkap as pelapor_nama',
                      'u.username as pelapor_username',
                      'u.role as pelapor_role',
                      'mo.nama_opt',
                      'mo.jenis',
                      'mo.etl_acuan'
                  ])
                  ->leftJoin('users u', 'lh.user_id = u.id')
                  ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id')
                  ->where('lh.user_id', $userId)
                  ->orderBy('lh.created_at', 'DESC')
                  ->get();
    }

    /**
     * Get report count by status using QueryBuilder
     */
    public function getCountByStatus(string $status): int {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama')
                  ->where('status', $status)
                  ->count();
    }

    /**
     * Get report count by status and user using QueryBuilder
     */
    public function getCountByStatusAndUser(string $status, int $userId): int {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama')
                  ->where('status', $status)
                  ->where('user_id', $userId)
                  ->count();
    }

    /**
     * Get total count using QueryBuilder
     */
    public function count(): int {
        $qb = new QueryBuilder();
        return $qb->table('laporan_hama')->count();
    }

    /**
     * Get top pests statistics
     */
    public function getTopPests(int $limit = 10, ?int $userId = null): array {
        try {
            $sql = "
                SELECT
                    mo.nama_opt,
                    mo.jenis,
                    COUNT(lh.id) as total_laporan,
                    AVG(lh.populasi) as avg_populasi,
                    SUM(lh.luas_serangan) as total_luas,
                    SUM(CASE WHEN lh.tingkat_keparahan = 'Berat' THEN 1 ELSE 0 END) as berat,
                    SUM(CASE WHEN lh.tingkat_keparahan = 'Sedang' THEN 1 ELSE 0 END) as sedang,
                    SUM(CASE WHEN lh.tingkat_keparahan = 'Ringan' THEN 1 ELSE 0 END) as ringan
                FROM laporan_hama lh
                LEFT JOIN master_opt mo ON lh.master_opt_id = mo.id
                WHERE lh.status = 'Diverifikasi'
                AND mo.nama_opt IS NOT NULL";
            
            if ($userId !== null) {
                $sql .= " AND lh.user_id = :user_id";
            }
            
            $sql .= "
                GROUP BY mo.id, mo.nama_opt, mo.jenis
                ORDER BY total_laporan DESC
                LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            if ($userId !== null) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Data validation
            foreach ($results as &$row) {
                $row['total_laporan'] = (int) $row['total_laporan'];
                $row['avg_populasi'] = round((float) $row['avg_populasi'], 2);
                $row['total_luas'] = round((float) $row['total_luas'], 2);
                $row['berat'] = (int) $row['berat'];
                $row['sedang'] = (int) $row['sedang'];
                $row['ringan'] = (int) $row['ringan'];
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error in getTopPests: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get severity distribution statistics
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getSeverityDistribution(?int $userId = null): array {
        try {
            $sql = "
                SELECT
                    tingkat_keparahan,
                    COUNT(*) as total,
                    SUM(luas_serangan) as total_luas,
                    AVG(populasi) as avg_populasi
                FROM laporan_hama
                WHERE status = 'Diverifikasi'
                AND tingkat_keparahan IS NOT NULL";
            
            if ($userId !== null) {
                $sql .= " AND user_id = :user_id";
            }
            
            $sql .= "
                GROUP BY tingkat_keparahan
                ORDER BY FIELD(tingkat_keparahan, 'Ringan', 'Sedang', 'Berat')";
            
            $stmt = $this->db->prepare($sql);
            if ($userId !== null) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure all severity levels exist
            $severityLevels = ['Ringan', 'Sedang', 'Berat'];
            $distribution = [];
            
            foreach ($severityLevels as $level) {
                $found = false;
                foreach ($results as $row) {
                    if ($row['tingkat_keparahan'] === $level) {
                        $distribution[] = [
                            'tingkat_keparahan' => $level,
                            'total' => (int) $row['total'],
                            'total_luas' => round((float) $row['total_luas'], 2),
                            'avg_populasi' => round((float) $row['avg_populasi'], 2)
                        ];
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $distribution[] = [
                        'tingkat_keparahan' => $level,
                        'total' => 0,
                        'total_luas' => 0,
                        'avg_populasi' => 0
                    ];
                }
            }
            
            return $distribution;
            
        } catch (PDOException $e) {
            error_log("Error in getSeverityDistribution: " . $e->getMessage());
            return [
                ['tingkat_keparahan' => 'Ringan', 'total' => 0, 'total_luas' => 0, 'avg_populasi' => 0],
                ['tingkat_keparahan' => 'Sedang', 'total' => 0, 'total_luas' => 0, 'avg_populasi' => 0],
                ['tingkat_keparahan' => 'Berat', 'total' => 0, 'total_luas' => 0, 'avg_populasi' => 0]
            ];
        }
    }
    
    /**
     * Get area statistics by month
     * @param int $year Year to get statistics for
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getAreaStatsByMonth(int $year, ?int $userId = null): array {
        try {
            $sql = "
                SELECT
                    MONTH(tanggal) as bulan,
                    SUM(luas_serangan) as total_luas,
                    AVG(luas_serangan) as avg_luas,
                    COUNT(*) as jumlah_laporan
                FROM laporan_hama
                WHERE YEAR(tanggal) = :year
                AND status = 'Diverifikasi'
                AND luas_serangan > 0";
            
            if ($userId !== null) {
                $sql .= " AND user_id = :user_id";
            }
            
            $sql .= "
                GROUP BY MONTH(tanggal)
                ORDER BY bulan";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            if ($userId !== null) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Initialize all 12 months
            $stats = [];
            for ($i = 1; $i <= 12; $i++) {
                $stats[$i] = [
                    'bulan' => $i,
                    'total_luas' => 0,
                    'avg_luas' => 0,
                    'jumlah_laporan' => 0
                ];
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $month = (int) $row['bulan'];
                $stats[$month] = [
                    'bulan' => $month,
                    'total_luas' => round((float) $row['total_luas'], 2),
                    'avg_luas' => round((float) $row['avg_luas'], 2),
                    'jumlah_laporan' => (int) $row['jumlah_laporan']
                ];
            }
            
            return array_values($stats);
            
        } catch (PDOException $e) {
            error_log("Error in getAreaStatsByMonth: " . $e->getMessage());
            return [];
        }
    }



    /**
     * Verify report using QueryBuilder
     */
    public function verify(int $id, int $userId, string $status, string $catatan = ''): int {
        $data = [
            'status' => $status,
            'verified_by' => $userId,
            'verified_at' => date('Y-m-d H:i:s'),
            'catatan_verifikasi' => $catatan
        ];

        $qb = new QueryBuilder();
        return $qb->table('laporan_hama')
                  ->where('id', $id)
                  ->update($data);
    }

    /**
     * Get reports for DataTables with pagination
     */
    public function getForDataTable(array $params = []): array {
        $qb = new QueryBuilder();
        $qb->table('laporan_hama lh')
           ->select([
               'lh.id',
               'lh.tanggal',
               'lh.lokasi',
               'mo.nama_opt',
               'lh.tingkat_keparahan',
               'lh.status',
               'u.nama_lengkap as pelapor',
               'lh.created_at'
           ])
           ->leftJoin('users u', 'lh.user_id = u.id')
           ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id');

        // Search functionality
        if (!empty($params['search'])) {
            $searchTerm = '%' . $params['search'] . '%';
            $qb->having("lh.lokasi LIKE ? OR mo.nama_opt LIKE ? OR u.nama_lengkap LIKE ?", [
                $searchTerm, $searchTerm, $searchTerm
            ]);
        }

        // Status filter
        if (!empty($params['status'])) {
            $qb->where('lh.status', $params['status']);
        }

        // Ordering
        $orderColumn = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'desc';

        $columns = ['lh.id', 'lh.tanggal', 'lh.lokasi', 'mo.nama_opt', 'lh.tingkat_keparahan', 'lh.status', 'u.nama_lengkap'];
        if (isset($columns[$orderColumn])) {
            $qb->orderBy($columns[$orderColumn], $orderDir);
        }

        // Pagination
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);

        $qb->limit($length)->offset($start);

        return $qb->get();
    }

    /**
     * Get filtered count for DataTables
     */
    public function getFilteredCount(array $params = []): int {
        $qb = new QueryBuilder();
        $qb->table('laporan_hama lh')
           ->leftJoin('users u', 'lh.user_id = u.id')
           ->leftJoin('master_opt mo', 'lh.master_opt_id = mo.id');

        // Apply same filters as getForDataTable
        if (!empty($params['search'])) {
            $searchTerm = '%' . $params['search'] . '%';
            $qb->having("lh.lokasi LIKE ? OR mo.nama_opt LIKE ? OR u.nama_lengkap LIKE ?", [
                $searchTerm, $searchTerm, $searchTerm
            ]);
        }

        if (!empty($params['status'])) {
            $qb->where('lh.status', $params['status']);
        }

        return $qb->count();
    }

    /**
     * Get dashboard statistics
     * Returns statistics with correct keys expected by dashboard view
     */
    /**
     * Get dashboard statistics
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getDashboardStats(?int $userId = null): array {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_laporan,
                    SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as pending_verifikasi,
                    SUM(CASE WHEN status = 'Diverifikasi' THEN 1 ELSE 0 END) as terverifikasi,
                    SUM(CASE WHEN status = 'Draf' THEN 1 ELSE 0 END) as draf,
                    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
                    SUM(CASE WHEN tingkat_keparahan = 'Berat' THEN 1 ELSE 0 END) as keparahan_berat,
                    SUM(CASE WHEN status = 'Diverifikasi' THEN luas_serangan ELSE 0 END) as total_luas,
                    SUM(CASE WHEN status = 'Diverifikasi' THEN populasi ELSE 0 END) as total_populasi
                FROM laporan_hama";
            
            if ($userId !== null) {
                $sql .= " WHERE user_id = :user_id";
            }
            
            $stmt = $this->db->prepare($sql);
            if ($userId !== null) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return stats with correct keys for dashboard view
            return [
                // Primary keys expected by dashboard view
                'total_laporan' => (int) ($result['total_laporan'] ?? 0),
                'pending_verifikasi' => (int) ($result['pending_verifikasi'] ?? 0),
                'terverifikasi' => (int) ($result['terverifikasi'] ?? 0),
                'keparahan_berat' => (int) ($result['keparahan_berat'] ?? 0),
                'draf' => (int) ($result['draf'] ?? 0),
                'ditolak' => (int) ($result['ditolak'] ?? 0),
                'total_luas' => (float) ($result['total_luas'] ?? 0),
                'total_populasi' => (int) ($result['total_populasi'] ?? 0),
                
                // Backward compatibility with old keys
                'total_reports' => (int) ($result['total_laporan'] ?? 0),
                'verified_reports' => (int) ($result['terverifikasi'] ?? 0),
                'pending_reports' => (int) ($result['pending_verifikasi'] ?? 0),
                'draft_reports' => (int) ($result['draf'] ?? 0),
                'total_area_affected' => (float) ($result['total_luas'] ?? 0),
                'total_population' => (int) ($result['total_populasi'] ?? 0)
            ];
            
        } catch (PDOException $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            
            // Return default values on error
            return [
                'total_laporan' => 0,
                'pending_verifikasi' => 0,
                'terverifikasi' => 0,
                'keparahan_berat' => 0,
                'draf' => 0,
                'ditolak' => 0,
                'total_luas' => 0,
                'total_populasi' => 0,
                // Backward compatibility
                'total_reports' => 0,
                'verified_reports' => 0,
                'pending_reports' => 0,
                'draft_reports' => 0,
                'total_area_affected' => 0,
                'total_population' => 0
            ];
        }
    }

    /**
     * Get monthly statistics for a given year
     * @param int $year Year to get statistics for
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getMonthlyStats(int $year, ?int $userId = null): array {
        try {
            $sql = "
                SELECT
                    MONTH(tanggal) as bulan,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Diverifikasi' THEN 1 ELSE 0 END) as terverifikasi,
                    SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as pending,
                    SUM(luas_serangan) as total_luas
                FROM laporan_hama
                WHERE YEAR(tanggal) = :year";
            
            if ($userId !== null) {
                $sql .= " AND user_id = :user_id";
            }
            
            $sql .= "
                GROUP BY MONTH(tanggal)
                ORDER BY bulan";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            if ($userId !== null) {
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize array with all months
            $stats = [];
            for ($i = 1; $i <= 12; $i++) {
                $stats[$i] = [
                    'bulan' => $i,
                    'total' => 0,
                    'terverifikasi' => 0,
                    'pending' => 0,
                    'total_luas' => 0
                ];
            }

            // Fill in actual data with validation
            foreach ($monthlyData as $data) {
                $month = (int) $data['bulan'];
                if ($month >= 1 && $month <= 12) {
                    $stats[$month] = [
                        'bulan' => $month,
                        'total' => (int) $data['total'],
                        'terverifikasi' => (int) $data['terverifikasi'],
                        'pending' => (int) $data['pending'],
                        'total_luas' => round((float) $data['total_luas'], 2)
                    ];
                }
            }

            return array_values($stats);
            
        } catch (PDOException $e) {
            error_log("Error in getMonthlyStats: " . $e->getMessage());
            
            // Return empty structure for all 12 months
            $stats = [];
            for ($i = 1; $i <= 12; $i++) {
                $stats[] = [
                    'bulan' => $i,
                    'total' => 0,
                    'terverifikasi' => 0,
                    'pending' => 0,
                    'total_luas' => 0
                ];
            }
            return $stats;
        }
    }

    /**
     * Get map data for pest distribution
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getMapData(?int $userId = null): array {
        $sql = "
            SELECT
                lh.id,
                lh.tanggal,
                lh.lokasi,
                lh.latitude,
                lh.longitude,
                lh.tingkat_keparahan,
                lh.populasi,
                lh.luas_serangan,
                mo.nama_opt,
                mo.jenis,
                u.nama_lengkap as pelapor
            FROM laporan_hama lh
            LEFT JOIN master_opt mo ON lh.master_opt_id = mo.id
            LEFT JOIN users u ON lh.user_id = u.id
            WHERE lh.status = 'Diverifikasi'
            AND lh.latitude IS NOT NULL
            AND lh.longitude IS NOT NULL";
        
        if ($userId !== null) {
            $sql .= " AND lh.user_id = :user_id";
        }
        
        $sql .= " ORDER BY lh.tanggal DESC";
        
        $stmt = $this->db->prepare($sql);
        if ($userId !== null) {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top kecamatan statistics
     * Returns top 5 kecamatan by report count and by affected area
     * @param int $limit Number of top kecamatan to return
     * @param int|null $userId Optional user ID to filter reports by user
     */
    public function getTopKecamatan(int $limit = 5, ?int $userId = null): array {
        try {
            $result = [
                'by_count' => [],
                'by_area' => []
            ];
            
            // 1. Get Top by Count (Jumlah Laporan)
            $sqlCount = "
                SELECT 
                    mk.nama_kecamatan, 
                    COUNT(lh.id) as total_laporan
                FROM laporan_hama lh
                JOIN master_kecamatan mk ON lh.kecamatan_id = mk.id
                WHERE lh.status = 'Diverifikasi'";
            
            if ($userId !== null) {
                $sqlCount .= " AND lh.user_id = :user_id";
            }
            
            $sqlCount .= "
                GROUP BY mk.id, mk.nama_kecamatan
                ORDER BY total_laporan DESC
                LIMIT :limit";
            
            $stmtCount = $this->db->prepare($sqlCount);
            if ($userId !== null) {
                $stmtCount->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmtCount->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmtCount->execute();
            $result['by_count'] = $stmtCount->fetchAll(PDO::FETCH_ASSOC);

            // 2. Get Top by Area (Luas Serangan)
            $sqlArea = "
                SELECT 
                    mk.nama_kecamatan, 
                    SUM(lh.luas_serangan) as total_luas
                FROM laporan_hama lh
                JOIN master_kecamatan mk ON lh.kecamatan_id = mk.id
                WHERE lh.status = 'Diverifikasi'";
            
            if ($userId !== null) {
                $sqlArea .= " AND lh.user_id = :user_id";
            }
            
            $sqlArea .= "
                GROUP BY mk.id, mk.nama_kecamatan
                ORDER BY total_luas DESC
                LIMIT :limit";
            
            $stmtArea = $this->db->prepare($sqlArea);
            if ($userId !== null) {
                $stmtArea->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $stmtArea->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmtArea->execute();
            $result['by_area'] = $stmtArea->fetchAll(PDO::FETCH_ASSOC);

            // Format numbers
            foreach ($result['by_area'] as &$row) {
                $row['total_luas'] = round((float) $row['total_luas'], 2);
            }

            return $result;

        } catch (PDOException $e) {
            error_log("Error in getTopKecamatan: " . $e->getMessage());
            return [
                'by_count' => [],
                'by_area' => []
            ];
        }
    }
}
