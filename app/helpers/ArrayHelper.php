<?php
/**
 * Array Helper
 * Helper functions untuk safe array access
 */

class ArrayHelper {
    /**
     * Get array value safely dengan default value
     * 
     * @param array $array Array yang akan diakses
     * @param string $key Key yang dicari
     * @param mixed $default Default value jika key tidak ada
     * @param bool $logMissing Log jika key tidak ditemukan
     * @return mixed
     */
    public static function get($array, $key, $default = null, $logMissing = false) {
        if (!is_array($array)) {
            if ($logMissing) {
                ErrorLogger::log("ArrayHelper::get called with non-array parameter", 'WARNING', [
                    'key' => $key,
                    'type' => gettype($array)
                ]);
            }
            return $default;
        }
        
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        
        if ($logMissing) {
            ErrorLogger::logMissingKey($key, debug_backtrace()[0]['file'] ?? '', debug_backtrace()[0]['line'] ?? 0);
        }
        
        return $default;
    }
    
    /**
     * Get multiple keys safely
     * 
     * @param array $array Array yang akan diakses
     * @param array $keys Array of keys
     * @param mixed $default Default value
     * @return array
     */
    public static function getMultiple($array, $keys, $default = null) {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = self::get($array, $key, $default);
        }
        
        return $result;
    }
    
    /**
     * Check if all keys exist in array
     * 
     * @param array $array Array yang akan dicek
     * @param array $keys Keys yang harus ada
     * @return bool
     */
    public static function hasKeys($array, $keys) {
        if (!is_array($array)) {
            return false;
        }
        
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get nested array value safely
     * 
     * @param array $array Array yang akan diakses
     * @param string $path Path dengan dot notation (e.g., 'user.profile.name')
     * @param mixed $default Default value
     * @return mixed
     */
    public static function getPath($array, $path, $default = null) {
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    /**
     * Safe htmlspecialchars untuk array value
     * 
     * @param array $array Array
     * @param string $key Key
     * @param mixed $default Default value
     * @return string
     */
    public static function escape($array, $key, $default = '') {
        $value = self::get($array, $key, $default);
        
        if ($value === null) {
            return htmlspecialchars((string)$default, ENT_QUOTES, 'UTF-8');
        }
        
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Filter array untuk hanya key tertentu
     * 
     * @param array $array Array yang akan difilter
     * @param array $allowedKeys Keys yang diperbolehkan
     * @return array
     */
    public static function only($array, $allowedKeys) {
        return array_intersect_key($array, array_flip($allowedKeys));
    }
    
    /**
     * Filter array untuk exclude key tertentu
     * 
     * @param array $array Array yang akan difilter
     * @param array $excludeKeys Keys yang akan diexclude
     * @return array
     */
    public static function except($array, $excludeKeys) {
        return array_diff_key($array, array_flip($excludeKeys));
    }
}
