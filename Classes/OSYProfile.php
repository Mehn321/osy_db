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
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
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
     * Handle image upload
     */
    private function uploadImage($file, $type = 'profile')
    {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $file['error']);
        }

        // Validate file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        }

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception("File size exceeds maximum limit of 5MB.");
        }

        // Determine upload directory
        $uploadDir = $type === 'govt_id' ?
            (__DIR__ . '/../uploads/govt_ids') : (__DIR__ . '/../uploads/profiles');

        // Create directory if needed
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . time() . '_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to save image file.");
        }

        // Return relative path for storage in database
        $path = $type === 'govt_id' ? '/uploads/govt_ids/' : '/uploads/profiles/';
        return $path . $filename;
    }

    /**
     * Create new youth profile
     */
    public function create($data, $profileImageFile = null, $govtIdImageFile = null)
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

            $query = "INSERT INTO {$this->table} 
                     (profile_type, first_name, middle_name, last_name, email, phone, age, gender, 
                      civil_status, education_level, reason_for_not_in_school, engagement_status, 
                      barangay, govt_id_type, govt_id_number, govt_id_image, primary_skill, skills, 
                      interests, status, registration_status, date_of_birth, image_path, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $this->db->execute($query, [
                $data['profile_type'] ?? 'Regular',     // s (1)
                $data['first_name'],                    // s (2)
                $data['middle_name'] ?? null,           // s (3)
                $data['last_name'],                     // s (4)
                $data['email'] ?? null,                 // s (5)
                $data['phone'] ?? null,                 // s (6)
                $data['age'],                           // i (7)
                $data['gender'],                        // s (8)
                $data['civil_status'] ?? null,          // s (9)
                $data['education_level'] ?? null,       // s (10)
                $data['reason_for_not_in_school'] ?? null, // s (11)
                $data['engagement_status'] ?? null,     // s (12)
                $data['barangay'],                         // s (13)
                $data['govt_id_type'] ?? null,          // s (14)
                $data['govt_id_number'] ?? null,        // s (15)
                $govtIdImagePath,                       // s (16)
                $data['primary_skill'] ?? 'Not Specified', // s (17)
                $data['skills'] ?? null,                // s (18)
                $data['interests'] ?? null,             // s (19)
                $data['status'] ?? 'Active',            // s (20)
                $data['registration_status'] ?? 'Drafting', // s (21)
                $data['date_of_birth'] ?? null,         // s (22)
                $profileImagePath,                      // s (23)
                $_SESSION['user_id']                    // i (24)
            ], "sssssssisssssssssssssssi");

            $id = $this->db->lastInsertId();

            // Trigger automated AI scoring in the background
            $scriptPath = realpath(__DIR__ . '/../api/background_recalculate_scores.php');
            if ($scriptPath) {
                // Windows-specific background process command
                pclose(popen("start /B php " . escapeshellarg($scriptPath) . " " . intval($id), "r"));
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
     * Get all profiles with filters
     */
    public function getAll($filters = [])
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

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

        $query .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($query);
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
                'date_of_birth'
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
                // Windows-specific background process command
                pclose(popen("start /B php " . escapeshellarg($scriptPath) . " " . intval($id), "r"));
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
