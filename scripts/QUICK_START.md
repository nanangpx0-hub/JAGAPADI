# Quick Start - User Seeder

## 🚀 Quick Commands

### Linux/Mac

```bash
# Make script executable
chmod +x scripts/run_seeder.sh

# Run everything (tests + seeder)
./scripts/run_seeder.sh

# Run tests only
./scripts/run_seeder.sh --test

# Run seeder only
./scripts/run_seeder.sh --seed
```

### Windows

```cmd
# Run everything (tests + seeder)
scripts\run_seeder.bat

# Run tests only
scripts\run_seeder.bat test

# Run seeder only
scripts\run_seeder.bat seed
```

### Direct PHP

```bash
# Run tests
php scripts/test_user_seeder.php

# Run seeder
php scripts/seed_users.php
```

## 📋 Prerequisites Checklist

- [ ] PHP >= 7.4 installed
- [ ] MySQL/MariaDB running
- [ ] Database `jagapadi` created
- [ ] Table `users` exists
- [ ] Database credentials configured in `config/database.php`

## 👥 Default Users

After running seeder, you can login with:

| Username | Password | Role |
|----------|----------|------|
| admin_jagapadi | admin123 | admin |
| operator1 | op1test | operator |
| viewer1 | vw1test | viewer |
| petugas | petugas3509 | petugas |

## 🔍 Verify Installation

```bash
# Check users in database
mysql -u root -p jagapadi -e "SELECT username, role, status FROM users;"

# Or via PHP
php -r "
require 'config/database.php';
\$db = Database::getInstance()->getConnection();
\$stmt = \$db->query('SELECT username, role, status FROM users');
foreach (\$stmt->fetchAll(PDO::FETCH_ASSOC) as \$user) {
    echo \$user['username'] . ' - ' . \$user['role'] . ' - ' . \$user['status'] . PHP_EOL;
}
"
```

## 📁 Output Files

After running, check these files:

```
logs/
├── user_seeder.log                          # Execution log
├── user_seeder_report_YYYY-MM-DD_HHMMSS.json  # JSON report
└── test_results_YYYY-MM-DD_HHMMSS.json      # Test results
```

## 🐛 Common Issues

### Issue: Database connection failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u root -p -e "SELECT 1"
```

### Issue: Permission denied
```bash
# Create logs directory
mkdir -p logs
chmod 777 logs
```

### Issue: Users already exist
```sql
-- Delete existing users
DELETE FROM users WHERE username IN ('admin_jagapadi', 'operator1', 'viewer1', 'petugas');
```

## 📚 Full Documentation

For complete documentation, see [README.md](README.md)

---

**Quick Start Guide** | **Version 1.0.0**
