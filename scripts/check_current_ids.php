<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT id, kode_kabupaten FROM master_kabupaten ORDER BY kode_kabupaten');
$records = $stmt->fetchAll();

echo "Current records:\n";
foreach ($records as $r) {
    echo "ID: {$r['id']} - Kode: {$r['kode_kabupaten']}\n";
}
echo "\nTotal: " . count($records) . "\n";

// Check duplicates
$stmt = $db->query("
    SELECT id, COUNT(*) as count, GROUP_CONCAT(kode_kabupaten) as kabupatens
    FROM master_kabupaten 
    GROUP BY id 
    HAVING COUNT(*) > 1
");
$duplicates = $stmt->fetchAll();

if (!empty($duplicates)) {
    echo "\nDuplicates found:\n";
    foreach ($duplicates as $dup) {
        echo "ID '{$dup['id']}' appears {$dup['count']} times: {$dup['kabupatens']}\n";
    }
} else {
    echo "\nNo duplicates found.\n";
}
