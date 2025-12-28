<?php
/**
 * Simple XLSX Writer
 * Creates basic Excel files without external dependencies
 * 
 * XLSX is a ZIP archive containing XML files
 * 
 * @version 1.0.0
 * @author JAGAPADI System
 */

class SimpleXLSXWriter {
    
    private $sheets = [];
    private $sharedStrings = [];
    private $stringCount = 0;
    
    /**
     * Add a sheet with data
     * 
     * @param string $name Sheet name
     * @param array $data 2D array of data
     * @param array $options Optional settings
     */
    public function addSheet($name, $data, $options = []) {
        $this->sheets[] = [
            'name' => $name,
            'data' => $data,
            'options' => $options
        ];
    }
    
    /**
     * Generate XLSX file and send to browser
     * 
     * @param string $filename
     */
    public function download($filename) {
        // Try different temp directories
        $tempDirs = [
            sys_get_temp_dir(),
            ROOT_PATH . '/logs',
            dirname(__FILE__),
            'C:/Windows/Temp',
            '/tmp'
        ];
        
        $tempFile = false;
        foreach ($tempDirs as $dir) {
            if (is_writable($dir)) {
                $tempFile = $dir . '/xlsx_' . uniqid() . '.xlsx';
                break;
            }
        }
        
        if ($tempFile === false) {
            // Fallback: use memory stream
            $tempFile = 'php://temp';
        }
        
        if (!$this->save($tempFile)) {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error generating Excel file';
            exit;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        if (file_exists($tempFile)) {
            header('Content-Length: ' . filesize($tempFile));
            header('Cache-Control: max-age=0');
            readfile($tempFile);
            @unlink($tempFile);
        }
        
        exit;
    }
    
    /**
     * Save XLSX to file
     * 
     * @param string $filepath
     * @return bool
     */
    public function save($filepath) {
        $zip = new ZipArchive();
        
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        
        // Build shared strings from all sheets
        $this->buildSharedStrings();
        
        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->getContentTypes());
        
        // _rels/.rels
        $zip->addFromString('_rels/.rels', $this->getRels());
        
        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRels());
        
        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->getWorkbook());
        
        // xl/styles.xml
        $zip->addFromString('xl/styles.xml', $this->getStyles());
        
        // xl/sharedStrings.xml
        $zip->addFromString('xl/sharedStrings.xml', $this->getSharedStrings());
        
        // xl/worksheets/sheet{n}.xml
        foreach ($this->sheets as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $this->getSheet($sheet));
        }
        
        $zip->close();
        return true;
    }
    
    /**
     * Build shared strings index
     */
    private function buildSharedStrings() {
        $this->sharedStrings = [];
        $this->stringCount = 0;
        
        foreach ($this->sheets as $sheet) {
            foreach ($sheet['data'] as $row) {
                foreach ($row as $cell) {
                    if (is_string($cell) && !is_numeric($cell)) {
                        if (!isset($this->sharedStrings[$cell])) {
                            $this->sharedStrings[$cell] = count($this->sharedStrings);
                        }
                        $this->stringCount++;
                    }
                }
            }
        }
    }
    
    /**
     * Get [Content_Types].xml
     */
    private function getContentTypes() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $xml .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        
        foreach ($this->sheets as $index => $sheet) {
            $xml .= '<Override PartName="/xl/worksheets/sheet' . ($index + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        
        $xml .= '</Types>';
        return $xml;
    }
    
    /**
     * Get _rels/.rels
     */
    private function getRels() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $xml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }
    
    /**
     * Get xl/_rels/workbook.xml.rels
     */
    private function getWorkbookRels() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        
        $rId = 1;
        foreach ($this->sheets as $index => $sheet) {
            $xml .= '<Relationship Id="rId' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($index + 1) . '.xml"/>';
            $rId++;
        }
        
        $xml .= '<Relationship Id="rId' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $rId++;
        $xml .= '<Relationship Id="rId' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        
        $xml .= '</Relationships>';
        return $xml;
    }
    
    /**
     * Get xl/workbook.xml
     */
    private function getWorkbook() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheets>';
        
        foreach ($this->sheets as $index => $sheet) {
            $xml .= '<sheet name="' . htmlspecialchars($sheet['name']) . '" sheetId="' . ($index + 1) . '" r:id="rId' . ($index + 1) . '"/>';
        }
        
        $xml .= '</sheets>';
        $xml .= '</workbook>';
        return $xml;
    }
    
    /**
     * Get xl/styles.xml
     */
    private function getStyles() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        
        // Fonts
        $xml .= '<fonts count="2">';
        $xml .= '<font><sz val="11"/><name val="Calibri"/></font>'; // Normal
        $xml .= '<font><b/><sz val="11"/><name val="Calibri"/></font>'; // Bold (header)
        $xml .= '</fonts>';
        
        // Fills
        $xml .= '<fills count="3">';
        $xml .= '<fill><patternFill patternType="none"/></fill>';
        $xml .= '<fill><patternFill patternType="gray125"/></fill>';
        $xml .= '<fill><patternFill patternType="solid"><fgColor rgb="FF4472C4"/><bgColor indexed="64"/></patternFill></fill>'; // Blue header
        $xml .= '</fills>';
        
        // Borders
        $xml .= '<borders count="2">';
        $xml .= '<border/>';
        $xml .= '<border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/></border>';
        $xml .= '</borders>';
        
        // Cell styles
        $xml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        
        // Cell formats
        $xml .= '<cellXfs count="3">';
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'; // Default
        $xml .= '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf>'; // Header
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'; // Data with border
        $xml .= '</cellXfs>';
        
        $xml .= '</styleSheet>';
        return $xml;
    }
    
    /**
     * Get xl/sharedStrings.xml
     */
    private function getSharedStrings() {
        $count = count($this->sharedStrings);
        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $this->stringCount . '" uniqueCount="' . $count . '">';
        
        foreach (array_keys($this->sharedStrings) as $string) {
            $xml .= '<si><t>' . htmlspecialchars($string, ENT_XML1) . '</t></si>';
        }
        
        $xml .= '</sst>';
        return $xml;
    }
    
    /**
     * Get sheet XML
     * 
     * @param array $sheet
     * @return string
     */
    private function getSheet($sheet) {
        $data = $sheet['data'];
        $options = $sheet['options'];
        $headerStyle = $options['headerStyle'] ?? true;
        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        
        // Column widths
        if (!empty($options['columnWidths'])) {
            $xml .= '<cols>';
            foreach ($options['columnWidths'] as $col => $width) {
                $colNum = is_numeric($col) ? $col + 1 : $this->colLetterToNum($col);
                $xml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }
        
        $xml .= '<sheetData>';
        
        foreach ($data as $rowIndex => $row) {
            $rowNum = $rowIndex + 1;
            $xml .= '<row r="' . $rowNum . '">';
            
            foreach ($row as $colIndex => $cell) {
                $colLetter = $this->numToColLetter($colIndex);
                $cellRef = $colLetter . $rowNum;
                
                // Determine style (1 = header, 2 = data with border, 0 = default)
                $styleId = 0;
                if ($headerStyle && $rowIndex === 0) {
                    $styleId = 1; // Header style
                } else if ($headerStyle) {
                    $styleId = 2; // Data with border
                }
                
                if (is_numeric($cell) && !is_string($cell)) {
                    // Number
                    $xml .= '<c r="' . $cellRef . '" s="' . $styleId . '"><v>' . $cell . '</v></c>';
                } else if ($cell !== '' && $cell !== null) {
                    // String - use shared string
                    $stringIndex = $this->sharedStrings[$cell] ?? 0;
                    $xml .= '<c r="' . $cellRef . '" t="s" s="' . $styleId . '"><v>' . $stringIndex . '</v></c>';
                } else {
                    // Empty
                    $xml .= '<c r="' . $cellRef . '" s="' . $styleId . '"/>';
                }
            }
            
            $xml .= '</row>';
        }
        
        $xml .= '</sheetData>';
        $xml .= '</worksheet>';
        
        return $xml;
    }
    
    /**
     * Convert column number to letter (0 = A, 1 = B, etc.)
     * 
     * @param int $num
     * @return string
     */
    private function numToColLetter($num) {
        $letter = '';
        while ($num >= 0) {
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intval($num / 26) - 1;
        }
        return $letter;
    }
    
    /**
     * Convert column letter to number
     * 
     * @param string $letter
     * @return int
     */
    private function colLetterToNum($letter) {
        $num = 0;
        $length = strlen($letter);
        for ($i = 0; $i < $length; $i++) {
            $num = $num * 26 + (ord($letter[$i]) - ord('A') + 1);
        }
        return $num;
    }
}
