<?php

/**
 * User Class
 * 
 * Handles user authentication, registration, and profile management
 */

class User
{
    private $db;
    private $user_id;
    private $username;
    private $email;
    private $role;
    private $fullname;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Register a new user
     */
    public function register($username, $email, $password, $fullname, $role = 'staff')
    {
        try {
            // Check if username exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE username = ? LIMIT 1",
                [$username],
                "s"
            );

            if ($existing) {
                throw new Exception("Username already exists");
            }

            // Check if email exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ? LIMIT 1",
                [$email],
                "s"
            );

            if ($existing) {
                throw new Exception("Email already exists");
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Set status based on role
            // Youth users start as 'Pending' until SK Chairman verifies
            // All others start as 'Active'
            $status = ($role === 'youth') ? 'Pending' : 'Active';

            // Insert user
            $query = "INSERT INTO users (username, email, password, fullname, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [$username, $email, $hashed_password, $fullname, $role, $status], "ssssss");

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password)
    {
        try {
            $user = $this->db->fetchOne(
                "SELECT id, username, email, password, fullname, role, is_active, status, barangay, temp_password_required FROM users WHERE username = ? LIMIT 1",
                [$username],
                "s"
            );

            if (!$user) {
                throw new Exception("Username not found");
            }

            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid password");
            }

            if (isset($user['is_active']) && $user['is_active'] == 0) {
                throw new Exception("Your account has been deactivated. Please contact support.");
            }

            $role = $user['role'];
            $status = $user['status'] ?? 'Active';

            if (in_array($role, ['employer', 'training_provider']) && $status !== 'Active') {
                throw new Exception("Your account is pending approval or not yet active.");
            }

            if (in_array($role, ['sk_chairman', 'lydo']) && $status === 'Suspended') {
                throw new Exception("Your account is suspended. Please contact the system administrator.");
            }

            if ($role === 'youth' && $status === 'Pending') {
                throw new Exception("Your registration is pending approval from your SK Chairman. Please check back later.");
            }

            if ($role === 'youth' && $status === 'Declined') {
                throw new Exception("Your registration has been declined. Please contact your SK Chairman for next steps.");
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['status'] = $status;
            $_SESSION['barangay'] = $user['barangay'] ?? null;
            $_SESSION['temp_password_required'] = $user['temp_password_required'] ?? 0;

            $this->user_id = $user['id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->fullname = $user['fullname'];
            $this->role = $user['role'];

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'fullname' => $user['fullname'],
                    'role' => $user['role']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user info
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'fullname' => $_SESSION['fullname'],
            'role' => $_SESSION['role'],
            'email' => $_SESSION['email']
        ];
    }

    /**
     * Logout user
     */
    public function logout()
    {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile($user_id, $fullname, $email)
    {
        try {
            $query = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
            $this->db->execute($query, [$fullname, $email, $user_id], "ssi");

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        $query = "SELECT id, username, email, fullname, role, created_at FROM users ORDER BY created_at DESC";
        return $this->db->fetchAll($query);
    }

    /**
     * Check if user has permission
     */
    public function hasRole($role)
    {
        return isset($_SESSION['role']) && $_SESSION['role'] == $role;
    }

    /**
     * Change user password
     */
    public function changePassword($user_id, $current_password, $new_password)
    {
        try {
            $user = $this->db->fetchOne(
                "SELECT password FROM users WHERE id = ? LIMIT 1",
                [$user_id],
                "i"
            );

            if (!$user) {
                throw new Exception("User not found");
            }

            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }

            if (strlen($new_password) < 6) {
                throw new Exception("New password must be at least 6 characters");
            }

            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $this->db->execute($query, [$hashed, $user_id], "si");

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a new system user or provider account
     */
    public function createUser($data)
    {
        try {
            $required = ['username', 'email', 'fullname', 'role'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("{$field} is required");
                }
            }

            $existing = $this->db->fetchOne("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1", [$data['username'], $data['email']], "ss");
            if ($existing) {
                throw new Exception("Username or email already exists");
            }

            $role = $data['role'];
            $validRoles = ['admin', 'staff', 'manager', 'viewer', 'lydo', 'sk_chairman', 'youth', 'employer', 'training_provider'];
            if (!in_array($role, $validRoles, true)) {
                throw new Exception("Invalid role specified");
            }

            $passwordPlain = $data['password'] ?? bin2hex(random_bytes(6));
            $hashedPassword = password_hash($passwordPlain, PASSWORD_BCRYPT);
            $status = $data['status'] ?? 'Active';
            if (in_array($role, ['employer', 'training_provider'], true)) {
                $status = 'Pending';
            }

            $query = "INSERT INTO users (username, email, password, fullname, role, is_active, status, barangay, provider_type, provider_document_path, temp_password_required, approval_remark, created_by, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['fullname'],
                $role,
                $status,
                $data['barangay'] ?? null,
                $data['provider_type'] ?? null,
                $data['provider_document_path'] ?? null,
                $data['temp_password_required'] ?? 1,
                $data['approval_remark'] ?? null,
                $data['created_by'] ?? null
            ], "ssssssssssisi");

            $createdId = $this->db->lastInsertId();
            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $createdId,
                'id' => $createdId,
                'password' => $passwordPlain
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update provider approval status
     */
    public function approveProvider($user_id, $status = 'Active', $remark = null)
    {
        try {
            $user = $this->db->fetchOne("SELECT role FROM users WHERE id = ? LIMIT 1", [$user_id], "i");
            if (!$user || !in_array($user['role'], ['employer', 'training_provider'], true)) {
                throw new Exception("Provider account not found");
            }

            if (!in_array($status, ['Active', 'Pending', 'Declined', 'Suspended'], true)) {
                throw new Exception("Invalid status");
            }

            $query = "UPDATE users SET status = ?, approval_remark = ? WHERE id = ?";
            $this->db->execute($query, [$status, $remark, $user_id], "ssi");

            return [
                'success' => true,
                'message' => 'Provider approval status updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark a user record as requiring a password reset
     */
    public function setTempPasswordRequired($user_id, $required = true)
    {
        try {
            $query = "UPDATE users SET temp_password_required = ? WHERE id = ?";
            $this->db->execute($query, [$required ? 1 : 0, $user_id], "ii");

            return [
                'success' => true,
                'message' => 'Password reset flag updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role, $filters = [])
    {
        $query = "SELECT * FROM users WHERE role = ?";
        $params = [$role];
        $types = 's';

        if (isset($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['barangay'])) {
            $query .= " AND barangay = ?";
            $params[] = $filters['barangay'];
            $types .= 's';
        }

        if (isset($filters['approval_remark'])) {
            $query .= " AND approval_remark LIKE ?";
            $params[] = '%' . $filters['approval_remark'] . '%';
            $types .= 's';
        }

        $query .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($query, $params, $types);
    }
}
