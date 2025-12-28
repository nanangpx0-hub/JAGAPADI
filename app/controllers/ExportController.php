<?php
class ExportController extends Controller {
    private $laporanModel;
    
    public function __construct() {
        $this->laporanModel = $this->model('LaporanHama');
    }
    
    public function csv() {
        $this->checkRole(['admin', 'operator']);
        
        $laporan = $this->laporanModel->getAllWithDetails();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=laporan_hama_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'ID',
            'Tanggal',
            'Kode OPT',
            'Nama OPT',
            'Jenis',
            'Lokasi',
            'Latitude',
            'Longitude',
            'Tingkat Keparahan',
            'Populasi',
            'Luas Serangan (Ha)',
            'Status',
            'Pelapor',
            'Catatan'
        ]);
        
        // Data
        foreach ($laporan as $row) {
            fputcsv($output, [
                $row['id'] ?? '',
                $row['tanggal'] ?? '',
                $row['kode_opt'] ?? '',
                $row['nama_opt'] ?? '',
                $row['jenis'] ?? '',
                $row['lokasi'] ?? '',
                $row['latitude'] ?? '',
                $row['longitude'] ?? '',
                $row['tingkat_keparahan'] ?? '',
                $row['populasi'] ?? 0,
                $row['luas_serangan'] ?? 0,
                $row['status'] ?? '',
                $row['pelapor_nama'] ?? '',
                $row['catatan'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function pdf() {
        $this->checkRole(['admin', 'operator']);
        
        $laporan = $this->laporanModel->getAllWithDetails();
        
        // Generate HTML for display (not real PDF without library)
        $html = $this->generatePdfHtml($laporan);
        
        // Output as HTML (viewable in browser)
        // Note: For real PDF, install Dompdf with: composer require dompdf/dompdf
        header('Content-Type: text/html; charset=utf-8');
        
        echo $html;
        exit;
    }
    
    private function generatePdfHtml($laporan) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Hama - JAGAPADI</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
            background: white;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #4CAF50; 
            color: white; 
            font-weight: bold;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            padding: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        .logo { 
            font-size: 28px; 
            font-weight: bold; 
            color: #4CAF50; 
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .print-button:hover {
            background: #45a049;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
            }
            @page {
                margin: 2cm;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>
    
    <div class="header">
        <div class="logo">JAGAPADI</div>
        <h2>Laporan Fenomena Pertanian</h2>
        <p class="subtitle">Jember Agrikultur Gapai Prestasi Digital</p>
        <p class="subtitle">BPS Kabupaten Jember</p>
        <p>Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>OPT</th>
                <th>Jenis</th>
                <th>Lokasi</th>
                <th>Keparahan</th>
                <th>Populasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';
        
        $no = 1;
        foreach ($laporan as $row) {
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                <td>' . htmlspecialchars($row['nama_opt'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['jenis'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['lokasi'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['tingkat_keparahan'] ?? '') . '</td>
                <td>' . ($row['populasi'] ?? 0) . '</td>
                <td>' . htmlspecialchars($row['status'] ?? '') . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p><strong>Catatan:</strong></p>
        <ul style="margin: 10px 0;">
            <li>Dokumen ini digenerate otomatis oleh sistem JAGAPADI</li>
            <li>Total Laporan: ' . count($laporan) . ' record</li>
            <li>Untuk menyimpan sebagai PDF: Klik tombol Print, lalu pilih "Save as PDF"</li>
        </ul>
        <p><strong>BPS Kabupaten Jember</strong></p>
        <p>Pengembang: Nanang Pamungkas | Email: nanangpx@gmail.com | WA: +6281232303096</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    public function excel() {
        $this->checkRole(['admin', 'operator']);
        
        $laporan = $this->laporanModel->getAllWithDetails();
        
        // Simple Excel export using HTML table
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=laporan_hama_' . date('Y-m-d') . '.xls');
        
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Laporan Hama</x:Name>
                            <x:WorksheetOptions><x:Print><x:ValidPrinterInfo/></x:Print></x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
        </head>
        <body>';
        
        echo '<table border="1">
            <tr>
                <th colspan="14" style="text-align:center;"><h2>JAGAPADI - Laporan Fenomena Pertanian</h2></th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Kode OPT</th>
                <th>Nama OPT</th>
                <th>Jenis</th>
                <th>Lokasi</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Tingkat Keparahan</th>
                <th>Populasi</th>
                <th>Luas Serangan (Ha)</th>
                <th>Status</th>
                <th>Pelapor</th>
                <th>Catatan</th>
            </tr>';
        
        foreach ($laporan as $row) {
            echo '<tr>
                <td>' . ($row['id'] ?? '') . '</td>
                <td>' . ($row['tanggal'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['kode_opt'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['nama_opt'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['jenis'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['lokasi'] ?? '') . '</td>
                <td>' . ($row['latitude'] ?? '') . '</td>
                <td>' . ($row['longitude'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['tingkat_keparahan'] ?? '') . '</td>
                <td>' . ($row['populasi'] ?? 0) . '</td>
                <td>' . ($row['luas_serangan'] ?? 0) . '</td>
                <td>' . htmlspecialchars($row['status'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['pelapor_nama'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['catatan'] ?? '') . '</td>
            </tr>';
        }
        
        echo '</table></body></html>';
        exit;
    }
}
