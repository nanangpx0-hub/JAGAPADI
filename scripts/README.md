# User Seeder Script - JAGAPADI

Script untuk menambahkan pengguna dengan level berbeda ke dalam database JAGAPADI.

## 📋 Daftar Isi

- [Requirements](#requirements)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Cara Menjalankan](#cara-menjalankan)
- [User yang Ditambahkan](#user-yang-ditambahkan)
- [Fitur](#fitur)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Log Files](#log-files)

## 🔧 Requirements

### Software Requirements
- PHP >= 7.4
- MySQL/MariaDB >= 5.7
- PDO Extension
- BCrypt Support (built-in PHP)

### PHP Extensions
```bash
php -m | grep -E 'pdo|pdo_mysql|bcrypt'
```

Pastikan extension berikut aktif:
- `pdo`
- `pdo_mysql`
- `openssl` (untuk bcrypt)

## 📦 Instalasi

### 1. Clone atau Copy Script

Script sudah tersedia di folder `scripts/`:
```
scripts/
├── seed_users.php          # Main seeder script
├── test_user_seeder.php    # Unit test script
└── README.md               # Dokumentasi ini
```

### 2. Pastikan Database Sudah Dibuat

```sql
CREATE DATABASE IF NOT EXISTS jagapadi;
USE jagapadi;

-- Tabel users harus sudah ada
-- Jika belum, jalankan migration terlebih dahulu
```

### 3. Verifikasi Koneksi Database

Edit file `config/database.php` dan pastikan konfigurasi benar:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'jagapadi');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## ⚙️ Konfigurasi

### Environment Variables (Optional)

Anda bisa menggunakan environment variables untuk konfigurasi database:

```bash
export DB_HOST=localhost
export DB_NAME=jagapadi
export DB_USER=root
export DB_PASS=your_password
```

Atau buat file `.env`:

```env
DB_HOST=localhost
DB_NAME=jagapadi
DB_USER=root
DB_PASS=your_password
```

## 🚀 Cara Menjalankan

### 1. Jalankan Unit Test (Recommended)

Sebelum menjalankan seeder, jalankan unit test untuk memastikan semua fungsi bekerja:

```bash
cd /path/to/jagapadi
php scripts/test_user_seeder.php
```

**Expected Output:**
```
===========================================
  User Seeder Unit Tests
===========================================

[Test 1] Password Encryption with bcrypt
----------------------------------------
✓ PASS: Password hash created
✓ PASS: Hash uses bcrypt algorithm
✓ PASS: Password verification works
✓ PASS: Wrong password rejected
✓ PASS: Hash uses random salt

...

===========================================
  Test Summary
===========================================

Total Tests: 15
Passed: 15
Failed: 0
Success Rate: 100%

✓ All tests passed!
```

### 2. Jalankan Seeder Script

```bash
cd /path/to/jagapadi
php scripts/seed_users.php
```

**Expected Output:**
```
===========================================
  JAGAPADI User Seeder
===========================================

[2025-12-01 10:00:00] [INFO] [SYSTEM] [SUCCESS] Database connection established
[2025-12-01 10:00:00] [INFO] [SYSTEM] [TRANSACTION] Transaction started
[2025-12-01 10:00:00] [INFO] [admin_jagapadi] [SUCCESS] User created with role: admin
[2025-12-01 10:00:00] [INFO] [operator1] [SUCCESS] User created with role: operator
[2025-12-01 10:00:00] [INFO] [viewer1] [SUCCESS] User created with role: viewer
[2025-12-01 10:00:00] [INFO] [petugas] [SUCCESS] User created with role: petugas
[2025-12-01 10:00:00] [INFO] [SYSTEM] [TRANSACTION] Transaction committed

===========================================
  Execution Report
===========================================

{
    "overall_status": "success",
    "message": "Successfully created 4 users",
    "execution_time": "0.234 seconds",
    "summary": {
        "total_users": 4,
        "success": 4,
        "failed": 0,
        "skipped": 0
    },
    "details": [
        {
            "username": "admin_jagapadi",
            "role": "admin",
            "permissions": "create, read, update, delete",
            "status": "success",
            "message": "User created successfully"
        },
        ...
    ],
    "timestamp": "2025-12-01 10:00:00"
}

Report saved to: logs/user_seeder_report_2025-12-01_100000.json
```

### 3. Verifikasi User Berhasil Ditambahkan

```bash
# Via MySQL CLI
mysql -u root -p jagapadi -e "SELECT username, role, status FROM users;"

# Via PHP
php -r "
require 'config/database.php';
\$db = Database::getInstance()->getConnection();
\$stmt = \$db->query('SELECT username, role, status FROM users');
print_r(\$stmt->fetchAll(PDO::FETCH_ASSOC));
"
```

## 👥 User yang Ditambahkan

### 1. Admin User
```
Username: admin_jagapadi
Password: admin123
Role: admin
Permissions: create, read, update, delete
Status: active
```

### 2. Operator User
```
Username: operator1
Password: op1test
Role: operator
Permissions: create, read, update
Status: active
```

### 3. Viewer User
```
Username: viewer1
Password: vw1test
Role: viewer
Permissions: read
Status: active
```

### 4. Petugas User
```
Username: petugas
Password: petugas3509
Role: petugas
Permissions: create, read
Status: active
```

## ✨ Fitur

### 1. Security Features

#### Password Encryption
- Menggunakan **bcrypt** dengan salt round 10
- Password di-hash sebelum disimpan ke database
- Tidak ada plain text password di database

```php
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
```

#### SQL Injection Prevention
- Menggunakan **prepared statements**
- Parameter binding untuk semua query
- Input sanitization

```php
$stmt = $db->prepare("INSERT INTO users (...) VALUES (?, ?, ?, ...)");
$stmt->execute([$username, $hashedPassword, ...]);
```

### 2. Validation

#### Username Validation
- Tidak boleh kosong
- Minimal 3 karakter
- Hanya huruf, angka, dan underscore
- Regex: `/^[a-zA-Z0-9_]+$/`

#### Password Validation
- Tidak boleh kosong
- Minimal 8 karakter

#### Email Validation
- Format email valid
- Menggunakan `filter_var($email, FILTER_VALIDATE_EMAIL)`

#### Role Validation
- Hanya role yang valid: admin, operator, viewer, petugas

### 3. Transaction Management

#### Atomic Operation
- Semua insert dalam satu transaction
- All-or-nothing: semua berhasil atau semua rollback

```php
$db->beginTransaction();
try {
    // Insert all users
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
}
```

### 4. Duplicate Detection

#### Check Before Insert
- Cek username sudah ada atau belum
- Skip jika sudah ada (tidak error)
- Log sebagai "skipped"

```php
if ($this->usernameExists($username)) {
    // Skip and log
    continue;
}
```

### 5. Retry Mechanism

#### Database Connection Retry
- Maksimal 3 kali retry
- Delay 2 detik antar retry
- Log setiap attempt

```php
$maxRetries = 3;
while ($retries < $maxRetries) {
    try {
        $db = Database::getInstance()->getConnection();
        break;
    } catch (Exception $e) {
        $retries++;
        sleep(2);
    }
}
```

### 6. Comprehensive Logging

#### Log Format
```
[TIMESTAMP] [LEVEL] [USERNAME] [STATUS] [MESSAGE]
```

#### Log Levels
- `INFO`: Informasi normal
- `WARNING`: Peringatan (skip, dll)
- `ERROR`: Error yang terjadi

#### Log File Location
```
logs/user_seeder.log
logs/user_seeder_report_YYYY-MM-DD_HHMMSS.json
```

### 7. JSON Report

#### Report Structure
```json
{
    "overall_status": "success|partial|failed",
    "message": "Summary message",
    "execution_time": "0.234 seconds",
    "summary": {
        "total_users": 4,
        "success": 4,
        "failed": 0,
        "skipped": 0
    },
    "details": [
        {
            "username": "admin_jagapadi",
            "role": "admin",
            "permissions": "create, read, update, delete",
            "status": "success",
            "message": "User created successfully"
        }
    ],
    "timestamp": "2025-12-01 10:00:00"
}
```

## 🧪 Testing

### Run Unit Tests

```bash
php scripts/test_user_seeder.php
```

### Test Coverage

1. **Password Encryption Test**
   - Hash creation
   - Bcrypt algorithm verification
   - Password verification
   - Wrong password rejection
   - Random salt verification

2. **Input Validation Test**
   - Empty username rejection
   - Short username rejection
   - Valid user acceptance

3. **Duplicate Detection Test**
   - Query execution
   - Existing user detection

4. **Password Minimum Length Test**
   - Short password rejection
   - Minimum length acceptance

5. **Username Format Test**
   - Special characters rejection
   - Spaces rejection
   - Underscore acceptance

6. **Email Validation Test**
   - Invalid email rejection
   - Valid email acceptance

7. **Role Validation Test**
   - Invalid role rejection
   - Valid roles acceptance

### Manual Testing

#### Test 1: Run Seeder First Time
```bash
php scripts/seed_users.php
# Expected: 4 users created successfully
```

#### Test 2: Run Seeder Again (Duplicate Test)
```bash
php scripts/seed_users.php
# Expected: 4 users skipped (already exist)
```

#### Test 3: Login Test
```bash
# Try login with created users
# Username: admin_jagapadi
# Password: admin123
```

## 🐛 Troubleshooting

### Issue 1: Database Connection Failed

**Error:**
```
[ERROR] [SYSTEM] [FAILED] Database connection failed after 3 attempts
```

**Solution:**
1. Check database is running:
   ```bash
   # MySQL
   sudo systemctl status mysql
   
   # MariaDB
   sudo systemctl status mariadb
   ```

2. Verify credentials in `config/database.php`

3. Test connection manually:
   ```bash
   mysql -u root -p -e "SELECT 1"
   ```

### Issue 2: Permission Denied

**Error:**
```
[ERROR] Cannot write to log file
```

**Solution:**
```bash
# Create logs directory
mkdir -p logs

# Set permissions
chmod 777 logs
```

### Issue 3: PDO Extension Not Found

**Error:**
```
Fatal error: Class 'PDO' not found
```

**Solution:**
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql

# CentOS/RHEL
sudo yum install php-mysql

# Restart web server
sudo systemctl restart apache2
```

### Issue 4: Users Already Exist

**Output:**
```
[WARNING] [admin_jagapadi] [SKIPPED] Username already exists
```

**Solution:**
This is normal behavior. If you want to recreate users:

```sql
-- Delete existing users
DELETE FROM users WHERE username IN ('admin_jagapadi', 'operator1', 'viewer1', 'petugas');

-- Then run seeder again
php scripts/seed_users.php
```

### Issue 5: Transaction Rollback

**Error:**
```
[ERROR] [SYSTEM] [ROLLBACK] Transaction rolled back
```

**Solution:**
1. Check error message in log
2. Fix the issue (usually validation or constraint)
3. Run seeder again

## 📁 Log Files

### Log File Locations

```
logs/
├── user_seeder.log                          # Main log file
├── user_seeder_report_2025-12-01_100000.json  # Execution report
└── test_results_2025-12-01_100000.json      # Test results
```

### View Logs

```bash
# View main log
tail -f logs/user_seeder.log

# View latest report
cat logs/user_seeder_report_*.json | jq .

# View test results
cat logs/test_results_*.json | jq .
```

### Clear Logs

```bash
# Clear all logs
rm -f logs/user_seeder*.log
rm -f logs/user_seeder_report_*.json
rm -f logs/test_results_*.json
```

## 📊 Performance

### Execution Time

Typical execution time:
- **Unit Tests**: ~0.1 seconds
- **Seeder (4 users)**: ~0.2-0.5 seconds
- **Seeder (duplicate check)**: ~0.1 seconds

### Database Impact

- **Queries per user**: 2 (check duplicate + insert)
- **Total queries**: 8 for 4 users
- **Transaction**: Single transaction for all inserts

## 🔐 Security Best Practices

1. **Never commit passwords** to version control
2. **Use environment variables** for sensitive data
3. **Change default passwords** after first login
4. **Enable 2FA** for admin accounts (if available)
5. **Regular password rotation** policy
6. **Monitor log files** for suspicious activity

## 📞 Support

Jika ada masalah:
1. Check log files di `logs/`
2. Run unit tests: `php scripts/test_user_seeder.php`
3. Verify database connection
4. Review error messages

## 📝 License

Internal use only - JAGAPADI Project

---

**Last Updated:** December 1, 2025  
**Version:** 1.0.0  
**Author:** Kiro AI Assistant
