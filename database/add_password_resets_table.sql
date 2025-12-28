-- Add password_resets table for secure password reset functionality

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

-- Update users table to accommodate longer password hashes (Argon2)
-- The existing VARCHAR(128) should be sufficient for Argon2, but let's make sure
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;
