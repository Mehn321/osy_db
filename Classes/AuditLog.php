<?php

/**
 * AuditLog Class
 *
 * Tracks system activity for accountability and review.
 */
class AuditLog
{
    private $db;
    private $table = 'audit_logs';

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function logAction($actor_id, $actor_role, $action, $target_type = null, $target_id = null, $metadata = null)
    {
        try {
            $query = "INSERT INTO {$this->table} (actor_id, actor_role, action, target_type, target_id, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [
                $actor_id,
                $actor_role,
                $action,
                $target_type,
                $target_id,
                $metadata
            ], "isssis");

            return [
                'success' => true,
                'message' => 'Audit logged successfully',
                'id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAll($filters = [], $limit = 100, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filters['actor_id'])) {
            $query .= " AND actor_id = ?";
            $params[] = $filters['actor_id'];
            $types .= 'i';
        }

        if (isset($filters['actor_role'])) {
            $query .= " AND actor_role = ?";
            $params[] = $filters['actor_role'];
            $types .= 's';
        }

        if (isset($filters['action'])) {
            $query .= " AND action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
            $types .= 's';
        }

        if (isset($filters['target_type'])) {
            $query .= " AND target_type = ?";
            $params[] = $filters['target_type'];
            $types .= 's';
        }

        if (isset($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        if (isset($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return $this->db->fetchAll($query, $params, $types);
    }
}
