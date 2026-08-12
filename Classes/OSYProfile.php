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
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($query, [$id], "i");
    }

    /**
     * Get profile by user ID
     */
    public function getByUserId($userId)
    {
        $query = "SELECT * FROM {$this->table} WHERE created_by = ? LIMIT 1";
        return $this->db->fetchOne($query, [$userId], "i");
    }

    private function applyFilters($filters, &$query)
    {
        if (isset($filters['profile_type']) && $filters['profile_type'] != 'All Types') {
            $query .= " AND TRIM(profile_type) = '" . $this->db->escape(trim($filters['profile_type'])) . "'";
        }

        if (isset($filters['barangay']) && $filters['barangay'] != 'All Barangays') {
            $query .= " AND TRIM(barangay) = '" . $this->db->escape(trim($filters['barangay'])) . "'";
        }

        if (isset($filters['gender']) && $filters['gender'] != 'All Genders') {
            $query .= " AND TRIM(gender) = '" . $this->db->escape(trim($filters['gender'])) . "'";
        }

        if (isset($filters['education']) && $filters['education'] != 'Any Level') {
            $query .= " AND TRIM(education_level) = '" . $this->db->escape(trim($filters['education'])) . "'";
        }

        if (isset($filters['status']) && $filters['status'] != 'All Status') {
            $query .= " AND TRIM(status) = '" . $this->db->escape(trim($filters['status'])) . "'";
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $query .= " AND (first_name LIKE '%{$search}%' OR last_name LIKE '%{$search}%' OR email LIKE '%{$search}%')";
        }
    }

    /**
     * Get all profiles with filters
     */
    public function getAll($filters = [], $limit = null, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
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
    public function update($id, $data, $profileImageFile = null, $govtIdImageFile = null)
    {
        try {
            $updates = [];
            $params = [];
            $types = '';

            $allowedFields = [
                'profile_type',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'phone',
                'age',
                'gender',
                'civil_status',
                'education_level',
                'reason_for_not_in_school',
                'engagement_status',
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

            if (empty($updates)) {
                throw new Exception("No valid fields to update");
            }

            $params[] = $id;
            $types .= 'i';

            $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, $params, $types);

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
            $validStatuses = ['Drafting', 'Pending', 'Verified', 'Rejected', 'Action Required'];
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

            if ($status === 'Rejected') {
                $updates[] = 'registration_status = ?';
                $params[] = 'Declined';
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
        $query = "SELECT * FROM {$this->table} WHERE barangay = ? AND verification_status = 'Pending' ORDER BY created_at DESC";
        return $this->db->fetchAll($query, [$barangay], 's');
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
        $query = "SELECT * FROM {$this->table} WHERE 
                 first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?
                 ORDER BY created_at DESC";

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
        $query = "SELECT * FROM {$this->table} WHERE profile_type = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($query, [$type], "s");
    }
}