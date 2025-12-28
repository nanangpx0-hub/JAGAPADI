<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'laporan_hama'");
$stmt->execute([DB_NAME]);
$cols = $stmt->fetchAll();
echo json_encode(array_column($cols, 'COLUMN_NAME'));