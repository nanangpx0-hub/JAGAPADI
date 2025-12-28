<?php
/**
 * Secure Query Builder for preventing SQL injection
 */
class QueryBuilder {
    private $pdo;
    private $table;
    private $columns = ['*'];
    private $whereClauses = [];
    private $whereParams = [];
    private $joins = [];
    private $orderBy = [];
    private $groupBy = [];
    private $havingClauses = [];
    private $havingParams = [];
    private $limit;
    private $offset;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    /**
     * Set the table for the query
     */
    public function table(string $table): self {
        // Don't sanitize the entire table string if it contains aliases
        // Only sanitize if it's a simple table name without spaces
        if (strpos($table, ' ') === false) {
            $this->table = $this->sanitizeIdentifier($table);
        } else {
            // For table with alias, split and sanitize each part
            $parts = explode(' ', $table);
            $sanitizedParts = [];
            foreach ($parts as $part) {
                if (!empty($part)) {
                    $sanitizedParts[] = $this->sanitizeIdentifier($part);
                }
            }
            $this->table = implode(' ', $sanitizedParts);
        }
        return $this;
    }

    /**
     * Select specific columns
     */
    public function select(array $columns): self {
        $this->columns = $columns; // Don't sanitize select columns to preserve aliases and wildcards
        return $this;
    }

    /**
     * Add WHERE condition
     */
    public function where(string $column, $value, string $operator = '='): self {
        $column = $this->sanitizeIdentifier($column);
        $placeholder = $this->generatePlaceholder($column, $operator);

        if (in_array(strtoupper($operator), ['IN', 'NOT IN'])) {
            // Handle array values for IN/NOT IN
            if (!is_array($value)) {
                $value = [$value];
            }
            $placeholders = str_repeat('?,', count($value) - 1) . '?';
            $this->whereClauses[] = "{$column} {$operator} ({$placeholders})";
            $this->whereParams = array_merge($this->whereParams, $value);
        } elseif (in_array(strtoupper($operator), ['LIKE', 'NOT LIKE'])) {
            $this->whereClauses[] = "{$column} {$operator} ?";
            $this->whereParams[] = $value;
        } else {
            $this->whereClauses[] = "{$column} {$operator} ?";
            $this->whereParams[] = $value;
        }

        return $this;
    }

    /**
     * Add WHERE IN condition
     */
    public function whereIn(string $column, array $values): self {
        return $this->where($column, $values, 'IN');
    }

    /**
     * Add WHERE NOT IN condition
     */
    public function whereNotIn(string $column, array $values): self {
        return $this->where($column, $values, 'NOT IN');
    }

    /**
     * Add WHERE NULL condition
     */
    public function whereNull(string $column): self {
        $column = $this->sanitizeIdentifier($column);
        $this->whereClauses[] = "{$column} IS NULL";
        return $this;
    }

    /**
     * Add WHERE NOT NULL condition
     */
    public function whereNotNull(string $column): self {
        $column = $this->sanitizeIdentifier($column);
        $this->whereClauses[] = "{$column} IS NOT NULL";
        return $this;
    }

    /**
     * Add WHERE BETWEEN condition
     */
    public function whereBetween(string $column, $start, $end): self {
        $column = $this->sanitizeIdentifier($column);
        $this->whereClauses[] = "{$column} BETWEEN ? AND ?";
        $this->whereParams[] = $start;
        $this->whereParams[] = $end;
        return $this;
    }

    /**
     * Add JOIN clause
     */
    public function join(string $table, string $condition, string $type = 'INNER'): self {
        // Handle table names with aliases
        if (strpos($table, ' ') === false) {
            $table = $this->sanitizeIdentifier($table);
        } else {
            // For table with alias, split and sanitize each part
            $parts = explode(' ', $table);
            $sanitizedParts = [];
            foreach ($parts as $part) {
                if (!empty($part)) {
                    $sanitizedParts[] = $this->sanitizeIdentifier($part);
                }
            }
            $table = implode(' ', $sanitizedParts);
        }
        
        $validTypes = ['INNER', 'LEFT', 'RIGHT', 'FULL OUTER'];
        $type = strtoupper($type);

        if (!in_array($type, $validTypes)) {
            $type = 'INNER';
        }

        $this->joins[] = "{$type} JOIN {$table} ON {$condition}";
        return $this;
    }

    /**
     * Add LEFT JOIN
     */
    public function leftJoin(string $table, string $condition): self {
        return $this->join($table, $condition, 'LEFT');
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $column = $this->sanitizeIdentifier($column);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    /**
     * Add GROUP BY clause
     */
    public function groupBy(array $columns): self {
        $this->groupBy = array_map([$this, 'sanitizeIdentifier'], $columns);
        return $this;
    }

    /**
     * Add HAVING clause
     */
    public function having(string $condition, $params = []): self {
        $this->havingClauses[] = $condition;
        if (!is_array($params)) {
            $params = [$params];
        }
        $this->havingParams = array_merge($this->havingParams, $params);
        return $this;
    }

    /**
     * Set LIMIT
     */
    public function limit(int $limit): self {
        $this->limit = max(1, $limit);
        return $this;
    }

    /**
     * Set OFFSET
     */
    public function offset(int $offset): self {
        $this->offset = max(0, $offset);
        return $this;
    }

    /**
     * Execute SELECT query and return all results
     */
    public function get(): array {
        $sql = $this->buildSelectSql();
        $stmt = $this->pdo->prepare($sql);

        $params = array_merge($this->whereParams, $this->havingParams);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute SELECT query and return first result
     */
    public function first(): ?array {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Execute SELECT query and return single value
     */
    public function value(string $column) {
        $originalColumns = $this->columns;
        $this->columns = [$this->sanitizeIdentifier($column)];

        $result = $this->first();
        $this->columns = $originalColumns;

        return $result[$column] ?? null;
    }

    /**
     * Get count of records
     */
    public function count(): int {
        $originalColumns = $this->columns;
        $this->columns = ['COUNT(*) as count'];

        $result = $this->first();
        $this->columns = $originalColumns;

        return (int)($result['count'] ?? 0);
    }

    /**
     * Check if records exist
     */
    public function exists(): bool {
        return $this->count() > 0;
    }

    /**
     * Execute INSERT query
     */
    public function insert(array $data): int {
        $this->validateInsertData($data);

        $columns = array_keys($data);
        $sanitizedColumns = array_map([$this, 'sanitizeIdentifier'], $columns);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';

        $sql = "INSERT INTO {$this->table} (`" . implode('`, `', $sanitizedColumns) . "`) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);

        $values = array_values($data);
        $stmt->execute($values);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Execute UPDATE query
     */
    public function update(array $data): int {
        $this->validateUpdateData($data);

        if (empty($this->whereClauses)) {
            throw new RuntimeException('Update queries must have WHERE conditions');
        }

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $sanitizedColumn = $this->sanitizeIdentifier($column);
            $setParts[] = "`{$sanitizedColumn}` = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $this->whereClauses);
        $stmt = $this->pdo->prepare($sql);

        $params = array_merge(array_values($data), $this->whereParams);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Execute DELETE query
     */
    public function delete(): int {
        if (empty($this->whereClauses)) {
            throw new RuntimeException('Delete queries must have WHERE conditions');
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $this->whereClauses);
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($this->whereParams);

        return $stmt->rowCount();
    }

    /**
     * Get raw SQL string for debugging
     */
    public function toSql(): string {
        $sql = $this->buildSelectSql();
        $params = array_merge($this->whereParams, $this->havingParams);

        // Replace placeholders with actual values for debugging
        foreach ($params as $param) {
            if (is_string($param)) {
                $param = "'{$param}'";
            }
            $sql = preg_replace('/\?/', $param, $sql, 1);
        }

        return $sql;
    }

    /**
     * Build the SELECT SQL query
     */
    private function buildSelectSql(): string {
        if (!$this->table) {
            throw new RuntimeException('Table must be set before building query');
        }

        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $this->whereClauses);
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->havingClauses)) {
            $sql .= " HAVING " . implode(' AND ', $this->havingClauses);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    /**
     * Sanitize database identifier (table/column name)
     */
    private function sanitizeIdentifier(string $identifier): string {
        // Remove any potentially dangerous characters but allow letters, numbers, underscores, and dots
        $identifier = preg_replace('/[^a-zA-Z0-9_.]/', '', $identifier);
        return $identifier;
    }

    /**
     * Generate placeholder for parameterized queries
     */
    private function generatePlaceholder(string $column, string $operator): string {
        // This is a simplified placeholder generation
        // In a full implementation, you might want more sophisticated handling
        return '?';
    }

    /**
     * Validate data for INSERT
     */
    private function validateInsertData(array $data): void {
        if (empty($data)) {
            throw new InvalidArgumentException('Insert data cannot be empty');
        }

        // Basic validation - you might want to extend this
        foreach ($data as $key => $value) {
            if (!is_scalar($value) && !is_null($value)) {
                throw new InvalidArgumentException("Invalid data type for column '{$key}'");
            }
        }
    }

    /**
     * Validate data for UPDATE
     */
    private function validateUpdateData(array $data): void {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty');
        }

        // Ensure WHERE conditions exist for safety
        if (empty($this->whereClauses)) {
            throw new RuntimeException('Update operations require WHERE conditions');
        }
    }
}
