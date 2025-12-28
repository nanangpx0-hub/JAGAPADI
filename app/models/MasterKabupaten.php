<?php
require_once ROOT_PATH . '/app/core/Cache.php';

class MasterKabupaten extends Model {
    protected $table = 'master_kabupaten';
    
    public function getAllOrdered() {
        // Sort by kode_kabupaten ascending for consistent ordering
        return Cache::remember('master_kabupaten_all_by_kode', function() {
            $stmt = $this->db->query("SELECT * FROM master_kabupaten WHERE deleted_at IS NULL ORDER BY kode_kabupaten ASC");
            return $stmt->fetchAll();
        }, 3600); // Cache for 1 hour
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find kabupaten by ID or by kode (supports multiple formats)
     * Supports: database ID, BPS kode (3509), short kode (09), or JT-format (JT-09)
     * @param mixed $idOrKode The identifier to search for
     * @return array|false The kabupaten record or false if not found
     */
    public function findByIdOrKode($idOrKode) {
        if (empty($idOrKode) && $idOrKode !== '0' && $idOrKode !== 0) {
            return false;
        }
        
        // Convert to string for consistent comparison
        $idOrKode = (string)$idOrKode;
        
        // Step 1: Try exact ID match (as string - for IDs like '09')
        $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$idOrKode]);
        $result = $stmt->fetch();
        if ($result) {
            return $result;
        }
        
        // Step 2: Try exact kode_kabupaten match (e.g., "3509")
        $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL");
        $stmt->execute([$idOrKode]);
        $result = $stmt->fetch();
        if ($result) {
            return $result;
        }
        
        // Step 3: Try BPS format conversion (e.g., "09" -> "3509", "9" -> "3509")
        if (is_numeric($idOrKode) && strlen(ltrim($idOrKode, '0')) <= 2) {
            // Convert to 2-digit padded then prepend province code
            $numericPart = (int)$idOrKode;
            $bpsKode = '35' . str_pad($numericPart, 2, '0', STR_PAD_LEFT);
            
            $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL");
            $stmt->execute([$bpsKode]);
            $result = $stmt->fetch();
            if ($result) {
                return $result;
            }
        }
        
        // Step 4: Try JT-format match (legacy format like "JT-09")
        $jtKode = $idOrKode;
        if (!str_starts_with(strtoupper($idOrKode), 'JT-') && is_numeric($idOrKode)) {
            $jtKode = 'JT-' . str_pad((int)$idOrKode, 2, '0', STR_PAD_LEFT);
            
            // Extract the numeric part and convert to BPS format
            $bpsKode = '35' . str_pad((int)$idOrKode, 2, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL");
            $stmt->execute([$bpsKode]);
            $result = $stmt->fetch();
            if ($result) {
                return $result;
            }
        }
        
        return false;
    }
    
    public function search($q, $limit = 50) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE nama_kabupaten LIKE ? AND deleted_at IS NULL ORDER BY nama_kabupaten LIMIT $limit");
        $stmt->execute(['%'.$q.'%']);
        return $stmt->fetchAll();
    }

    public function findByName($nama) {
        $stmt = $this->db->prepare("SELECT * FROM master_kabupaten WHERE nama_kabupaten = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$nama]);
        return $stmt->fetch();
    }
    
    // Admin CRUD Methods
    public function getAllWithPagination($search = '', $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM master_kabupaten WHERE deleted_at IS NULL";
        $params = [];
        
        if ($search) {
            $sql .= " AND (nama_kabupaten LIKE ? OR kode_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " ORDER BY nama_kabupaten LIMIT $limit OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($search = '') {
        $sql = "SELECT COUNT(*) as total FROM master_kabupaten WHERE deleted_at IS NULL";
        $params = [];
        
        if ($search) {
            $sql .= " AND (nama_kabupaten LIKE ? OR kode_kabupaten LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO master_kabupaten (nama_kabupaten, kode_kabupaten, created_by) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['nama_kabupaten'], $data['kode_kabupaten'], $data['created_by']]);
        Cache::delete('master_kabupaten_all');
        Cache::delete('master_kabupaten_dropdown');
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE master_kabupaten SET nama_kabupaten = ?, kode_kabupaten = ?, updated_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['nama_kabupaten'], $data['kode_kabupaten'], $data['updated_by'], $id]);
        Cache::delete('master_kabupaten_all');
        Cache::delete('master_kabupaten_dropdown');
        return $stmt->rowCount();
    }
    
    public function softDelete($id, $userId) {
        $sql = "UPDATE master_kabupaten SET deleted_at = NOW(), deleted_by = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $id]);
        Cache::delete('master_kabupaten_all');
        Cache::delete('master_kabupaten_dropdown');
        return $stmt->rowCount();
    }
    
    public function checkKodeExists($kode, $excludeId = null) {
        $sql = "SELECT COUNT(*) as c FROM master_kabupaten WHERE kode_kabupaten = ? AND deleted_at IS NULL";
        $params = [$kode];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    public function getAllForDropdown() {
        return Cache::remember('master_kabupaten_dropdown', function() {
            // Only show kabupaten that exist in kabupaten table (JT-01 to JT-38)
            // Order by kode_kabupaten to match kabupaten page order
            $stmt = $this->db->query("
                SELECT m.id, m.nama_kabupaten, m.kode_kabupaten 
                FROM master_kabupaten m
                INNER JOIN kabupaten k ON k.kode_kabupaten = m.kode_kabupaten
                WHERE m.deleted_at IS NULL 
                ORDER BY m.kode_kabupaten ASC
            ");
            return $stmt->fetchAll();
        }, 3600); // Cache for 1 hour
    }
}
