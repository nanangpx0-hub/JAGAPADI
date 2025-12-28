-- Migration: Add password change fields to users table
-- Date: 2024-12-26
-- Description: Add must_change_password and last_password_change_at fields for force password change functionality

-- Add must_change_password column (default 0 for existing users, 1 for new users)
ALTER TABLE users 
ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Flag to force password change on next login';

-- Add last_password_change_at column
ALTER TABLE users 
ADD COLUMN last_password_change_at DATETIME NULL 
COMMENT 'Timestamp of last password change';

-- Update existing users to have last_password_change_at as their created_at date
UPDATE users 
SET last_password_change_at = created_at 
WHERE last_password_change_at IS NULL;

-- Set must_change_password = 1 for default admin accounts that should change password
UPDATE users 
SET must_change_password = 1 
WHERE username IN ('admin', 'admin_jagapadi') 
   OR (role = 'admin' AND password IS NOT NULL);

-- Create index for performance
CREATE INDEX idx_users_must_change_password ON users(must_change_password);
CREATE INDEX idx_users_last_password_change ON users(last_password_change_at);