<?php
class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function all() {
        // Sanitize table name to prevent SQL injection
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
        if (empty($table)) {
            throw new RuntimeException('Invalid table name');
        }
        $stmt = $this->db->prepare("SELECT * FROM `{$table}`");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function where($conditions = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= implode(" AND ", $whereClause);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        // Sanitize table name to prevent SQL injection
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
        if (empty($table)) {
            throw new RuntimeException('Invalid table name');
        }
        
        // Sanitize column names
        $sanitizedColumns = [];
        foreach (array_keys($data) as $column) {
            $sanitizedColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
            if (!empty($sanitizedColumn)) {
                $sanitizedColumns[] = "`{$sanitizedColumn}`";
            }
        }
        
        if (empty($sanitizedColumns)) {
            throw new RuntimeException('No valid columns to insert');
        }
        
        $columns = implode(', ', $sanitizedColumns);
        $placeholders = implode(', ', array_fill(0, count($sanitizedColumns), '?'));
        
        $sql = "INSERT INTO `{$table}` ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            $setClause[] = "$key = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
