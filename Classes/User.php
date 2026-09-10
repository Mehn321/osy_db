<?php

/**
 * User Class
 * 
 * Handles user authentication, registration, and profile management
 */

class User
{
    private $db;
    private const PASSWORD_MIN_LENGTH = 12;
    private const OTP_EXPIRY_SECONDS = 600;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;
    private $user_id;
    private $username;
    private $email;
    private $role;
    private $fullname;

    private $commonWeakPasswords = [
        'password', 'password123', '123456', '12345678', '123456789',
        'qwerty', 'qwerty123', 'admin', 'admin123', 'letmein',
        'welcome', 'iloveyou', 'abc123', '000000', '111111',
        '123123', 'passw0rd', 'p@ssw0rd'
    ];

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Register a new user
     */
    public function register($username, $email, $password, $fullname, $role = 'youth')
    {
        try {
            $passwordValidation = $this->validateStrongPassword($password, [
                'username' => $username,
                'email' => $email,
                'fullname' => $fullname,
            ]);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }

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

            $createdId = (int) $this->db->lastInsertId();
            $this->rememberPassword($createdId, $hashed_password);

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $createdId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function beginSignupVerification($userId, $email, $phone)
    {
        try {
            $userId = (int) $userId;
            if ($userId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || trim($phone) === '') {
                throw new Exception('A valid email address and phone number are required.');
            }

            foreach (['email', 'phone'] as $channel) {
                $code = (string) random_int(100000, 999999);
                $this->db->execute(
                    "UPDATE user_2fa_codes SET consumed_at = NOW() WHERE user_id = ? AND purpose = 'signup' AND channel = ? AND consumed_at IS NULL",
                    [$userId, $channel], 'is'
                );
                $this->db->execute(
                    "INSERT INTO user_2fa_codes (user_id, channel, purpose, otp_hash, expires_at, attempts, created_at) VALUES (?, ?, 'signup', ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 0, NOW())",
                    [$userId, $channel, password_hash($code, PASSWORD_BCRYPT), self::OTP_EXPIRY_SECONDS], 'issi'
                );

                if ($channel === 'email') {
                    require_once __DIR__ . '/EmailService.php';
                    $emailService = new EmailService($this->db);
                    $body = $emailService->buildStyledEmail(
                        'Complete Your Registration',
                        '<p>Your email verification code is:</p><p style="font-size:30px;font-weight:800;letter-spacing:4px;color:#1d4ed8;">' . htmlspecialchars($code) . '</p><p>This code expires in 10 minutes.</p>'
                    );
                    $result = $emailService->send($email, 'Your Registration Verification Code', $body);
                } else {
                    require_once __DIR__ . '/SmsService.php';
                    $sms = new SmsService($this->db);
                    $result = $sms->send($phone, 'Your registration verification code is ' . $code . '. It expires in 10 minutes.');
                }
                if (empty($result['success'])) {
                    throw new Exception('Unable to send the ' . $channel . ' verification code. Please try again later.');
                }
            }

            $_SESSION['pending_signup_verification'] = ['user_id' => $userId, 'created_at' => time()];
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function verifySignupOtp($channel, $otp)
    {
        try {
            $pending = $_SESSION['pending_signup_verification'] ?? [];
            $userId = (int) ($pending['user_id'] ?? 0);
            if ($userId <= 0 || !in_array($channel, ['email', 'phone'], true)) {
                throw new Exception('Your signup verification session has expired. Please register again.');
            }
            if (!preg_match('/^\d{6}$/', trim($otp))) {
                throw new Exception('Enter a valid 6-digit verification code.');
            }
            $row = $this->db->fetchOne(
                "SELECT id, otp_hash, expires_at, attempts FROM user_2fa_codes WHERE user_id = ? AND purpose = 'signup' AND channel = ? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1",
                [$userId, $channel], 'is'
            );
            if (!$row || strtotime($row['expires_at']) < time()) {
                throw new Exception('That verification code has expired. Please register again.');
            }
            if ((int) $row['attempts'] >= self::OTP_MAX_ATTEMPTS || !password_verify(trim($otp), $row['otp_hash'])) {
                $this->db->execute("UPDATE user_2fa_codes SET attempts = attempts + 1 WHERE id = ?", [(int) $row['id']], 'i');
                throw new Exception('Invalid verification code.');
            }
            $this->db->execute("UPDATE user_2fa_codes SET consumed_at = NOW() WHERE id = ?", [(int) $row['id']], 'i');
            $column = $channel === 'email' ? 'email_verified_at' : 'phone_verified_at';
            $this->db->execute("UPDATE users SET {$column} = NOW() WHERE id = ?", [$userId], 'i');
            $verified = $this->db->fetchOne("SELECT email_verified_at, phone_verified_at FROM users WHERE id = ?", [$userId], 'i');
            if (!empty($verified['email_verified_at']) && !empty($verified['phone_verified_at'])) {
                unset($_SESSION['pending_signup_verification']);
                return ['success' => true, 'complete' => true];
            }
            return ['success' => true, 'complete' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password, $allowedRoles = null)
    {
        try {
            $normalizedUsername = strtolower(trim($username));
            $attemptKey = 'login_' . md5($normalizedUsername . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
            $now = time();
            $lockUntil = $_SESSION['login_lockout_until'][$attemptKey] ?? 0;

            if ($lockUntil > $now) {
                throw new Exception('Too many failed login attempts. Please wait 15 minutes before trying again.');
            }

            if ($lockUntil > 0) {
                unset($_SESSION['login_lockout_until'][$attemptKey]);
                unset($_SESSION['login_attempts'][$attemptKey]);
            }

            $user = $this->db->fetchOne(
                "SELECT id, username, email, password, fullname, role, is_active, status, barangay, temp_password_required FROM users WHERE username = ? LIMIT 1",
                [$username],
                "s"
            );

            if (!$user) {
                throw new Exception("Username not found");
            }

            if (!password_verify($password, $user['password'])) {
                $attempts = $_SESSION['login_attempts'][$attemptKey] ?? 0;
                $attempts++;
                $_SESSION['login_attempts'][$attemptKey] = $attempts;

                $this->recordLoginEvent((int) $user['id'], 'failed', 'invalid_password');

                if ($attempts >= 5) {
                    $_SESSION['login_lockout_until'][$attemptKey] = $now + 900;
                    $this->sendActivityAlertEmail(
                        $user,
                        'Security Alert: Login Locked',
                        'Your account had multiple failed login attempts and has been temporarily locked for 15 minutes.'
                    );
                    throw new Exception('Too many failed login attempts. Please wait 15 minutes before trying again.');
                }

                throw new Exception("Invalid password");
            }

            if (isset($user['is_active']) && $user['is_active'] == 0) {
                throw new Exception("Your account has been deactivated. Please contact support.");
            }

            $role = $user['role'];
            // Auto-convert legacy admin role to lydo
            if ($role === 'admin') {
                $role = 'lydo';
                $this->db->execute("UPDATE users SET role = 'lydo' WHERE id = ?", [$user['id']], "i");
            }
            $status = $user['status'] ?? 'Active';

            if ($allowedRoles !== null) {
                if (is_string($allowedRoles)) {
                    $allowedRoles = [$allowedRoles];
                }

                if (!in_array($role, $allowedRoles, true)) {
                    throw new Exception('This account is not allowed to sign in on this page.');
                }
            }

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

            if ($role === 'youth' && $status === 'Action Required') {
                throw new Exception("Your registration requires action. Please review your profile or contact your SK Chairman.");
            }

            if ($status !== 'Active') {
                throw new Exception("Your account is not active. Please contact the system administrator.");
            }

            unset($_SESSION['login_attempts'][$attemptKey]);
            unset($_SESSION['login_lockout_until'][$attemptKey]);

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000);
            $isNewDevice = !$this->isKnownLoginContext((int) $user['id'], $ipAddress, $userAgent);

            if (!$isNewDevice) {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $role;
                $_SESSION['email'] = $user['email'];
                $_SESSION['status'] = $status;
                $_SESSION['barangay'] = $user['barangay'] ?? null;
                $_SESSION['temp_password_required'] = (int) ($user['temp_password_required'] ?? 0);

                $this->user_id = (int) $user['id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->fullname = $user['fullname'];
                $this->role = $role;

                $this->recordLoginEvent((int) $user['id'], 'success', 'direct_login');

                return [
                    'success' => true,
                    'requires_otp' => false,
                    'message' => 'Login successful.',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'fullname' => $user['fullname'],
                        'role' => $role
                    ]
                ];
            }

            $pendingUser = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'role' => $role,
                'status' => $status,
                'barangay' => $user['barangay'] ?? null,
                'temp_password_required' => (int) ($user['temp_password_required'] ?? 0),
                'is_new_device' => $isNewDevice,
            ];

            $otpResult = $this->startEmailOtpLogin($pendingUser);
            if (!$otpResult['success']) {
                throw new Exception($otpResult['message']);
            }

            return [
                'success' => true,
                'requires_otp' => true,
                'message' => 'A verification code has been sent to your email address.',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'fullname' => $user['fullname'],
                    'role' => $role
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
            $existing = $this->db->fetchOne(
                "SELECT email, fullname FROM users WHERE id = ? LIMIT 1",
                [$user_id],
                "i"
            );

            $query = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
            $this->db->execute($query, [$fullname, $email, $user_id], "ssi");

            if ($existing && isset($existing['email']) && strcasecmp((string) $existing['email'], (string) $email) !== 0) {
                $this->sendActivityAlertEmail(
                    [
                        'id' => (int) $user_id,
                        'email' => (string) $email,
                        'fullname' => (string) $fullname,
                    ],
                    'Security Alert: Email Address Changed',
                    'Your account email address was changed. If this was not you, please secure your account immediately.'
                );
            }

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
                "SELECT username, email, fullname, password FROM users WHERE id = ? LIMIT 1",
                [$user_id],
                "i"
            );

            if (!$user) {
                throw new Exception("User not found");
            }

            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }

            $passwordValidation = $this->validateStrongPassword($new_password, [
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? '',
                'fullname' => $user['fullname'] ?? '',
            ]);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }

            if ($this->isPasswordReused((int) $user_id, $new_password)) {
                throw new Exception('You cannot reuse any of your last 5 passwords.');
            }

            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE users SET password = ?, temp_password_required = 0 WHERE id = ?";
            $this->db->execute($query, [$hashed, $user_id], "si");
            $this->rememberPassword((int) $user_id, $hashed);
            $this->db->execute(
                "INSERT INTO user_security_settings (user_id, two_factor_enabled, alert_email_enabled, last_password_changed_at, updated_at)
                 VALUES (?, 1, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE two_factor_enabled = 1, last_password_changed_at = NOW(), updated_at = NOW()",
                [(int) $user_id],
                'i'
            );

            $this->sendActivityAlertEmail(
                [
                    'id' => (int) $user_id,
                    'email' => $user['email'] ?? '',
                    'fullname' => $user['fullname'] ?? $user['username'] ?? 'User',
                ],
                'Security Alert: Password Changed',
                'Your account password was changed. If this was not you, please contact support immediately.'
            );

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
            $validRoles = ['lydo', 'sk_chairman', 'youth', 'employer', 'training_provider'];
            if (!in_array($role, $validRoles, true)) {
                throw new Exception("Invalid role specified");
            }

            $passwordPlain = $data['password'] ?? $this->generateStrongTemporaryPassword();
            $passwordValidation = $this->validateStrongPassword($passwordPlain, [
                'username' => $data['username'] ?? '',
                'email' => $data['email'] ?? '',
                'fullname' => $data['fullname'] ?? '',
            ]);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }

            $hashedPassword = password_hash($passwordPlain, PASSWORD_BCRYPT);
            $status = $data['status'] ?? 'Active';
            if (in_array($role, ['employer', 'training_provider'], true)) {
                $status = 'Pending';
            }

            $query = "INSERT INTO users (username, email, phone, password, fullname, role, is_active, status, barangay, provider_type, provider_document_path, temp_password_required, approval_remark, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [
                $data['username'],
                $data['email'],
                $data['phone'] ?? null,
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
            $this->rememberPassword((int) $createdId, $hashedPassword);
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
            $user = $this->db->fetchOne("SELECT role, status, provider_document_path FROM users WHERE id = ? LIMIT 1", [$user_id], "i");
            if (!$user || !in_array($user['role'], ['employer', 'training_provider'], true)) {
                throw new Exception("Provider account not found");
            }

            if ($user['status'] !== 'Pending') {
                throw new Exception('Only pending provider registrations can be approved or declined.');
            }

            if ($status === 'Active' && empty($user['provider_document_path'])) {
                throw new Exception('A legitimacy document is required before a provider can be approved.');
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

    public function getSecuritySettings($userId)
    {
        $userId = (int) $userId;
        $this->db->execute(
            "INSERT IGNORE INTO user_security_settings (user_id, two_factor_enabled, alert_email_enabled) VALUES (?, 1, 1)",
            [$userId],
            'i'
        );

        $row = $this->db->fetchOne(
            "SELECT two_factor_enabled, alert_email_enabled, last_password_changed_at, updated_at FROM user_security_settings WHERE user_id = ? LIMIT 1",
            [$userId],
            'i'
        );

        return [
            'two_factor_enabled' => (int) ($row['two_factor_enabled'] ?? 1),
            'alert_email_enabled' => (int) ($row['alert_email_enabled'] ?? 1),
            'last_password_changed_at' => $row['last_password_changed_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    public function updateSecuritySettings($userId, $alertEmailEnabled)
    {
        try {
            $userId = (int) $userId;
            $alertEmailEnabled = $alertEmailEnabled ? 1 : 0;

            // 2FA remains enforced and email-based in this rollout.
            $this->db->execute(
                "INSERT INTO user_security_settings (user_id, two_factor_enabled, alert_email_enabled, updated_at)
                 VALUES (?, 1, ?, NOW())
                 ON DUPLICATE KEY UPDATE two_factor_enabled = 1, alert_email_enabled = VALUES(alert_email_enabled), updated_at = NOW()",
                [$userId, $alertEmailEnabled],
                'ii'
            );

            return [
                'success' => true,
                'message' => 'Security preferences updated successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getRecentLoginEvents($userId, $limit = 10)
    {
        $userId = (int) $userId;
        $limit = max(1, min(50, (int) $limit));

        return $this->db->fetchAll(
            "SELECT login_at, ip_address, user_agent, login_result, failure_reason
             FROM user_login_events
             WHERE user_id = ?
             ORDER BY id DESC
             LIMIT ?",
            [$userId, $limit],
            'ii'
        );
    }

    public function isOtpVerificationPending()
    {
        return !empty($_SESSION['pending_auth']) && !empty($_SESSION['pending_auth']['user']['id']);
    }

    public function getPendingOtpIdentityLabel()
    {
        if (!$this->isOtpVerificationPending()) {
            return '';
        }

        $email = $_SESSION['pending_auth']['user']['email'] ?? '';
        if ($email === '' || strpos($email, '@') === false) {
            return 'your email';
        }

        [$local, $domain] = explode('@', $email, 2);
        $maskedLocal = strlen($local) <= 2
            ? substr($local, 0, 1) . '*'
            : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));
        return $maskedLocal . '@' . $domain;
    }

    public function verifyEmailOtpForPendingLogin($otp)
    {
        try {
            if (!$this->isOtpVerificationPending()) {
                throw new Exception('Your verification session has expired. Please log in again.');
            }

            $otp = trim($otp);
            if (!preg_match('/^\d{6}$/', $otp)) {
                throw new Exception('Enter a valid 6-digit verification code.');
            }

            $pendingUser = $_SESSION['pending_auth']['user'];
            $userId = (int) $pendingUser['id'];

            $otpRow = $this->db->fetchOne(
                "SELECT id, otp_hash, expires_at, attempts FROM user_2fa_codes WHERE user_id = ? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1",
                [$userId],
                'i'
            );

            if (!$otpRow) {
                throw new Exception('Verification code not found. Please request a new code.');
            }

            if ((int) $otpRow['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                unset($_SESSION['pending_auth']);
                throw new Exception('Too many verification attempts. Please log in again.');
            }

            $expiresAt = strtotime((string) $otpRow['expires_at']);
            if ($expiresAt !== false && $expiresAt < time()) {
                throw new Exception('Verification code expired. Please request a new code.');
            }

            if (!password_verify($otp, $otpRow['otp_hash'])) {
                $this->db->execute(
                    "UPDATE user_2fa_codes SET attempts = attempts + 1 WHERE id = ?",
                    [(int) $otpRow['id']],
                    'i'
                );
                throw new Exception('Invalid verification code.');
            }

            $this->db->execute(
                "UPDATE user_2fa_codes SET consumed_at = NOW() WHERE id = ?",
                [(int) $otpRow['id']],
                'i'
            );

            $this->completeLoginSessionFromPendingAuth();

            return [
                'success' => true,
                'message' => 'Verification successful.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function resendEmailOtpForPendingLogin()
    {
        try {
            if (!$this->isOtpVerificationPending()) {
                throw new Exception('Your verification session has expired. Please log in again.');
            }

            $nextAllowedAt = (int) ($_SESSION['pending_auth']['resend_available_at'] ?? 0);
            if ($nextAllowedAt > time()) {
                $wait = $nextAllowedAt - time();
                throw new Exception('Please wait ' . $wait . ' second(s) before requesting another code.');
            }

            $pendingUser = $_SESSION['pending_auth']['user'];
            $otpResult = $this->sendEmailOtpCode($pendingUser);
            if (!$otpResult['success']) {
                throw new Exception($otpResult['message']);
            }

            $_SESSION['pending_auth']['resend_available_at'] = time() + self::OTP_RESEND_COOLDOWN_SECONDS;

            return [
                'success' => true,
                'message' => 'A new verification code has been sent to your email.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function startEmailOtpLogin(array $pendingUser)
    {
        if (empty($pendingUser['email']) || !filter_var($pendingUser['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'A valid email address is required to complete login verification.'
            ];
        }

        $otpResult = $this->sendEmailOtpCode($pendingUser);
        if (!$otpResult['success']) {
            return $otpResult;
        }

        $_SESSION['pending_auth'] = [
            'user' => $pendingUser,
            'resend_available_at' => time() + self::OTP_RESEND_COOLDOWN_SECONDS,
            'created_at' => time(),
        ];

        return [
            'success' => true
        ];
    }

    private function sendEmailOtpCode(array $pendingUser)
    {
        $userId = (int) ($pendingUser['id'] ?? 0);
        $email = trim((string) ($pendingUser['email'] ?? ''));
        if ($userId <= 0 || $email === '') {
            return [
                'success' => false,
                'message' => 'Unable to send verification code. Missing account details.'
            ];
        }

        $otpCode = (string) random_int(100000, 999999);
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);

        $this->db->execute(
            "UPDATE user_2fa_codes SET consumed_at = NOW() WHERE user_id = ? AND consumed_at IS NULL",
            [$userId],
            'i'
        );

        $this->db->execute(
            "INSERT INTO user_2fa_codes (user_id, otp_hash, expires_at, attempts, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 0, NOW())",
            [$userId, $otpHash, self::OTP_EXPIRY_SECONDS],
            'isi'
        );

        require_once __DIR__ . '/EmailService.php';
        $emailService = new EmailService($this->db);
        $subject = 'Your Login Verification Code';
        $body = $emailService->buildStyledEmail(
            'Email Verification Required',
            '<p>Hello,</p><p>Your one-time verification code is:</p><p style="font-size:30px;font-weight:800;letter-spacing:4px;margin:16px 0;color:#1d4ed8;">' . htmlspecialchars($otpCode) . '</p><p>This code expires in 10 minutes.</p><p>If you did not attempt to log in, please change your password immediately.</p>'
        );

        $sendResult = $emailService->send($email, $subject, $body);
        if (!$sendResult['success']) {
            return [
                'success' => false,
                'message' => 'Unable to send verification code email right now. Please try again later.'
            ];
        }

        return [
            'success' => true
        ];
    }

    private function completeLoginSessionFromPendingAuth()
    {
        $pendingUser = $_SESSION['pending_auth']['user'] ?? null;
        if (!$pendingUser || empty($pendingUser['id'])) {
            throw new Exception('Verification session is invalid.');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = (int) $pendingUser['id'];
        $_SESSION['username'] = $pendingUser['username'];
        $_SESSION['fullname'] = $pendingUser['fullname'];
        $_SESSION['role'] = $pendingUser['role'];
        $_SESSION['email'] = $pendingUser['email'];
        $_SESSION['status'] = $pendingUser['status'];
        $_SESSION['barangay'] = $pendingUser['barangay'] ?? null;
        $_SESSION['temp_password_required'] = (int) ($pendingUser['temp_password_required'] ?? 0);

        $this->user_id = (int) $pendingUser['id'];
        $this->username = $pendingUser['username'];
        $this->email = $pendingUser['email'];
        $this->fullname = $pendingUser['fullname'];
        $this->role = $pendingUser['role'];

        $this->recordLoginEvent((int) $pendingUser['id'], 'success', 'otp_verified');

        if (!empty($pendingUser['is_new_device'])) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown browser';
            $this->sendActivityAlertEmail(
                $pendingUser,
                'Security Alert: New Login Activity',
                'We noticed a login from a new browser or network. IP: ' . htmlspecialchars($ip) . '. Browser: ' . htmlspecialchars(substr($agent, 0, 120)) . '. If this was not you, reset your password immediately.'
            );
        }

        unset($_SESSION['pending_auth']);
    }

    private function validateStrongPassword($password, array $context = [])
    {
        $password = (string) $password;

        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            return [
                'valid' => false,
                'message' => 'Password must be at least ' . self::PASSWORD_MIN_LENGTH . ' characters long.'
            ];
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must include both uppercase and lowercase letters.'
            ];
        }

        if (!preg_match('/\d/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must include at least one number.'
            ];
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must include at least one symbol.'
            ];
        }

        $lower = strtolower(trim($password));
        if (in_array($lower, $this->commonWeakPasswords, true)) {
            return [
                'valid' => false,
                'message' => 'This password is too common or weak. Please choose a stronger password.'
            ];
        }

        $keywords = [];
        if (!empty($context['username'])) {
            $keywords[] = strtolower((string) $context['username']);
        }
        if (!empty($context['email']) && strpos((string) $context['email'], '@') !== false) {
            $keywords[] = strtolower(substr((string) $context['email'], 0, strpos((string) $context['email'], '@')));
        }
        if (!empty($context['fullname'])) {
            $fullname = strtolower((string) $context['fullname']);
            foreach (preg_split('/\s+/', $fullname) as $part) {
                if ($part !== '') {
                    $keywords[] = $part;
                }
            }
        }

        foreach (array_unique($keywords) as $keyword) {
            if (strlen($keyword) >= 3 && strpos($lower, $keyword) !== false) {
                return [
                    'valid' => false,
                    'message' => 'Password must not contain your personal account details.'
                ];
            }
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    private function isPasswordReused($userId, $plainPassword)
    {
        $rows = $this->db->fetchAll(
            "SELECT password_hash FROM user_password_history WHERE user_id = ? ORDER BY id DESC LIMIT 5",
            [(int) $userId],
            'i'
        );

        foreach ($rows as $row) {
            if (!empty($row['password_hash']) && password_verify($plainPassword, $row['password_hash'])) {
                return true;
            }
        }

        return false;
    }

    private function rememberPassword($userId, $hash)
    {
        $this->db->execute(
            "INSERT INTO user_password_history (user_id, password_hash, created_at) VALUES (?, ?, NOW())",
            [(int) $userId, $hash],
            'is'
        );

        $this->db->execute(
            "DELETE FROM user_password_history WHERE user_id = ? AND id NOT IN (SELECT id FROM (SELECT id FROM user_password_history WHERE user_id = ? ORDER BY id DESC LIMIT 5) t)",
            [(int) $userId, (int) $userId],
            'ii'
        );
    }

    private function generateStrongTemporaryPassword()
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $symbols = '!@#$%^&*()-_=+?';

        $passwordChars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        $all = $upper . $lower . $digits . $symbols;
        while (count($passwordChars) < 14) {
            $passwordChars[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($passwordChars);
        return implode('', $passwordChars);
    }

    private function isKnownLoginContext($userId, $ipAddress, $userAgent)
    {
        if ($ipAddress === '' && $userAgent === '') {
            return true;
        }

        $row = $this->db->fetchOne(
            "SELECT id FROM user_login_events WHERE user_id = ? AND login_result = 'success' AND ip_address = ? AND user_agent = ? AND login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) LIMIT 1",
            [(int) $userId, (string) $ipAddress, (string) $userAgent],
            'iss'
        );

        return !empty($row);
    }

    private function recordLoginEvent($userId, $result, $reason = '')
    {
        $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
        $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000);

        $this->db->execute(
            "INSERT INTO user_login_events (user_id, ip_address, user_agent, login_at, login_result, failure_reason) VALUES (?, ?, ?, NOW(), ?, ?)",
            [(int) $userId, $ipAddress, $userAgent, (string) $result, (string) $reason],
            'issss'
        );
    }

    private function sendActivityAlertEmail(array $userData, $subject, $message)
    {
        $userId = (int) ($userData['id'] ?? 0);
        $email = (string) ($userData['email'] ?? '');
        if ($userId <= 0 || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $alertPref = $this->db->fetchOne(
            "SELECT alert_email_enabled FROM user_security_settings WHERE user_id = ? LIMIT 1",
            [$userId],
            'i'
        );

        if (isset($alertPref['alert_email_enabled']) && (int) $alertPref['alert_email_enabled'] !== 1) {
            return;
        }

        require_once __DIR__ . '/EmailService.php';
        $emailService = new EmailService($this->db);
        $name = htmlspecialchars((string) ($userData['fullname'] ?? $userData['username'] ?? 'User'));
        $body = $emailService->buildStyledEmail(
            'Account Activity Alert',
            '<p>Hello ' . $name . ',</p><p>' . htmlspecialchars($message) . '</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>'
        );
        $emailService->send($email, $subject, $body);
    }

    public function initiatePasswordReset($usernameOrEmail) {
        $user = $this->db->fetchOne("SELECT id, email, username FROM users WHERE username = ? OR email = ? LIMIT 1", [$usernameOrEmail, $usernameOrEmail], "ss");
        if (!$user) {
            return ['success' => true];
        }
        
        $otpCode = (string) random_int(100000, 999999);
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);
        
        $this->db->execute(
            "UPDATE user_2fa_codes SET consumed_at = NOW() WHERE user_id = ? AND consumed_at IS NULL",
            [$user['id']], 'i'
        );
        $this->db->execute(
            "INSERT INTO user_2fa_codes (user_id, otp_hash, expires_at, attempts, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 0, NOW())",
            [$user['id'], $otpHash, self::OTP_EXPIRY_SECONDS], 'isi'
        );
        
        require_once __DIR__ . '/EmailService.php';
        $emailService = new EmailService($this->db);
        $subject = 'Password Reset Verification Code';
        $body = $emailService->buildStyledEmail(
            'Password Reset Verification',
            '<p>Hello,</p><p>You requested a password reset. Your verification code is:</p><p style="font-size:30px;font-weight:800;letter-spacing:4px;margin:16px 0;color:#1d4ed8;">' . htmlspecialchars($otpCode) . '</p><p>This code expires in 10 minutes. If you did not request this, please ignore this email.</p>'
        );
        
        $emailService->send($user['email'], $subject, $body);
        
        $_SESSION['pwd_reset'] = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'verified' => false,
            'resend_available_at' => time() + self::OTP_RESEND_COOLDOWN_SECONDS,
        ];
        
        return ['success' => true];
    }
    
    public function verifyPasswordResetOtp($otp) {
        try {
            if (empty($_SESSION['pwd_reset']['user_id'])) {
                throw new Exception('Session expired. Please request a new password reset.');
            }
            
            $userId = (int) $_SESSION['pwd_reset']['user_id'];
            $otp = trim($otp);
            if (!preg_match('/^\d{6}$/', $otp)) {
                throw new Exception('Enter a valid 6-digit verification code.');
            }
            
            $otpRow = $this->db->fetchOne(
                "SELECT id, otp_hash, expires_at, attempts FROM user_2fa_codes WHERE user_id = ? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1",
                [$userId], 'i'
            );
            
            if (!$otpRow) {
                throw new Exception('Verification code not found. Please request a new code.');
            }
            if ((int) $otpRow['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                unset($_SESSION['pwd_reset']);
                throw new Exception('Too many verification attempts. Please try again later.');
            }
            
            $expiresAt = strtotime((string) $otpRow['expires_at']);
            if ($expiresAt !== false && $expiresAt < time()) {
                throw new Exception('Verification code expired. Please request a new code.');
            }
            
            if (!password_verify($otp, $otpRow['otp_hash'])) {
                $this->db->execute("UPDATE user_2fa_codes SET attempts = attempts + 1 WHERE id = ?", [(int) $otpRow['id']], 'i');
                throw new Exception('Invalid verification code.');
            }
            
            $this->db->execute("UPDATE user_2fa_codes SET consumed_at = NOW() WHERE id = ?", [(int) $otpRow['id']], 'i');
            
            $_SESSION['pwd_reset']['verified'] = true;
            return ['success' => true, 'message' => 'Verification successful.'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function completePasswordReset($newPassword) {
        try {
            if (empty($_SESSION['pwd_reset']['user_id']) || empty($_SESSION['pwd_reset']['verified'])) {
                throw new Exception('Session expired or not verified. Please start over.');
            }
            
            $userId = (int) $_SESSION['pwd_reset']['user_id'];
            
            $userRow = $this->db->fetchOne("SELECT username, email, fullname FROM users WHERE id = ? LIMIT 1", [$userId], "i");
            if (!$userRow) {
                throw new Exception("User not found.");
            }
            
            $passwordValidation = $this->validateStrongPassword($newPassword, [
                'username' => $userRow['username'] ?? '',
                'email' => $userRow['email'] ?? '',
                'fullname' => $userRow['fullname'] ?? '',
            ]);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }
            if ($this->isPasswordReused($userId, $newPassword)) {
                throw new Exception('You cannot reuse any of your last 5 passwords.');
            }
            
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->db->execute("UPDATE users SET password = ?, temp_password_required = 0 WHERE id = ?", [$hashed, $userId], "si");
            $this->rememberPassword($userId, $hashed);
            
            unset($_SESSION['pwd_reset']);
            
            return ['success' => true, 'message' => 'Password reset successfully. You can now log in.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
