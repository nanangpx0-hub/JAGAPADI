<?php

require_once ROOT_PATH . '/app/controllers/Api/BaseApiController.php';
require_once ROOT_PATH . '/app/models/User.php';

class UserController extends BaseApiController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get all users with pagination and filters
     * GET /api/users
     */
    public function index() {
        try {
            // Only admin can access all users
            $this->checkPermission('admin');
            
            $pagination = $this->getPaginationParams();
            
            // Get filters
            $filters = [
                'role' => $_GET['role'] ?? null,
                'aktif' => $_GET['aktif'] ?? null,
                'search' => $_GET['search'] ?? null
            ];
            
            // Remove null filters
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Get data
            $users = $this->userModel->getAllWithFilters($filters, $pagination['limit'], $pagination['offset']);
            $total = $this->userModel->getCountWithFilters($filters);
            
            // Remove sensitive data
            $users = array_map(function($user) {
                unset($user['password']);
                return $user;
            }, $users);
            
            // Format response
            $response = $this->formatPaginatedResponse($users, $total, $pagination['page'], $pagination['limit']);
            
            $this->sendResponse($response, 'Users retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get specific user by ID
     * GET /api/users/{id}
     */
    public function show($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid user ID', 400);
            }
            
            // Users can only view their own profile, admin can view all
            if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $id) {
                $this->sendError('Forbidden', 403);
            }
            
            $user = $this->userModel->getById($id);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            // Remove sensitive data
            unset($user['password']);
            
            $this->sendResponse($user, 'User retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Create new user
     * POST /api/users
     */
    public function store() {
        try {
            // Only admin can create users
            $this->checkPermission('admin');
            
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate required fields
            $requiredFields = ['username', 'password', 'nama_lengkap', 'email', 'role'];
            $errors = $this->validateRequired($data, $requiredFields);
            
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Validate role
            $validRoles = ['admin', 'operator', 'petugas'];
            if (!in_array($data['role'], $validRoles)) {
                $this->sendError('Invalid role. Must be one of: ' . implode(', ', $validRoles), 400);
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError('Invalid email format', 400);
            }
            
            // Check if username already exists
            if ($this->userModel->getUserByUsername($data['username'])) {
                $this->sendError('Username already exists', 409);
            }
            
            // Check if email already exists
            if ($this->userModel->getUserByEmail($data['email'])) {
                $this->sendError('Email already exists', 409);
            }
            
            // Set default values
            $data['aktif'] = $data['aktif'] ?? 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $userId = $this->userModel->createUser($data);
            
            if ($userId) {
                $user = $this->userModel->getById($userId);
                unset($user['password']);
                $this->sendResponse($user, 'User created successfully', 201);
            } else {
                $this->sendError('Failed to create user', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to create user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update user
     * PUT /api/users/{id}
     */
    public function update($id) {
        try {
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid user ID', 400);
            }
            
            $existingUser = $this->userModel->getById($id);
            if (!$existingUser) {
                $this->sendError('User not found', 404);
            }
            
            // Users can only update their own profile, admin can update all
            if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $id) {
                $this->sendError('Forbidden', 403);
            }
            
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Validate email format if provided
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError('Invalid email format', 400);
            }
            
            // Check if username already exists (excluding current user)
            if (isset($data['username'])) {
                $existingUsername = $this->userModel->getUserByUsername($data['username']);
                if ($existingUsername && $existingUsername['id'] != $id) {
                    $this->sendError('Username already exists', 409);
                }
            }
            
            // Check if email already exists (excluding current user)
            if (isset($data['email'])) {
                $existingEmail = $this->userModel->getUserByEmail($data['email']);
                if ($existingEmail && $existingEmail['id'] != $id) {
                    $this->sendError('Email already exists', 409);
                }
            }
            
            // Validate role if provided (admin only)
            if (isset($data['role'])) {
                if ($_SESSION['role'] !== 'admin') {
                    $this->sendError('Only admin can change user roles', 403);
                }
                
                $validRoles = ['admin', 'operator', 'petugas'];
                if (!in_array($data['role'], $validRoles)) {
                    $this->sendError('Invalid role. Must be one of: ' . implode(', ', $validRoles), 400);
                }
            }
            
            // Hash password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']); // Don't update password if not provided
            }
            
            // Set updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $success = $this->userModel->updateUser($id, $data);
            
            if ($success) {
                $user = $this->userModel->getById($id);
                unset($user['password']);
                $this->sendResponse($user, 'User updated successfully');
            } else {
                $this->sendError('Failed to update user', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to update user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete user
     * DELETE /api/users/{id}
     */
    public function destroy($id) {
        try {
            // Only admin can delete users
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid user ID', 400);
            }
            
            $existingUser = $this->userModel->getById($id);
            if (!$existingUser) {
                $this->sendError('User not found', 404);
            }
            
            // Prevent admin from deleting themselves
            if ($_SESSION['user_id'] == $id) {
                $this->sendError('Cannot delete your own account', 400);
            }
            
            $success = $this->userModel->deleteUser($id);
            
            if ($success) {
                $this->sendResponse(null, 'User deleted successfully');
            } else {
                $this->sendError('Failed to delete user', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Toggle user status
     * POST /api/users/{id}/toggle-status
     */
    public function toggleStatus($id) {
        try {
            // Only admin can toggle user status
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid user ID', 400);
            }
            
            $existingUser = $this->userModel->getById($id);
            if (!$existingUser) {
                $this->sendError('User not found', 404);
            }
            
            // Prevent admin from deactivating themselves
            if ($_SESSION['user_id'] == $id) {
                $this->sendError('Cannot change your own status', 400);
            }
            
            $newStatus = $existingUser['aktif'] ? 0 : 1;
            $success = $this->userModel->toggleStatus($id);
            
            if ($success) {
                $user = $this->userModel->getById($id);
                unset($user['password']);
                
                $statusText = $newStatus ? 'activated' : 'deactivated';
                $this->sendResponse($user, "User {$statusText} successfully");
            } else {
                $this->sendError('Failed to toggle user status', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to toggle user status: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get current user profile
     * GET /api/users/profile
     */
    public function getProfile() {
        try {
            $user = $this->userModel->getById($_SESSION['user_id']);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            // Remove sensitive data
            unset($user['password']);
            
            $this->sendResponse($user, 'Profile retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update current user profile
     * PUT /api/users/profile
     */
    public function updateProfile() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            // Users can only update certain fields in their profile
            $allowedFields = ['nama_lengkap', 'email', 'no_telepon', 'alamat'];
            $data = array_intersect_key($data, array_flip($allowedFields));
            
            // Validate email format if provided
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError('Invalid email format', 400);
            }
            
            // Check if email already exists (excluding current user)
            if (isset($data['email'])) {
                $existingEmail = $this->userModel->getUserByEmail($data['email']);
                if ($existingEmail && $existingEmail['id'] != $_SESSION['user_id']) {
                    $this->sendError('Email already exists', 409);
                }
            }
            
            // Set updated timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $success = $this->userModel->updateUser($_SESSION['user_id'], $data);
            
            if ($success) {
                $user = $this->userModel->getById($_SESSION['user_id']);
                unset($user['password']);
                $this->sendResponse($user, 'Profile updated successfully');
            } else {
                $this->sendError('Failed to update profile', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Change password
     * POST /api/users/change-password
     */
    public function changePassword() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            $requiredFields = ['current_password', 'new_password', 'confirm_password'];
            $errors = $this->validateRequired($data, $requiredFields);
            
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Verify current password
            $user = $this->userModel->getById($_SESSION['user_id']);
            if (!password_verify($data['current_password'], $user['password'])) {
                $this->sendError('Current password is incorrect', 400);
            }
            
            // Validate new password confirmation
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->sendError('New password confirmation does not match', 400);
            }
            
            // Use the model's changePassword method for validation and update
            $result = $this->userModel->changePassword(
                $_SESSION['user_id'], 
                $data['current_password'], 
                $data['new_password']
            );
            
            if ($result['success']) {
                $this->sendResponse(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to change password: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Force password change (for first login)
     * POST /api/users/force-change-password
     */
    public function forceChangePassword() {
        try {
            $data = $this->getRequestData();
            $data = $this->sanitizeData($data);
            
            $requiredFields = ['new_password', 'confirm_password'];
            $errors = $this->validateRequired($data, $requiredFields);
            
            if (!empty($errors)) {
                $this->sendError('Validation failed', 422, $errors);
            }
            
            // Validate new password confirmation
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->sendError('New password confirmation does not match', 400);
            }
            
            // Check if user needs password change
            $userId = $_SESSION['user_needs_password_change'] ?? $_SESSION['user_id'];
            $user = $this->userModel->getById($userId);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            // Use the model's changePassword method (skip current password verification)
            $result = $this->userModel->changePassword($userId, null, $data['new_password']);
            
            if ($result['success']) {
                // If this was a force change, complete the login process
                if (isset($_SESSION['user_needs_password_change'])) {
                    $tempUserData = $_SESSION['temp_user_data'] ?? null;
                    
                    if ($tempUserData) {
                        // Complete login
                        $_SESSION['user_id'] = $tempUserData['id'];
                        $_SESSION['username'] = $tempUserData['username'];
                        $_SESSION['role'] = $tempUserData['role'];
                        $_SESSION['nama_lengkap'] = $tempUserData['nama_lengkap'];
                        
                        // Clean up temporary session data
                        unset($_SESSION['user_needs_password_change']);
                        unset($_SESSION['temp_user_data']);
                    }
                }
                
                $this->sendResponse([
                    'login_completed' => isset($tempUserData),
                    'user' => $tempUserData ?? null
                ], $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to change password: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Check if user needs password change
     * GET /api/users/check-password-change
     */
    public function checkPasswordChange() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                $this->sendError('User not authenticated', 401);
            }
            
            $user = $this->userModel->getById($userId);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            $needsChange = $user['must_change_password'] == 1;
            $passwordExpiry = $this->userModel->checkPasswordExpiry($userId);
            
            $this->sendResponse([
                'must_change_password' => $needsChange,
                'password_expiry' => $passwordExpiry,
                'last_password_change' => $user['last_password_change_at']
            ], 'Password change status retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to check password change status: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Set user to require password change (admin only)
     * POST /api/users/{id}/force-password-change
     */
    public function setForcePasswordChange($id) {
        try {
            // Only admin can force password change for other users
            $this->checkPermission('admin');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Invalid user ID', 400);
            }
            
            $user = $this->userModel->getById($id);
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            $success = $this->userModel->setMustChangePassword($id, true);
            
            if ($success) {
                $this->sendResponse(null, 'User will be required to change password on next login');
            } else {
                $this->sendError('Failed to set password change requirement', 500);
            }
            
        } catch (Exception $e) {
            $this->sendError('Failed to set password change requirement: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get users needing password change (admin only)
     * GET /api/users/needing-password-change
     */
    public function getUsersNeedingPasswordChange() {
        try {
            // Only admin can view this information
            $this->checkPermission('admin');
            
            $users = $this->userModel->getUsersNeedingPasswordChange();
            
            $this->sendResponse($users, 'Users needing password change retrieved successfully');
            
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve users needing password change: ' . $e->getMessage(), 500);
        }
    }
}