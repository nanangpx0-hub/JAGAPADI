<?php
class Kabupaten extends Model {
    protected $table = 'kabupaten';

    public function getAllWithPagination($search = '', $limit = 20, $offset = 0, $orderBy = 'kode_kabupaten', $orderDir = 'asc') {
        $sql = "SELECT k.*, m.id AS master_id, 1 as status FROM kabupaten k LEFT JOIN master_kabupaten m ON m.kode_kabupaten = k.kode_kabupaten WHERE 1";
        $params = [];
        if ($search) {
            $sql .= " AND (k.nama_kabupaten LIKE ? OR k.kode_kabupaten LIKE ? OR k.provinsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Validate and sanitize order parameters
        $allowedColumns = ['id', 'kode_kabupaten', 'nama_kabupaten', 'provinsi', 'master_id'];
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'kode_kabupaten';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql .= " ORDER BY ";
        if ($orderBy === 'master_id') {
            $sql .= "m.id $orderDir";
        } else {
            $sql .= "k.$orderBy $orderDir";
        }
        $sql .= " LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($search = '') {
        $sql = "SELECT COUNT(*) AS total FROM kabupaten WHERE 1";
        $params = [];
        if ($search) {
            $sql .= " AND (nama_kabupaten LIKE ? OR kode_kabupaten LIKE ? OR provinsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function checkKodeExists($kode, $excludeId = null) {
        $sql = "SELECT COUNT(*) as c FROM kabupaten WHERE kode_kabupaten = ?";
        $params = [$kode];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch()['c'] ?? 0) > 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO kabupaten (nama_kabupaten, kode_kabupaten, provinsi) VALUES (?, ?, 'Jawa Timur')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['nama_kabupaten'], $data['kode_kabupaten']]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE kabupaten SET nama_kabupaten = ?, kode_kabupaten = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['nama_kabupaten'], 
            $data['kode_kabupaten'], 
            $id
        ]);
        return $stmt->rowCount();
    }
    
    public function softDelete($id, $userId) {
        $sql = "DELETE FROM kabupaten WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM kabupaten WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
