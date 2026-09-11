<?php

/**
 * OSY Profile Class
 * 
 * Handles OSY (Out-of-School Youth) profile management
 */

class OSYProfile
{
    private $db;
    private $table = 'osy_profiles';
    private $uploadDir = __DIR__ . '/../uploads/profiles';
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    private $maxFileSize = 5242880; // 5MB

    public function __construct($database)
    {
        $this->db = $database;
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function profileSelect($where = '')
    {
        return "SELECT p.*, u.email AS email, u.phone AS phone
                FROM {$this->table} p
                LEFT JOIN users u ON u.id = p.created_by" . ($where !== '' ? " WHERE {$where}" : '');
    }

    /**
     * Handle document upload
     */
    private function uploadImage($file, $type = 'profile')
    {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $file['error']);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception("Uploaded file is invalid.");
        }

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File size exceeds maximum limit of 5MB.");
        }

        $originalName = basename($file['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions, true)) {
            throw new Exception("Invalid file extension. Only JPG, PNG, GIF, and PDF are allowed.");
        }

        $detectedMime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detectedMime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        if ($detectedMime === null) {
            $detectedMime = $file['type'] ?? '';
        }

        // Validate file type based on server-detected content when possible
        if (!in_array($detectedMime, $this->allowedTypes, true)) {
            throw new Exception("Invalid file type. Only JPEG, PNG, GIF, and PDF are allowed.");
        }

        // Determine upload directory
        if ($type === 'govt_id') {
            $uploadDir = __DIR__ . '/../uploads/govt_ids';
        } elseif ($type === 'certification') {
            $uploadDir = __DIR__ . '/../uploads/certifications';
        } else {
            $uploadDir = __DIR__ . '/../uploads/profiles';
        }

        // Create directory if needed
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = $type . '_' . time() . '_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to save image file.");
        }

        // Return relative path for storage in database
        if ($type === 'govt_id') {
            $path = '/uploads/govt_ids/';
        } elseif ($type === 'certification') {
            $path = '/uploads/certifications/';
        } else {
            $path = '/uploads/profiles/';
        }

        return $path . $filename;
    }

    /**
     * Create new youth profile
     */
    public function create($data, $profileImageFile = null, $govtIdImageFile = null, $certificationFile = null)
    {
        try {
            unset($data['email'], $data['phone']);
            // Handle image uploads if provided
            $profileImagePath = null;
            if ($profileImageFile) {
                $profileImagePath = $this->uploadImage($profileImageFile, 'profile');
            }

            $govtIdImagePath = null;
            if ($govtIdImageFile) {
                $govtIdImagePath = $this->uploadImage($govtIdImageFile, 'govt_id');
            }

            $certificationPath = null;
            if ($certificationFile) {
                $certificationPath = $this->uploadImage($certificationFile, 'certification');
            }

            // Add the paths to data
            $data['govt_id_image'] = $govtIdImagePath;
            $data['image_path'] = $profileImagePath;
            $data['identity_document_path'] = $certificationPath;

            // Dynamic insert
            $columns = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($values) - 1) . '?';
            $types = str_repeat('s', count($values));
            $query = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";

            $this->db->execute($query, $values, $types);

            $id = $this->db->lastInsertId();

            // Trigger automated AI scoring in the background
            $scriptPath = realpath(__DIR__ . '/../api/background_recalculate_scores.php');
            if ($scriptPath) {
                $phpBinary = escapeshellarg(PHP_BINARY);
                $scriptArg = escapeshellarg($scriptPath);
                $profileIdArg = escapeshellarg((string) intval($id));
                $command = PHP_OS_FAMILY === 'Windows'
                    ? 'start /B ' . $phpBinary . ' ' . $scriptArg . ' ' . $profileIdArg . ' > NUL 2>&1'
                    : $phpBinary . ' ' . $scriptArg . ' ' . $profileIdArg . ' >/dev/null 2>&1 &';
                shell_exec($command);
            }

            return [
                'success' => true,
                'message' => 'Youth profile registered successfully',
                'id' => $id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get profile by ID
     */
    public function getById($id)
    {
        $query = $this->profileSelect('p.id = ?') . " LIMIT 1";
        return $this->db->fetchOne($query, [$id], "i");
    }

    /**
     * Get profile by user ID
     */
    public function getByUserId($userId)
    {
        $query = $this->profileSelect('p.created_by = ?') . " LIMIT 1";
        return $this->db->fetchOne($query, [$userId], "i");
    }

    private function applyFilters($filters, &$query)
    {
        if (isset($filters['profile_type']) && $filters['profile_type'] != 'All Types') {
            $query .= " AND TRIM(p.profile_type) = '" . $this->db->escape(trim($filters['profile_type'])) . "'";
        }

        if (isset($filters['barangay']) && $filters['barangay'] != 'All Barangays') {
            $query .= " AND TRIM(p.barangay) = '" . $this->db->escape(trim($filters['barangay'])) . "'";
        }

        if (isset($filters['gender']) && $filters['gender'] != 'All Genders') {
            $query .= " AND TRIM(p.gender) = '" . $this->db->escape(trim($filters['gender'])) . "'";
        }

        if (isset($filters['education']) && $filters['education'] != 'Any Level') {
            $query .= " AND TRIM(p.education_level) = '" . $this->db->escape(trim($filters['education'])) . "'";
        }

        if (isset($filters['status']) && $filters['status'] != 'All Status') {
            $query .= " AND TRIM(p.status) = '" . $this->db->escape(trim($filters['status'])) . "'";
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $query .= " AND (first_name LIKE '%{$search}%' OR last_name LIKE '%{$search}%' OR u.email LIKE '%{$search}%')";
        }
    }

    /**
     * Get all profiles with filters
     */
    public function getAll($filters = [], $limit = null, $offset = 0)
    {
        $query = $this->profileSelect('1=1');
        $this->applyFilters($filters, $query);
        $query .= " ORDER BY created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        return $this->db->fetchAll($query);
    }

    /**
     * Get count of filtered profiles
     */
    public function getFilteredCount($filters = [])
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $this->applyFilters($filters, $query);
        $result = $this->db->fetchOne($query);
        return $result['total'] ?? 0;
    }

    /**
     * Update profile
     */
    public function update($id, $data, $profileImageFile = null, $govtIdImageFile = null, $certificationFile = null)
    {
        try {
            $currentProfile = $this->getById($id);
            if (!$currentProfile) {
                throw new Exception("Profile not found");
            }

            $updates = [];
            $params = [];
            $types = '';

            $allowedFields = [
                'profile_type',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'age',
                'gender',
                'civil_status',
                'education_level',
                'address',
                'purok',
                'province',
                'municipality',
                'reason_for_not_in_school',
                'engagement_status',
                'occupation',
                'barangay',
                'govt_id_type',
                'govt_id_number',
                'primary_skill',
                'skills',
                'interests',
                'status',
                'registration_status',
                'date_of_birth',
                'identity_document_path'
            ];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "{$key} = ?";
                    $params[] = $value;
                    $types .= (is_int($value) || $key == 'age') ? 'i' : 's';
                }
            }

            // Handle profile image upload if provided
            if ($profileImageFile) {
                $profileImagePath = $this->uploadImage($profileImageFile, 'profile');
                $updates[] = "image_path = ?";
                $params[] = $profileImagePath;
                $types .= 's';
            }

            // Handle government ID image upload if provided
            if ($govtIdImageFile) {
                $govtIdImagePath = $this->uploadImage($govtIdImageFile, 'govt_id');
                $updates[] = "govt_id_image = ?";
                $params[] = $govtIdImagePath;
                $types .= 's';
            }

            // Handle certification document upload if provided
            if ($certificationFile) {
                $certificationPath = $this->uploadImage($certificationFile, 'certification');
                $updates[] = "identity_document_path = ?";
                $params[] = $certificationPath;
                $types .= 's';
            }

            if (empty($updates)) {
                throw new Exception("No valid fields to update");
            }

            $linkedUserId = !empty($currentProfile['created_by']) ? (int) $currentProfile['created_by'] : 0;
            $newFirstName = trim((string) ($data['first_name'] ?? $currentProfile['first_name'] ?? ''));
            $newLastName = trim((string) ($data['last_name'] ?? $currentProfile['last_name'] ?? ''));
            $newFullname = trim($newFirstName . ' ' . $newLastName);
            $newEmail = trim((string) ($data['email'] ?? $currentProfile['email'] ?? ''));
            $newPhone = trim((string) ($data['phone'] ?? $currentProfile['phone'] ?? ''));

            $conn = $this->db->getConnection();
            $conn->begin_transaction();

            if ($linkedUserId > 0) {
                if ($newFullname !== '') {
                    $this->db->execute(
                        "UPDATE users SET fullname = ? WHERE id = ?",
                        [$newFullname, $linkedUserId],
                        "si"
                    );
                }

                if ($newEmail !== '') {
                    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("The email address is invalid.");
                    }

                    $existingUserEmail = $this->db->fetchOne(
                        "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1",
                        [$newEmail, $linkedUserId],
                        "si"
                    );

                    if ($existingUserEmail) {
                        throw new Exception("This email address is already in use by another account.");
                    }

                    $this->db->execute(
                        "UPDATE users SET email = ?, phone = ?, fullname = ? WHERE id = ?",
                        [$newEmail, $newPhone, $newFullname, $linkedUserId],
                        "sssi"
                    );
                } elseif ($newPhone !== '') {
                    $this->db->execute("UPDATE users SET phone = ? WHERE id = ?", [$newPhone, $linkedUserId], 'si');
                }
            }

            $params[] = $id;
            $types .= 'i';

            $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, $params, $types);
            $conn->commit();

            // Trigger automated AI scoring in the background
            $scriptPath = realpath(__DIR__ . '/../api/background_recalculate_scores.php');
            if ($scriptPath) {
                $phpBinary = escapeshellarg(PHP_BINARY);
                $scriptArg = escapeshellarg($scriptPath);
                $profileIdArg = escapeshellarg((string) intval($id));
                $command = PHP_OS_FAMILY === 'Windows'
                    ? 'start /B ' . $phpBinary . ' ' . $scriptArg . ' ' . $profileIdArg . ' > NUL 2>&1'
                    : $phpBinary . ' ' . $scriptArg . ' ' . $profileIdArg . ' >/dev/null 2>&1 &';
                shell_exec($command);
            }

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set verification status for a youth profile
     */
    public function setVerificationStatus($profile_id, $status, $remark = null, $approved_by = null)
    {
        try {
            $validStatuses = ['Drafting', 'Pending', 'Verified', 'Rejected', 'Action Required', 'Declined'];
            if (!in_array($status, $validStatuses, true)) {
                throw new Exception("Invalid verification status");
            }

            $updates = [
                'verification_status = ?',
                'verification_remark = ?',
                'approved_by = ?',
                'approved_at = NOW()'
            ];
            $params = [$status, $remark, $approved_by];
            $types = 'ssi';

            if ($status === 'Verified') {
                $updates[] = 'registration_status = ?';
                $params[] = 'Approved';
                $types .= 's';
            }

            if (in_array($status, ['Rejected', 'Declined', 'Action Required'], true)) {
                $updates[] = 'registration_status = ?';
                $params[] = 'Submitted';
                $types .= 's';
            }

            $params[] = $profile_id;
            $types .= 'i';

            $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
            $this->db->execute($query, $params, $types);

            return [
                'success' => true,
                'message' => 'Profile verification status updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get pending profiles for a barangay
     */
    public function getPendingByBarangay($barangay)
    {
        $query = $this->profileSelect("p.barangay = ? AND p.verification_status IN ('Pending', 'Drafting', 'Action Required')") . " ORDER BY p.created_at DESC";
        return $this->db->fetchAll($query, [$barangay], 's');
    }

    /** Request a move without changing the profile's current barangay yet. */
    public function requestBarangayTransfer($profileId, $toBarangay, $requestedBy, $remark = null)
    {
        try {
            $profile = $this->getById($profileId);
            $toBarangay = trim((string) $toBarangay);
            if (!$profile || (int) $profile['created_by'] !== (int) $requestedBy) {
                throw new Exception('You can only request a transfer for your own profile.');
            }
            if ($toBarangay === '' || $toBarangay === $profile['barangay']) {
                throw new Exception('Please choose a different barangay.');
            }

            $existing = $this->db->fetchOne(
                "SELECT id FROM youth_barangay_transfers WHERE profile_id = ? AND status = 'Pending' LIMIT 1",
                [$profileId],
                'i'
            );
            if ($existing) {
                throw new Exception('A barangay transfer request is already awaiting review.');
            }

            $this->db->execute(
                "INSERT INTO youth_barangay_transfers (profile_id, from_barangay, to_barangay, request_remark, requested_by) VALUES (?, ?, ?, ?, ?)",
                [$profileId, $profile['barangay'], $toBarangay, trim((string) $remark), $requestedBy],
                'isssi'
            );
            return ['success' => true, 'message' => 'Transfer request sent to the new barangay for verification.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getPendingTransfersToBarangay($barangay)
    {
        return $this->db->fetchAll(
            "SELECT t.*, p.first_name, p.middle_name, p.last_name, u.email, u.phone, p.image_path
             FROM youth_barangay_transfers t
             INNER JOIN osy_profiles p ON p.id = t.profile_id
             LEFT JOIN users u ON u.id = p.created_by
             WHERE t.to_barangay = ? AND t.status = 'Pending'
             ORDER BY t.requested_at ASC",
            [$barangay],
            's'
        );
    }

    /** Approving performs the actual ownership change atomically. */
    public function reviewBarangayTransfer($transferId, $destinationBarangay, $approve, $reviewedBy, $remark = null)
    {
        $conn = $this->db->getConnection();
        try {
            $conn->begin_transaction();
            $transfer = $this->db->fetchOne(
                "SELECT * FROM youth_barangay_transfers WHERE id = ? AND to_barangay = ? AND status = 'Pending' FOR UPDATE",
                [$transferId, $destinationBarangay],
                'is'
            );
            if (!$transfer) {
                throw new Exception('This transfer request is no longer available for review.');
            }

            $status = $approve ? 'Approved' : 'Rejected';
            $this->db->execute(
                "UPDATE youth_barangay_transfers SET status = ?, review_remark = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
                [$status, trim((string) $remark), $reviewedBy, $transferId],
                'ssii'
            );

            if ($approve) {
                $this->db->execute("UPDATE osy_profiles SET barangay = ?, updated_at = NOW() WHERE id = ?", [$destinationBarangay, $transfer['profile_id']], 'si');
                $this->db->execute("UPDATE users SET barangay = ? WHERE id = ?", [$destinationBarangay, $transfer['requested_by']], 'si');
            }
            $conn->commit();
            return ['success' => true, 'transfer' => $transfer, 'message' => $approve ? 'Youth transferred to your barangay.' : 'Transfer request declined; the youth remains in the old barangay.'];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete profile
     */
    public function delete($id)
    {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $this->db->execute($query, [$id], "i");

            return [
                'success' => true,
                'message' => 'Profile deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get total profiles
     */
    public function getTotalCount()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table}");
        return $result['total'];
    }

    /**
     * Get profiles by status
     */
    public function getByStatus($status)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
        $result = $this->db->fetchOne($query, [$status], "s");
        return $result['count'];
    }

    /**
     * Search profiles
     */
    public function search($term)
    {
        $query = $this->profileSelect('p.first_name LIKE ? OR p.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?') .
            " ORDER BY p.created_at DESC";

        $searchTerm = "%{$term}%";
        return $this->db->fetchAll($query, [$searchTerm, $searchTerm, $searchTerm, $searchTerm], "ssss");
    }

    /**
     * Get total count by profile type
     */
    public function getCountByType($type = null)
    {
        if ($type) {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE profile_type = ?";
            $result = $this->db->fetchOne($query, [$type], "s");
        } else {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->db->fetchOne($query);
        }
        return $result['total'] ?? 0;
    }

    /**
     * Get profiles by type
     */
    public function getByType($type)
    {
        $query = $this->profileSelect('p.profile_type = ?') . " ORDER BY p.created_at DESC";
        return $this->db->fetchAll($query, [$type], "s");
    }
}
