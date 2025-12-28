<?php
/**
 * File-based Cache Class
 * Provides simple file-based caching for master data and frequently accessed data
 */
class Cache {
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour default
    
    /**
     * Initialize cache directory
     */
    public static function init() {
        self::$cacheDir = ROOT_PATH . '/storage/cache/';
        
        // Create cache directory if it doesn't exist
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cache file path
     */
    private static function getCachePath($key) {
        self::init();
        $hash = md5($key);
        return self::$cacheDir . $hash . '.cache';
    }
    
    /**
     * Get cached data
     * 
     * @param string $key Cache key
     * @return mixed|null Cached data or null if not found/expired
     */
    public static function get($key) {
        $file = self::getCachePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // Check if expired
        if (time() > $data['expires']) {
            self::delete($key);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cache data
     * 
     * @param string $key Cache key
     * @param mixed $value Data to cache
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @return bool Success status
     */
    public static function set($key, $value, $ttl = null) {
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $file = self::getCachePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Delete cache entry
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public static function delete($key) {
        $file = self::getCachePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     * 
     * @return int Number of files deleted
     */
    public static function clear() {
        self::init();
        $count = 0;
        
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Clear expired cache entries
     * 
     * @return int Number of files deleted
     */
    public static function clearExpired() {
        self::init();
        $count = 0;
        
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if (time() > $data['expires']) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Check if cache exists and is valid
     * 
     * @param string $key Cache key
     * @return bool
     */
    public static function has($key) {
        $file = self::getCachePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if (time() > $data['expires']) {
            self::delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get cache info
     * 
     * @param string $key Cache key
     * @return array|null Cache info or null if not found
     */
    public static function info($key) {
        $file = self::getCachePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        return [
            'created' => $data['created'],
            'expires' => $data['expires'],
            'ttl' => $data['expires'] - $data['created'],
            'is_expired' => time() > $data['expires'],
            'size' => filesize($file)
        ];
    }
    
    /**
     * Remember pattern: Get from cache or execute callback and cache result
     * 
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @param int $ttl Time to live in seconds
     * @return mixed
     */
    public static function remember($key, callable $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
}

