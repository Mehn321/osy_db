<?php

/**
 * Opportunity Class
 * 
 * Handles job opportunities, training programs, and scholarships
 */

class Opportunity
{
    private $db;
    private $table = 'opportunities';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Create new opportunity
     */
    public function create($data)
    {
        try {
            // Check if user is an approved provider
            if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['employer', 'training_provider'])) {
                throw new Exception("Only approved providers can create opportunities");
            }

            if ($_SESSION['status'] !== 'Active') {
                throw new Exception("Your account must be approved to create opportunities");
            }

            $query = "INSERT INTO {$this->table} 
                     (title, type, employment_type, work_schedule, experience_req, training_provider, duration, modality, location, compensation, benefits, certification, description, total_slots, deadline, age_min, age_max, status, provider_id, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open', ?, ?, NOW())";

            $this->db->execute($query, [
                $data['title'],
                $data['type'], // 'Job Opening', 'Vocational Training', 'Scholarship'
                $data['employment_type'] ?? null,
                $data['work_schedule'] ?? null,
                $data['experience_req'] ?? null,
                $data['training_provider'] ?? null,
                $data['duration'] ?? null,
                $data['modality'] ?? null,
                $data['location'],
                $data['compensation'] ?? null,
                $data['benefits'] ?? null,
                $data['certification'] ?? null,
                $data['description'] ?? null,
                $data['total_slots'],
                $data['deadline'],
                $data['age_min'] ?? null,
                $data['age_max'] ?? null,
                $_SESSION['user_id'], // provider_id
                $_SESSION['user_id']  // created_by
            ]);

            $opportunityId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Opportunity created successfully',
                'id' => $opportunityId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get opportunity by ID
     */
    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($query, [$id], "i");
    }

    /**
     * Get all opportunities
     */
    public function getAll($filters = [])
    {
        $query = "SELECT o.*, u.fullname as provider_name, u.provider_type as provider_type FROM {$this->table} o LEFT JOIN users u ON o.created_by = u.id WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filters['type']) && $filters['type'] != 'All') {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
            $types .= 's';
        }

        if (isset($filters['category'])) {
            if ($filters['category'] === 'jobs') {
                $query .= " AND type = 'Job Opening'";
            } elseif ($filters['category'] === 'training') {
                $query .= " AND type IN ('Vocational Training', 'Scholarship')";
            }
        }

        if (isset($filters['status']) && $filters['status'] != 'All') {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['provider_id'])) {
            $query .= " AND provider_id = ?";
            $params[] = (int)$filters['provider_id'];
            $types .= 'i';
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $query .= " AND (title LIKE ? OR location LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= 'ss';
        }

        $query .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Update opportunity
     */
    public function update($id, $data)
    {
        try {
            // Check ownership and permissions
            $opportunity = $this->getById($id);
            if (!$opportunity) {
                throw new Exception("Opportunity not found");
            }

            if (!isset($_SESSION['role'])) {
                throw new Exception("Authentication required");
            }

            // Only the provider who created it or LYDO can update
            if ($_SESSION['role'] !== 'lydo' && $opportunity['provider_id'] != $_SESSION['user_id']) {
                throw new Exception("You can only update your own opportunities");
            }

            $updates = [];
            $params = [];
            $types = '';

            foreach ($data as $key => $value) {
                if (in_array($key, [
                    'title',
                    'type',
                    'employment_type',
                    'work_schedule',
                    'experience_req',
                    'training_provider',
                    'duration',
                    'modality',
                    'location',
                    'compensation',
                    'benefits',
                    'certification',
                    'description',
                    'total_slots',
                    'deadline',
                    'age_min',
                    'age_max',
                    'status'
                ])) {
                    $updates[] = "{$key} = ?";
                    $params[] = $value;
                    $types .= ($key == 'total_slots' || $key == 'age_min' || $key == 'age_max') ? 'i' : 's';
                }
            }

            if (empty($updates)) {
                throw new Exception("No valid fields to update");
            }

            $params[] = $id;
            $types .= 'i';

            $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, $params, $types);

            return [
                'success' => true,
                'message' => 'Opportunity updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete opportunity
     */
    public function delete($id)
    {
        try {
            // Check ownership and permissions
            $opportunity = $this->getById($id);
            if (!$opportunity) {
                throw new Exception("Opportunity not found");
            }

            if (!isset($_SESSION['role'])) {
                throw new Exception("Authentication required");
            }

            // Only the provider who created it or LYDO can delete
            if ($_SESSION['role'] !== 'lydo' && $opportunity['provider_id'] != $_SESSION['user_id']) {
                throw new Exception("You can only delete your own opportunities");
            }

            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $this->db->execute($query, [$id], "i");

            return [
                'success' => true,
                'message' => 'Opportunity deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get total opportunities
     */
    public function getTotalCount()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'Open'");
        return $result['total'];
    }

    /**
     * Get opportunities by type
     */
    public function getByType($type)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE type = ? AND status = 'Open'";
        $result = $this->db->fetchOne($query, [$type], "s");
        return $result['count'];
    }

    /**
     * Get opportunities by provider
     */
    public function getByProvider($providerId = null)
    {
        $providerId = $providerId ?? $_SESSION['user_id'];
        $query = "SELECT o.*, u.fullname as provider_name FROM {$this->table} o LEFT JOIN users u ON o.provider_id = u.id WHERE provider_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($query, [$providerId], "i");
    }

    /**
     * Get opportunities for youth (only active opportunities)
     */
    public function getForYouth($filters = [])
    {
        $query = "SELECT o.*, u.fullname as provider_name, u.provider_type 
                 FROM {$this->table} o 
                 LEFT JOIN users u ON o.provider_id = u.id 
                 WHERE o.status = ? AND u.status = ?";

        $params = ['Open', 'Active'];
        $types = 'ss';

        if (isset($filters['type']) && $filters['type'] != 'All') {
            $query .= " AND o.type = ?";
            $params[] = $filters['type'];
            $types .= 's';
        }

        if (isset($filters['location'])) {
            $query .= " AND o.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
            $types .= 's';
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query .= " AND (o.title LIKE ? OR o.description LIKE ? OR o.location LIKE ?)";
            $params = array_merge($params, ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
            $types .= 'sss';
        }

        $query .= " ORDER BY o.created_at DESC";

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Add required skill to opportunity
     */
    public function addRequiredSkill($opportunityId, $skill, $importanceLevel = 'Required')
    {
        try {
            $query = "INSERT INTO opportunity_required_skills (opportunity_id, skill, importance_level) VALUES (?, ?, ?)";
            $this->db->execute($query, [$opportunityId, $skill, $importanceLevel], "iss");
            return ['success' => true, 'message' => 'Skill added successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get required skills for opportunity
     */
    public function getRequiredSkills($opportunityId)
    {
        $query = "SELECT * FROM opportunity_required_skills WHERE opportunity_id = ? ORDER BY importance_level, created_at";
        return $this->db->fetchAll($query, [$opportunityId], "i");
    }

    /**
     * Remove required skill from opportunity
     */
    public function removeRequiredSkill($skillId)
    {
        try {
            $query = "DELETE FROM opportunity_required_skills WHERE id = ?";
            $this->db->execute($query, [$skillId], "i");
            return ['success' => true, 'message' => 'Skill removed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update required skills for opportunity (bulk operation)
     */
    public function updateRequiredSkills($opportunityId, $skills)
    {
        try {
            // Delete existing skills
            $this->db->execute("DELETE FROM opportunity_required_skills WHERE opportunity_id = ?", [$opportunityId], "i");

            // Add new skills
            if (is_array($skills) && !empty($skills)) {
                foreach ($skills as $skill) {
                    $skillName = $skill['skill'] ?? $skill;
                    $importance = $skill['importance_level'] ?? 'Required';
                    $this->addRequiredSkill($opportunityId, $skillName, $importance);
                }
            }

            return ['success' => true, 'message' => 'Skills updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get opportunities by required skill
     */
    public function getByRequiredSkill($skill, $filters = [])
    {
        $query = "SELECT DISTINCT o.* FROM {$this->table} o 
                 INNER JOIN opportunity_required_skills ors ON o.id = ors.opportunity_id 
                 WHERE ors.skill LIKE ? AND o.status = 'Open'";

        $params = ['%' . $skill . '%'];
        $types = 's';

        if (isset($filters['type']) && $filters['type'] != 'All') {
            $query .= " AND o.type = ?";
            $params[] = $filters['type'];
            $types .= 's';
        }

        $query .= " ORDER BY o.created_at DESC";

        return $this->db->fetchAll($query, $params, $types);
    }
}
