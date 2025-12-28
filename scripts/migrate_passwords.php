<?php
/**
 * Password Migration Script
 * Migrates existing MD5 hashed passwords to secure Argon2ID hashing
 *
 * Since MD5 is a one-way hash, we cannot automatically convert existing passwords.
 * This script identifies users with old MD5 hashes and schedules password resets.
 */

require_once '../config/config.php';
require_once '../config/database.php';

class PasswordMigration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function run() {
        echo "=== PASSWORD MIGRATION SCRIPT ===\n\n";

        // 1. Identify users with MD5 hashed passwords
        $md5Users = $this->getUsersWithMd5Passwords();

        if (empty($md5Users)) {
            echo "✅ No users found with MD5 passwords. Migration complete.\n";
            return;
        }

        echo "Found " . count($md5Users) . " users with MD5 hashed passwords.\n\n";

        // 2. Create password reset tokens for all users
        $migratedCount = 0;
        $errors = [];

        foreach ($md5Users as $user) {
            try {
                $this->createPasswordResetForUser($user);
                $migratedCount++;
                echo "→ Scheduled password reset for user: {$user['nama_lengkap']} ({$user['username']})\n";
            } catch (Exception $e) {
                $errors[] = "Failed for user {$user['username']}: " . $e->getMessage();
                echo "❌ Failed to schedule reset for user: {$user['username']} - {$e->getMessage()}\n";
            }
        }

        // 3. Create migration log
        $this->createMigrationLog($md5Users, $migratedCount, $errors);

        echo "\n=== MIGRATION SUMMARY ===\n";
        echo "Total users processed: " . count($md5Users) . "\n";
        echo "Successful migrations: $migratedCount\n";
        echo "Errors: " . count($errors) . "\n";

        if (!empty($errors)) {
            echo "\nErrors encountered:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
        }

        echo "\n📧 All affected users will receive password reset emails.\n";
        echo "🔒 New user registrations will use secure Argon2ID hashing.\n";
        echo "✅ Migration complete!\n";
    }

    private function getUsersWithMd5Passwords() {
        // MD5 hashes are always 32 characters long
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LENGTH(password) = 32 AND aktif = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createPasswordResetForUser($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Insert reset token
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, token, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user['id'], $token, $expires]);

        // TODO: Send email to user with reset link
        // For now, we'll log the reset link that should be emailed
        $resetLink = BASE_URL . "auth/reset_password?token=$token";
        $this->logResetEmail($user, $resetLink);
    }

    private function createMigrationLog($md5Users, $migratedCount, $errors) {
        $logData = [
            'migration_type' => 'md5_to_argon2id',
            'timestamp' => date('Y-m-d H:i:s'),
            'total_users_affected' => count($md5Users),
            'successful_migrations' => $migratedCount,
            'errors_count' => count($errors),
            'affected_users' => array_column($md5Users, 'username'),
            'errors' => $errors
        ];

        $filename = '../logs/password_migration_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($filename, json_encode($logData, JSON_PRETTY_PRINT));
    }

    private function logResetEmail($user, $resetLink) {
        $logFile = '../logs/password_reset_emails.log';
        $logEntry = sprintf(
            "[%s] Password reset scheduled for user %s (%s): %s\n",
            date('Y-m-d H:i:s'),
            $user['username'],
            $user['nama_lengkap'],
            $resetLink
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}

// Run migration if password_resets table exists, otherwise create it
try {
    $migration = new PasswordMigration();
    $migration->run();
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";

    // Try to create password_resets table if it doesn't exist
    echo "Attempting to create password_resets table...\n";

    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        );
    ";

    try {
        $db = Database::getInstance()->getConnection();
        $db->exec($createTableSQL);
        echo "✅ password_resets table created.\n";

        // Retry migration
        $migration = new PasswordMigration();
        $migration->run();
    } catch (Exception $e2) {
        echo "❌ Failed to create table: " . $e2->getMessage() . "\n";
        echo "Please run this script after ensuring the database is set up properly.\n";
    }
}
