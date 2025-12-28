<?php
/**
 * Tag Model
 * 
 * Handles CRUD operations for tags and tag-laporan relationships
 */
class Tag extends Model {
    protected $table = 'tags';
    
    /**
     * Search tags by name (for autocomplete)
     * 
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return array Array of matching tags
     */
    public function search(string $query, int $limit = 10): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nama_tag, deskripsi, warna, usage_count
                FROM tags
                WHERE nama_tag LIKE :query
                ORDER BY usage_count DESC, nama_tag ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Tag::search: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all popular tags
     * 
     * @param int $limit Maximum number of results
     * @return array Array of popular tags
     */
    public function getPopular(int $limit = 20): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nama_tag, deskripsi, warna, usage_count
                FROM tags
                WHERE usage_count > 0
                ORDER BY usage_count DESC, nama_tag ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Tag::getPopular: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tags for a specific laporan
     * 
     * @param int $laporanId Laporan ID
     * @return array Array of tags
     */
    public function getByLaporan(int $laporanId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT t.id, t.nama_tag, t.deskripsi, t.warna
                FROM tags t
                INNER JOIN laporan_hama_tags lht ON t.id = lht.tag_id
                WHERE lht.laporan_hama_id = :laporan_id
                ORDER BY t.nama_tag ASC
            ");
            $stmt->bindValue(':laporan_id', $laporanId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in Tag::getByLaporan: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add tag to laporan (many-to-many relationship)
     * 
     * @param int $laporanId Laporan ID
     * @param int $tagId Tag ID
     * @return bool Success status
     */
    public function addToLaporan(int $laporanId, int $tagId): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO laporan_hama_tags (laporan_hama_id, tag_id)
                VALUES (:laporan_id, :tag_id)
            ");
            $stmt->bindValue(':laporan_id', $laporanId, PDO::PARAM_INT);
            $stmt->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Tag::addToLaporan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove tag from laporan
     * 
     * @param int $laporanId Laporan ID
     * @param int $tagId Tag ID
     * @return bool Success status
     */
    public function removeFromLaporan(int $laporanId, int $tagId): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM laporan_hama_tags
                WHERE laporan_hama_id = :laporan_id AND tag_id = :tag_id
            ");
            $stmt->bindValue(':laporan_id', $laporanId, PDO::PARAM_INT);
            $stmt->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in Tag::removeFromLaporan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set tags for laporan (replace existing tags)
     * 
     * @param int $laporanId Laporan ID
     * @param array $tagIds Array of tag IDs
     * @return bool Success status
     */
    public function setForLaporan(int $laporanId, array $tagIds): bool {
        try {
            $this->db->beginTransaction();
            
            // Remove existing tags
            $stmtDelete = $this->db->prepare("
                DELETE FROM laporan_hama_tags WHERE laporan_hama_id = :laporan_id
            ");
            $stmtDelete->bindValue(':laporan_id', $laporanId, PDO::PARAM_INT);
            $stmtDelete->execute();
            
            // Add new tags
            if (!empty($tagIds)) {
                $stmtInsert = $this->db->prepare("
                    INSERT INTO laporan_hama_tags (laporan_hama_id, tag_id)
                    VALUES (:laporan_id, :tag_id)
                ");
                foreach ($tagIds as $tagId) {
                    $stmtInsert->bindValue(':laporan_id', $laporanId, PDO::PARAM_INT);
                    $stmtInsert->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
                    $stmtInsert->execute();
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error in Tag::setForLaporan: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create tag if not exists (find or create)
     * 
     * @param string $namaTag Tag name
     * @param string $warna Tag color (hex)
     * @param string $deskripsi Tag description
     * @return int Tag ID
     */
    public function findOrCreate(string $namaTag, string $warna = '#007bff', string $deskripsi = ''): int {
        try {
            // Normalize tag name (lowercase, trim)
            $namaTag = strtolower(trim($namaTag));
            
            // Check if tag exists
            $stmt = $this->db->prepare("SELECT id FROM tags WHERE nama_tag = :nama_tag");
            $stmt->bindValue(':nama_tag', $namaTag, PDO::PARAM_STR);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                return (int)$existing['id'];
            }
            
            // Create new tag
            $stmt = $this->db->prepare("
                INSERT INTO tags (nama_tag, warna, deskripsi)
                VALUES (:nama_tag, :warna, :deskripsi)
            ");
            $stmt->bindValue(':nama_tag', $namaTag, PDO::PARAM_STR);
            $stmt->bindValue(':warna', $warna, PDO::PARAM_STR);
            $stmt->bindValue(':deskripsi', $deskripsi, PDO::PARAM_STR);
            $stmt->execute();
            
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in Tag::findOrCreate: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Auto-generate tags based on laporan content
     * Uses keyword matching and simple NLP techniques
     * 
     * @param array $laporanData Laporan data (catatan, tingkat_keparahan, populasi, luas_serangan, etc.)
     * @return array Array of suggested tag IDs with confidence scores
     */
    public function generateAutoTags(array $laporanData): array {
        $suggestions = [];
        
        // Get all tags for matching
        $allTags = $this->all();
        
        // Keyword mapping for auto-tagging
        $keywordMap = [
            'serius' => ['berat', 'parah', 'kritis', 'mengkhawatirkan', 'bahaya'],
            'urgent' => ['segera', 'darurat', 'cepat', 'mendesak', 'prioritas'],
            'epidemi' => ['wabah', 'menyebar', 'epidemi', 'outbreak', 'meluas'],
            'musiman' => ['musim', 'rutin', 'periodik', 'setiap'],
            'baru' => ['baru', 'pertama', 'belum pernah', 'awal'],
            'berulang' => ['berulang', 'terus', 'kembali', 'lagi', 'recurring'],
            'luas' => ['luas', 'besar', 'banyak', 'extensive', 'massive'],
            'terkendali' => ['terkendali', 'sembuh', 'membaik', 'stabil', 'kontrol']
        ];
        
        // Combine text content for analysis
        $content = strtolower(
            ($laporanData['catatan'] ?? '') . ' ' .
            ($laporanData['tingkat_keparahan'] ?? '') . ' ' .
            ($laporanData['nama_opt'] ?? '')
        );
        
        // Check keyword matches
        foreach ($keywordMap as $tagName => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    // Find tag ID
                    foreach ($allTags as $tag) {
                        if (strtolower($tag['nama_tag']) === $tagName) {
                            $suggestions[] = [
                                'tag_id' => (int)$tag['id'],
                                'nama_tag' => $tag['nama_tag'],
                                'warna' => $tag['warna'] ?? '#007bff',
                                'confidence' => 0.8,
                                'reason' => "Kata kunci terdeteksi: '{$keyword}'"
                            ];
                            break 2; // Break both loops
                        }
                    }
                }
            }
        }
        
        // Rule-based suggestions based on severity
        if (isset($laporanData['tingkat_keparahan'])) {
            if ($laporanData['tingkat_keparahan'] === 'Berat') {
                foreach ($allTags as $tag) {
                    if (strtolower($tag['nama_tag']) === 'serius') {
                        $suggestions[] = [
                            'tag_id' => (int)$tag['id'],
                            'nama_tag' => $tag['nama_tag'],
                            'warna' => $tag['warna'] ?? '#dc3545',
                            'confidence' => 0.9,
                            'reason' => 'Tingkat keparahan: Berat'
                        ];
                        break;
                    }
                }
            }
        }
        
        // Rule-based suggestions based on luas serangan
        if (isset($laporanData['luas_serangan']) && $laporanData['luas_serangan'] > 10) {
            foreach ($allTags as $tag) {
                if (strtolower($tag['nama_tag']) === 'luas') {
                    $suggestions[] = [
                        'tag_id' => (int)$tag['id'],
                        'nama_tag' => $tag['nama_tag'],
                        'warna' => $tag['warna'] ?? '#17a2b8',
                        'confidence' => 0.7,
                        'reason' => 'Luas serangan > 10 Ha'
                    ];
                    break;
                }
            }
        }
        
        // Rule-based suggestions based on populasi
        if (isset($laporanData['populasi']) && $laporanData['populasi'] > 1000) {
            foreach ($allTags as $tag) {
                if (strtolower($tag['nama_tag']) === 'urgent') {
                    $suggestions[] = [
                        'tag_id' => (int)$tag['id'],
                        'nama_tag' => $tag['nama_tag'],
                        'warna' => $tag['warna'] ?? '#fd7e14',
                        'confidence' => 0.75,
                        'reason' => 'Populasi tinggi (>1000)'
                    ];
                    break;
                }
            }
        }
        
        // Remove duplicates (same tag_id)
        $uniqueSuggestions = [];
        $addedTagIds = [];
        foreach ($suggestions as $suggestion) {
            if (!in_array($suggestion['tag_id'], $addedTagIds)) {
                $uniqueSuggestions[] = $suggestion;
                $addedTagIds[] = $suggestion['tag_id'];
            }
        }
        
        // Sort by confidence descending
        usort($uniqueSuggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        return $uniqueSuggestions;
    }
}

