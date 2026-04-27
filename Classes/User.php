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

            // Insert user
            $query = "INSERT INTO users (username, email, password, fullname, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [$username, $email, $hashed_password, $fullname, $role], "sssss");

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
                "SELECT id, username, email, password, fullname, role FROM users WHERE username = ? LIMIT 1",
                [$username],
                "s"
            );

            if (!$user) {
                throw new Exception("Username not found");
            }

            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid password");
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

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
}
