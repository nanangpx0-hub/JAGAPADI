<?php
/**
 * SimpleXLSX Parser - Enhanced Lightweight Excel Parser
 * Based on SimpleXLSX by Sergey Shuchkin
 * 
 * @version 1.1.0 
 * @author JAGAPADI System
 */

class SimpleXLSX {
    private $zip;
    private $sheets = [];
    private $sharedStrings = [];
    private $styles = [];
    private $error = '';
    private $debug = [];
    
    public static function parse($filename) {
        $xlsx = new self();
        if ($xlsx->_parse($filename)) {
            return $xlsx;
        }
        // Return object to get error instead of false
        return $xlsx;
    }
    
    public static function parseData($data) {
        $xlsx = new self();
        
        // Try multiple temp directories
        $tempDirs = [
            sys_get_temp_dir(),
            dirname(__FILE__) . '/../..',
            'C:/Windows/Temp',
            '/tmp'
        ];
        
        $tmp = false;
        foreach ($tempDirs as $dir) {
            if (is_writable($dir)) {
                $tmp = $dir . '/xlsx_parse_' . uniqid() . '.xlsx';
                break;
            }
        }
        
        if (!$tmp) {
            $xlsx->error = 'Cannot find writable temp directory';
            return $xlsx;
        }
        
        file_put_contents($tmp, $data);
        $result = $xlsx->_parse($tmp);
        @unlink($tmp);
        
        if ($result) {
            return $xlsx;
        }
        return $xlsx;
    }
    
    public function error() {
        return $this->error;
    }
    
    public function getDebug() {
        return $this->debug;
    }
    
    public function rows($sheetIndex = 0) {
        if (!isset($this->sheets[$sheetIndex])) {
            return [];
        }
        return $this->sheets[$sheetIndex];
    }
    
    public function sheetNames() {
        return array_keys($this->sheets);
    }
    
    public function sheetsCount() {
        return count($this->sheets);
    }
    
    public function hasData() {
        return !empty($this->sheets) && !empty($this->sheets[0]);
    }
    
    private function _parse($filename) {
        $this->debug[] = "Attempting to parse: " . $filename;
        
        if (!file_exists($filename)) {
            $this->error = 'File not found: ' . $filename;
            return false;
        }
        
        if (!is_readable($filename)) {
            $this->error = 'File not readable: ' . $filename;
            return false;
        }
        
        $filesize = filesize($filename);
        $this->debug[] = "File size: " . $filesize . " bytes";
        
        if ($filesize < 100) {
            $this->error = 'File too small to be a valid XLSX';
            return false;
        }
        
        // Check if it's actually a ZIP file
        $header = file_get_contents($filename, false, null, 0, 4);
        if ($header !== "PK\x03\x04") {
            // Try if it's a CSV file
            if (strpos(file_get_contents($filename, false, null, 0, 100), ',') !== false) {
                $this->debug[] = "File appears to be CSV, parsing as CSV";
                return $this->_parseAsCsv($filename);
            }
            $this->error = 'File is not a valid XLSX (ZIP) format';
            return false;
        }
        
        $this->zip = new ZipArchive();
        $openResult = $this->zip->open($filename);
        
        if ($openResult !== true) {
            $errorMessages = [
                ZipArchive::ER_EXISTS => 'File already exists',
                ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                ZipArchive::ER_INVAL => 'Invalid argument',
                ZipArchive::ER_MEMORY => 'Memory allocation failure',
                ZipArchive::ER_NOENT => 'No such file',
                ZipArchive::ER_NOZIP => 'Not a ZIP archive',
                ZipArchive::ER_OPEN => 'Cannot open file',
                ZipArchive::ER_READ => 'Read error',
                ZipArchive::ER_SEEK => 'Seek error'
            ];
            $this->error = 'Cannot open ZIP archive: ' . ($errorMessages[$openResult] ?? 'Unknown error ' . $openResult);
            return false;
        }
        
        $this->debug[] = "ZIP opened successfully, num files: " . $this->zip->numFiles;
        
        // List contents for debugging
        for ($i = 0; $i < min($this->zip->numFiles, 20); $i++) {
            $this->debug[] = "ZIP contains: " . $this->zip->getNameIndex($i);
        }
        
        // Parse shared strings
        $this->_parseSharedStrings();
        $this->debug[] = "Shared strings count: " . count($this->sharedStrings);
        
        // Parse workbook to get sheet names
        $sheetNames = $this->_parseWorkbook();
        $this->debug[] = "Found sheets: " . implode(', ', $sheetNames);
        
        // Parse each sheet
        $foundSheets = 0;
        foreach ($sheetNames as $index => $name) {
            $sheetPath = 'xl/worksheets/sheet' . ($index + 1) . '.xml';
            $sheetData = $this->zip->getFromName($sheetPath);
            
            if ($sheetData !== false) {
                $this->debug[] = "Parsing sheet: " . $sheetPath;
                $this->sheets[$index] = $this->_parseSheet($sheetData);
                $foundSheets++;
            } else {
                $this->debug[] = "Sheet not found: " . $sheetPath;
            }
        }
        
        $this->zip->close();
        
        if ($foundSheets === 0) {
            $this->error = 'No valid worksheets found in the file';
            return false;
        }
        
        $this->debug[] = "Successfully parsed " . $foundSheets . " sheets";
        return true;
    }
    
    /**
     * Parse file as CSV (fallback)
     */
    private function _parseAsCsv($filename) {
        $rows = [];
        
        if (($handle = fopen($filename, 'r')) !== false) {
            // Try to detect encoding
            $content = file_get_contents($filename);
            
            // Remove BOM if present
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }
            
            fclose($handle);
            
            // Parse CSV content
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $row = str_getcsv($line);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }
        
        if (empty($rows)) {
            $this->error = 'Failed to parse CSV file';
            return false;
        }
        
        $this->sheets[0] = $rows;
        return true;
    }
    
    private function _parseSharedStrings() {
        $data = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($data === false) {
            $this->debug[] = "No shared strings file found";
            return;
        }
        
        // Clean XML for parsing
        $data = $this->_cleanXml($data);
        
        $xml = @simplexml_load_string($data);
        if ($xml === false) {
            $this->debug[] = "Failed to parse shared strings XML";
            return;
        }
        
        // Register namespaces
        $namespaces = $xml->getNamespaces(true);
        
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $this->sharedStrings[] = (string) $si->t;
            } elseif (isset($si->r)) {
                $text = '';
                foreach ($si->r as $r) {
                    if (isset($r->t)) {
                        $text .= (string) $r->t;
                    }
                }
                $this->sharedStrings[] = $text;
            } else {
                // Try to get text from any child element
                $this->sharedStrings[] = (string) $si;
            }
        }
    }
    
    private function _parseWorkbook() {
        $data = $this->zip->getFromName('xl/workbook.xml');
        $sheetNames = [];
        
        if ($data !== false) {
            $data = $this->_cleanXml($data);
            $xml = @simplexml_load_string($data);
            
            if ($xml !== false && isset($xml->sheets)) {
                foreach ($xml->sheets->sheet as $sheet) {
                    $sheetNames[] = (string) $sheet['name'];
                }
            }
        }
        
        return $sheetNames ?: ['Sheet1'];
    }
    
    private function _parseSheet($data) {
        $rows = [];
        $data = $this->_cleanXml($data);
        $xml = @simplexml_load_string($data);
        
        if ($xml === false) {
            $this->debug[] = "Failed to parse sheet XML";
            return $rows;
        }
        
        if (!isset($xml->sheetData)) {
            $this->debug[] = "No sheetData element found";
            return $rows;
        }
        
        if (!isset($xml->sheetData->row)) {
            $this->debug[] = "No rows found in sheet";
            return $rows;
        }
        
        $maxRow = 0;
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            if ($rowNum > $maxRow) $maxRow = $rowNum;
            
            $rowData = [];
            
            foreach ($row->c as $cell) {
                $cellRef = (string) $cell['r'];
                $colIndex = $this->_colToIndex($cellRef);
                
                $value = '';
                
                // Check cell type
                $type = (string) $cell['t'];
                
                if ($type === 's') {
                    // Shared string
                    $stringIndex = (int) $cell->v;
                    $value = $this->sharedStrings[$stringIndex] ?? '';
                } elseif ($type === 'str' || $type === 'inlineStr') {
                    // String or inline string
                    if (isset($cell->is->t)) {
                        $value = (string) $cell->is->t;
                    } elseif (isset($cell->v)) {
                        $value = (string) $cell->v;
                    }
                } elseif ($type === 'b') {
                    // Boolean
                    $value = (string) $cell->v === '1' ? 'TRUE' : 'FALSE';
                } elseif ($type === 'e') {
                    // Error
                    $value = (string) $cell->v;
                } else {
                    // Number or formula
                    if (isset($cell->v)) {
                        $value = (string) $cell->v;
                    }
                }
                
                // Fill empty cells before this one
                while (count($rowData) < $colIndex) {
                    $rowData[] = '';
                }
                
                $rowData[$colIndex] = $value;
            }
            
            // Fill row array up to current row
            while (count($rows) < $rowNum - 1) {
                $rows[] = [];
            }
            
            $rows[$rowNum - 1] = $rowData;
        }
        
        $this->debug[] = "Parsed " . count($rows) . " rows, max row: " . $maxRow;
        return $rows;
    }
    
    /**
     * Clean XML string for parsing
     */
    private function _cleanXml($data) {
        // Remove any BOM
        if (substr($data, 0, 3) === "\xEF\xBB\xBF") {
            $data = substr($data, 3);
        }
        
        // Remove default namespace for easier parsing
        $data = preg_replace('/xmlns="[^"]*"/', '', $data);
        $data = preg_replace('/xmlns:r="[^"]*"/', '', $data);
        $data = preg_replace('/xmlns:mc="[^"]*"/', '', $data);
        $data = preg_replace('/mc:Ignorable="[^"]*"/', '', $data);
        
        return $data;
    }
    
    private function _colToIndex($cellRef) {
        preg_match('/^([A-Z]+)/', $cellRef, $matches);
        $col = $matches[1] ?? 'A';
        
        $index = 0;
        $length = strlen($col);
        
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        
        return $index - 1;
    }
}
