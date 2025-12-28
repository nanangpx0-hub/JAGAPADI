<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - JAGAPADI</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .password-requirements h6 {
            color: #495057;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .password-strength {
            margin-top: 10px;
        }
        .strength-bar {
            height: 5px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }
        .strength-weak { background: #dc3545; }
        .strength-fair { background: #fd7e14; }
        .strength-good { background: #ffc107; }
        .strength-strong { background: #28a745; }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .input-group-password {
            position: relative;
        }
        .force-change-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        .force-change-notice i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="<?= BASE_URL ?>"><b>JAGA</b>PADI</a>
        </div>
        
        <div class="card">
            <div class="card-body login-card-body">
                <?php if ($data['is_force_change']): ?>
                    <div class="force-change-notice">
                        <i class="fas fa-shield-alt"></i>
                        <h5>Ganti Password Wajib</h5>
                        <p class="mb-0">Untuk keamanan akun Anda, silakan ganti password sebelum melanjutkan.</p>
                        <?php if ($data['user_data']): ?>
                            <small>Selamat datang, <strong><?= htmlspecialchars($data['user_data']['nama_lengkap']) ?></strong></small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="login-box-msg">
                        <i class="fas fa-key"></i> Ganti Password
                    </p>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" id="changePasswordForm">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    
                    <?php if (!$data['is_force_change']): ?>
                        <div class="input-group mb-3">
                            <div class="input-group-password">
                                <input type="password" class="form-control" name="current_password" 
                                       placeholder="Password Lama" required>
                                <span class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="input-group mb-3">
                        <div class="input-group-password">
                            <input type="password" class="form-control" name="new_password" id="new_password"
                                   placeholder="Password Baru" required>
                            <span class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-key"></span>
                            </div>
                        </div>
                    </div>

                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthBar"></div>
                        </div>
                        <small id="strengthText" class="text-muted">Masukkan password baru</small>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-password">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password"
                                   placeholder="Konfirmasi Password Baru" required>
                            <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-check"></span>
                            </div>
                        </div>
                    </div>

                    <div class="password-requirements">
                        <h6><i class="fas fa-info-circle"></i> Persyaratan Password:</h6>
                        <ul>
                            <li>Minimal 8 karakter</li>
                            <li>Mengandung huruf besar (A-Z)</li>
                            <li>Mengandung huruf kecil (a-z)</li>
                            <li>Mengandung angka (0-9)</li>
                            <li>Mengandung karakter khusus (!@#$%^&*)</li>
                            <li>Berbeda dari password lama</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                <i class="fas fa-save"></i> 
                                <?= $data['is_force_change'] ? 'Ganti Password & Lanjutkan' : 'Ganti Password' ?>
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!$data['is_force_change']): ?>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/dashboard" class="text-muted">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/js/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/js/adminlte.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePassword(fieldName) {
            const field = document.querySelector(`input[name="${fieldName}"]`);
            const icon = field.parentElement.querySelector('.password-toggle i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) score += 1;
            else feedback.push('minimal 8 karakter');

            // Uppercase check
            if (/[A-Z]/.test(password)) score += 1;
            else feedback.push('huruf besar');

            // Lowercase check
            if (/[a-z]/.test(password)) score += 1;
            else feedback.push('huruf kecil');

            // Number check
            if (/[0-9]/.test(password)) score += 1;
            else feedback.push('angka');

            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            else feedback.push('karakter khusus');

            return { score, feedback };
        }

        // Update password strength indicator
        function updatePasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'strength-fill';
                strengthText.textContent = 'Masukkan password baru';
                strengthText.className = 'text-muted';
                return;
            }

            const { score, feedback } = checkPasswordStrength(password);
            const percentage = (score / 5) * 100;

            strengthBar.style.width = percentage + '%';

            if (score <= 2) {
                strengthBar.className = 'strength-fill strength-weak';
                strengthText.textContent = 'Lemah - Perlu: ' + feedback.join(', ');
                strengthText.className = 'text-danger';
            } else if (score === 3) {
                strengthBar.className = 'strength-fill strength-fair';
                strengthText.textContent = 'Cukup - Perlu: ' + feedback.join(', ');
                strengthText.className = 'text-warning';
            } else if (score === 4) {
                strengthBar.className = 'strength-fill strength-good';
                strengthText.textContent = 'Baik - Perlu: ' + feedback.join(', ');
                strengthText.className = 'text-info';
            } else {
                strengthBar.className = 'strength-fill strength-strong';
                strengthText.textContent = 'Kuat - Password memenuhi semua persyaratan';
                strengthText.className = 'text-success';
            }
        }

        // Password confirmation checker
        function checkPasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');

            if (confirmPassword.length > 0) {
                if (newPassword === confirmPassword) {
                    document.getElementById('confirm_password').classList.remove('is-invalid');
                    document.getElementById('confirm_password').classList.add('is-valid');
                } else {
                    document.getElementById('confirm_password').classList.remove('is-valid');
                    document.getElementById('confirm_password').classList.add('is-invalid');
                }
            } else {
                document.getElementById('confirm_password').classList.remove('is-valid', 'is-invalid');
            }

            // Enable/disable submit button
            const { score } = checkPasswordStrength(newPassword);
            const passwordsMatch = newPassword === confirmPassword && confirmPassword.length > 0;
            const isValid = score >= 5 && passwordsMatch;

            submitBtn.disabled = !isValid;
            if (isValid) {
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
            } else {
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }
        }

        // Event listeners
        document.getElementById('new_password').addEventListener('input', function() {
            updatePasswordStrength();
            checkPasswordMatch();
        });

        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

        // Form submission
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }

            const { score } = checkPasswordStrength(newPassword);
            if (score < 5) {
                e.preventDefault();
                alert('Password belum memenuhi semua persyaratan keamanan!');
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        });

        // Initialize
        updatePasswordStrength();
        checkPasswordMatch();
    </script>
</body>
</html>