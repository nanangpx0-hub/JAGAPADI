<?php
// Script to create petugas user - Browser Friendly
echo "<!DOCTYPE html><html><head><title>Create Petugas</title></head><body><pre>";

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$username = 'petugas_01';
$password = 'Petugas2025!';
$nama = 'Petugas Lapangan 01';
$email = 'petugas01@jagapadi.id';
$phone = '081234567890';
$role = 'petugas';

// Check if exists
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "User '$username' already exists.\n";
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 2048,
    'time_cost' => 4,
    'threads' => 3
]);

// Try to insert with email and phone
try {
    $query = "INSERT INTO users (username, password, nama_lengkap, email, phone, role, aktif) VALUES (?, ?, ?, ?, ?, ?, 1)";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$username, $hashedPassword, $nama, $email, $phone, $role]);
    
    if ($result) {
        echo "User '$username' created successfully.\n";
        echo "----------------------------------------\n";
        echo "Username : $username\n";
        echo "Password : $password\n";
        echo "Role     : $role\n";
        echo "Nama     : $nama\n";
        echo "Email    : $email\n";
        echo "Phone    : $phone\n";
        echo "----------------------------------------\n";
    } else {
        echo "Failed to create user.\n";
    }
} catch (PDOException $e) {
    // Fallback if email/phone columns don't exist
    echo "Error with full columns: " . $e->getMessage() . "\n";
    echo "Retrying without email/phone...\n";
    
    try {
        $query = "INSERT INTO users (username, password, nama_lengkap, role, aktif) VALUES (?, ?, ?, ?, 1)";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$username, $hashedPassword, $nama, $role]);
        
        if ($result) {
            echo "User '$username' created successfully (without email/phone).\n";
            echo "----------------------------------------\n";
            echo "Username : $username\n";
            echo "Password : $password\n";
            echo "Role     : $role\n";
            echo "Nama     : $nama\n";
            echo "----------------------------------------\n";
        }
    } catch (PDOException $e2) {
        echo "Failed to create user: " . $e2->getMessage() . "\n";
    }
}

echo "</pre></body></html>";
