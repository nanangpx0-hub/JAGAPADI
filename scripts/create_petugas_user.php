<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

$username = 'petugas_test';
$password = 'password123';
$nama = 'Petugas Test User';
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

$stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role, aktif) VALUES (?, ?, ?, ?, 1)");
if ($stmt->execute([$username, $hashedPassword, $nama, $role])) {
    echo "User '$username' created successfully.\n";
    echo "Password: $password\n";
    echo "Role: $role\n";
} else {
    echo "Failed to create user.\n";
}
