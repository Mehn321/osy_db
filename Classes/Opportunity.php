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
            $query = "INSERT INTO {$this->table} 
                     (title, type, employment_type, work_schedule, experience_req, training_provider, duration, modality, location, compensation, benefits, certification, description, total_slots, deadline, status, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open', ?, NOW())";

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
                $_SESSION['user_id']
            ], "sssssssssssssssi");

            return [
                'success' => true,
                'message' => 'Opportunity created successfully',
                'id' => $this->db->lastInsertId()
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
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

        if (isset($filters['type']) && $filters['type'] != 'All') {
            $query .= " AND type = '{$this->db->escape($filters['type'])}'";
        }
        
        if (isset($filters['category'])) {
            if ($filters['category'] === 'jobs') {
                $query .= " AND type = 'Job Opening'";
            } elseif ($filters['category'] === 'training') {
                $query .= " AND type IN ('Vocational Training', 'Scholarship')";
            }
        }

        if (isset($filters['status']) && $filters['status'] != 'All') {
            $query .= " AND status = '{$this->db->escape($filters['status'])}'";
        }

        if (isset($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $query .= " AND (title LIKE '%{$search}%' OR location LIKE '%{$search}%')";
        }

        $query .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($query);
    }

    /**
     * Update opportunity
     */
    public function update($id, $data)
    {
        try {
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
                    'status'
                ])) {
                    $updates[] = "{$key} = ?";
                    $params[] = $value;
                    $types .= ($key == 'total_slots') ? 'i' : 's';
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
     * Get filled slots
     */
    public function getFilledSlots($opportunity_id)
    {
        $query = "SELECT COUNT(*) as filled FROM osy_matches WHERE opportunity_id = ?";
        $result = $this->db->fetchOne($query, [$opportunity_id], "i");
        return $result['filled'] ?? 0;
    }
}
