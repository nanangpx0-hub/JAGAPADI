<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/Wilayah.php';

class WilayahController extends BaseApiController {
    
    private $wilayahModel;
    
    public function __construct() {
        $this->wilayahModel = new Wilayah();
    }
    
    /**
     * Get all kabupaten
     * GET /api/wilayah/kabupaten
     */
    public function getKabupaten() {
        try {
            $kabupaten = $this->wilayahModel->getAllKabupaten();
            $this->sendResponse($kabupaten, 'Kabupaten data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve kabupaten data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get kecamatan by kabupaten ID
     * GET /api/wilayah/kecamatan/{kabupaten_id}
     */
    public function getKecamatan($kabupatenId) {
        try {
            if (!$kabupatenId || !is_numeric($kabupatenId)) {
                $this->sendError('Invalid kabupaten ID', 400);
            }
            
            $kecamatan = $this->wilayahModel->getKecamatanByKabupaten($kabupatenId);
            $this->sendResponse($kecamatan, 'Kecamatan data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve kecamatan data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get desa by kecamatan ID
     * GET /api/wilayah/desa/{kecamatan_id}
     */
    public function getDesa($kecamatanId) {
        try {
            if (!$kecamatanId || !is_numeric($kecamatanId)) {
                $this->sendError('Invalid kecamatan ID', 400);
            }
            
            $desa = $this->wilayahModel->getDesaByKecamatan($kecamatanId);
            $this->sendResponse($desa, 'Desa data retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve desa data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get complete wilayah hierarchy
     * GET /api/wilayah/hierarchy
     */
    public function getHierarchy() {
        try {
            $hierarchy = $this->wilayahModel->getWilayahHierarchy();
            $this->sendResponse($hierarchy, 'Wilayah hierarchy retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve wilayah hierarchy: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Search wilayah by name
     * GET /api/wilayah/search?q={query}&type={type}
     */
    public function search() {
        try {
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all'; // kabupaten, kecamatan, desa, or all
            
            if (empty($query)) {
                $this->sendError('Search query is required', 400);
            }
            
            if (strlen($query) < 2) {
                $this->sendError('Search query must be at least 2 characters', 400);
            }
            
            $results = $this->wilayahModel->searchWilayah($query, $type);
            $this->sendResponse($results, 'Search results retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to search wilayah: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get wilayah statistics
     * GET /api/wilayah/stats
     */
    public function getStats() {
        try {
            $stats = $this->wilayahModel->getWilayahStatistics();
            $this->sendResponse($stats, 'Wilayah statistics retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve wilayah statistics: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get wilayah by coordinates (for mobile GPS)
     * GET /api/wilayah/by-coordinates?lat={lat}&lng={lng}&radius={radius}
     */
    public function getByCoordinates() {
        try {
            $lat = $_GET['lat'] ?? null;
            $lng = $_GET['lng'] ?? null;
            $radius = $_GET['radius'] ?? 5; // Default 5km radius
            
            if (!$lat || !$lng) {
                $this->sendError('Latitude and longitude are required', 400);
            }
            
            if (!is_numeric($lat) || !is_numeric($lng)) {
                $this->sendError('Invalid coordinates format', 400);
            }
            
            // Validate coordinate ranges
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                $this->sendError('Coordinates out of valid range', 400);
            }
            
            $wilayah = $this->wilayahModel->getWilayahByCoordinates($lat, $lng, $radius);
            $this->sendResponse($wilayah, 'Wilayah by coordinates retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve wilayah by coordinates: ' . $e->getMessage(), 500);
        }
    }
}