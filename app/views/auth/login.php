<?php
// Load Security class for CSRF token
require_once ROOT_PATH . '/app/core/Security.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - JAGAPADI</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 450px;
            max-width: 95%;
        }
        .login-logo a {
            color: white;
            font-size: 35px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            font-weight: 600;
            padding: 10px;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
        }
        .app-description {
            color: white;
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        /* User Roles Info Styling */
        .user-roles-info {
            max-height: 400px;
            overflow-y: auto;
            padding: 5px;
        }
        
        .user-roles-info::-webkit-scrollbar {
            width: 6px;
        }
        
        .user-roles-info::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .user-roles-info::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .user-roles-info::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .role-card {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 5px;
            padding: 10px;
            transition: all 0.3s ease;
        }
        
        .role-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .role-card:nth-child(2) {
            border-left-color: #dc3545;
        }
        
        .role-card:nth-child(3) {
            border-left-color: #ffc107;
        }
        
        .role-card:nth-child(4) {
            border-left-color: #17a2b8;
        }
        
        .role-card:nth-child(5) {
            border-left-color: #28a745;
        }
        
        .role-header {
            font-size: 14px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .role-header i {
            font-size: 16px;
        }
        
        .role-details {
            font-size: 12px;
        }
        
        .role-login {
            margin-bottom: 5px;
            padding: 5px;
            background: white;
            border-radius: 3px;
        }
        
        .role-login code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            color: #495057;
        }
        
        .role-permissions {
            padding: 5px;
        }
        
        .role-permissions .badge {
            font-size: 10px;
            padding: 3px 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 576px) {
            .login-box {
                width: 100%;
                margin: 10px;
            }
            
            .login-logo a {
                font-size: 28px;
            }
            
            .role-card {
                padding: 8px;
            }
            
            .role-header {
                font-size: 13px;
            }
            
            .role-details {
                font-size: 11px;
            }
            
            .user-roles-info {
                max-height: 300px;
            }
        }
        
        /* Print Styles */
        @media print {
            .login-page {
                background: white;
            }
            
            .btn-success {
                display: none;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="#"><i class="fas fa-leaf"></i> <b>JAGAPADI</b></a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg"><strong>Jember Agrikultur Gapai Prestasi Digital</strong></p>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>auth/login" method="post">
                <?php 
                // Generate CSRF token if not exists
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = Security::generateCsrfToken();
                }
                ?>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </div>
            </form>

            <hr>
            
            <!-- User Roles & Permissions Information -->
            <div class="user-roles-info">
                <div class="text-center mb-3">
                    <strong class="text-primary">
                        <i class="fas fa-users"></i> Akun Pengguna & Hak Akses
                    </strong>
                </div>
                
                <!-- Administrator -->
                <div class="role-card mb-2">
                    <div class="role-header">
                        <i class="fas fa-user-shield text-danger"></i>
                        <strong>Administrator</strong>
                    </div>
                    <div class="role-details">
                        <div class="role-login">
                            <i class="fas fa-user"></i> <code>admin_jagapadi</code>
                            <i class="fas fa-key ml-2"></i> <code>admin123</code>
                        </div>
                        <div class="role-permissions">
                            <span class="badge badge-danger">Full Access</span>
                            <small class="d-block text-muted mt-1">
                                Create, Read, Update, Delete semua data
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Operator -->
                <div class="role-card mb-2">
                    <div class="role-header">
                        <i class="fas fa-user-cog text-warning"></i>
                        <strong>Operator</strong>
                    </div>
                    <div class="role-details">
                        <div class="role-login">
                            <i class="fas fa-user"></i> <code>operator1</code>
                            <i class="fas fa-key ml-2"></i> <code>op1test</code>
                        </div>
                        <div class="role-permissions">
                            <span class="badge badge-warning">Create, Read, Update</span>
                            <small class="d-block text-muted mt-1">
                                Tidak dapat menghapus data
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Viewer -->
                <div class="role-card mb-2">
                    <div class="role-header">
                        <i class="fas fa-eye text-info"></i>
                        <strong>Viewer</strong>
                    </div>
                    <div class="role-details">
                        <div class="role-login">
                            <i class="fas fa-user"></i> <code>viewer1</code>
                            <i class="fas fa-key ml-2"></i> <code>vw1test</code>
                        </div>
                        <div class="role-permissions">
                            <span class="badge badge-info">Read Only</span>
                            <small class="d-block text-muted mt-1">
                                Hanya dapat melihat data
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Petugas -->
                <div class="role-card mb-2">
                    <div class="role-header">
                        <i class="fas fa-user-tie text-success"></i>
                        <strong>Petugas Lapangan</strong>
                    </div>
                    <div class="role-details">
                        <div class="role-login">
                            <i class="fas fa-user"></i> <code>petugas</code>
                            <i class="fas fa-key ml-2"></i> <code>petugas3509</code>
                        </div>
                        <div class="role-permissions">
                            <span class="badge badge-success">Create, Read</span>
                            <small class="d-block text-muted mt-1">
                                Tidak dapat update atau delete
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-description">
        <p>Sistem Pelaporan Fenomena Pertanian<br>BPS Kabupaten Jember</p>
        <p><small>&copy; 2025 Nanang Pamungkas</small></p>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
