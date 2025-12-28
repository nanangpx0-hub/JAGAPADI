<?php
/**
 * Audit Log Wilayah Model
 * Tracks all CRUD operations on wilayah tables
 */

class AuditLogWilayah extends Model {
    protected $table = 'audit_log_wilayah';
    
    public function log($data) {
        $sql = "INSERT INTO audit_log_wilayah 
                (user_id, table_name, record_id, action, old_values, new_values, ip_address, user_agent) 
                VALUES (:user_id, :table_name, :record_id, :action, :old_values, :new_values, :ip_address, :user_agent)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'table_name' => $data['table_name'],
            'record_id' => $data['record_id'],
            'action' => $data['action'],
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function getByRecord($tableName, $recordId, $limit = 50) {
        $sql = "SELECT al.*, u.nama_lengkap, u.username 
                FROM audit_log_wilayah al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.table_name = ? AND al.record_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tableName, $recordId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getRecent($limit = 100) {
        $sql = "SELECT al.*, u.nama_lengkap, u.username 
                FROM audit_log_wilayah al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getByUser($userId, $limit = 50) {
        $sql = "SELECT al.*, u.nama_lengkap, u.username 
                FROM audit_log_wilayah al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.user_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function searchLogs($filters = [], $limit = 50, $offset = 0) {
        $where = [];
        $params = [];
        
        if (!empty($filters['table_name'])) {
            $where[] = "al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT al.*, u.nama_lengkap, u.username 
                FROM audit_log_wilayah al
                LEFT JOIN users u ON al.user_id = u.id
                $whereClause
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function countLogs($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['table_name'])) {
            $where[] = "table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as total FROM audit_log_wilayah $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] ?? 0;
    }
}
