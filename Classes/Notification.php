<?php

/**
 * Notification Class
 * 
 * Handles notification management and broadcasting
 */

class Notification
{
    private $db;
    private $table = 'notifications';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Create notification
     */
    public function create($data)
    {
        try {
            $query = "INSERT INTO {$this->table} 
                     (title, message, type, recipient_type, status, created_by, created_at) 
                     VALUES (?, ?, ?, ?, 'Sent', ?, NOW())";

            $this->db->execute($query, [
                $data['title'],
                $data['message'],
                $data['type'], // 'Opportunity', 'Match', 'System', 'Reminder'
                $data['recipient_type'] ?? 'All', // 'All', 'OSY', 'Specific'
                $_SESSION['user_id']
            ], "ssssi");

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
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
     * Broadcast notification to matching candidates
     */
    public function broadcastToMatches($opportunity_id, $title, $message)
    {
        try {
            // Get matched OSY for this opportunity
            $query = "INSERT INTO {$this->table} (title, message, type, recipient_type, status, created_by, created_at)
                     SELECT ?, ?, 'Opportunity', 'Specific', 'Sent', ?, NOW()
                     FROM osy_profiles p
                     JOIN osy_matches m ON p.id = m.osy_id
                     WHERE m.opportunity_id = ? AND m.match_score >= 75";

            $this->db->execute($query, [$title, $message, $_SESSION['user_id'], $opportunity_id], "sssi");

            return [
                'success' => true,
                'message' => 'Notification broadcast successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all notifications
     */
    public function getAll($limit = 50)
    {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($query, [$limit], "i");
    }

    /**
     * Get notification by ID
     */
    public function getById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($query, [$id], "i");
    }

    /**
     * Get total notifications
     */
    public function getTotalCount()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM {$this->table}");
        return $result['total'] ?? 0;
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $this->db->execute($query, [$id], "i");

            return [
                'success' => true,
                'message' => 'Notification deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Template Management Methods

    /**
     * Create notification template
     */
    public function createTemplate($data)
    {
        try {
            $query = "INSERT INTO notification_templates 
                     (name, subject, body, type, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";

            $this->db->execute($query, [
                $data['name'],
                $data['subject'] ?? null,
                $data['body'],
                $data['type'], // 'SMS', 'Email', 'SMS/Email'
                $_SESSION['user_id']
            ], "ssssi");

            return [
                'success' => true,
                'message' => 'Template created successfully',
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
     * Get all notification templates
     */
    public function getAllTemplates()
    {
        $query = "SELECT * FROM notification_templates ORDER BY created_at DESC";
        return $this->db->fetchAll($query);
    }

    /**
     * Get template by ID
     */
    public function getTemplateById($id)
    {
        $query = "SELECT * FROM notification_templates WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($query, [$id], "i");
    }

    /**
     * Update notification template
     */
    public function updateTemplate($id, $data)
    {
        try {
            $query = "UPDATE notification_templates 
                     SET name = ?, subject = ?, body = ?, type = ?, updated_at = NOW() 
                     WHERE id = ?";

            $this->db->execute($query, [
                $data['name'],
                $data['subject'] ?? null,
                $data['body'],
                $data['type'],
                $id
            ], "ssssi");

            return [
                'success' => true,
                'message' => 'Template updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete notification template
     */
    public function deleteTemplate($id)
    {
        try {
            $query = "DELETE FROM notification_templates WHERE id = ?";
            $this->db->execute($query, [$id], "i");

            return [
                'success' => true,
                'message' => 'Template deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Render template with variables
     */
    public function renderTemplate($template, $variables = [])
    {
        $body = $template['body'];
        $subject = $template['subject'] ?? '';

        foreach ($variables as $key => $value) {
            $body = str_replace("{{{$key}}}", $value, $body);
            $subject = str_replace("{{{$key}}}", $value, $subject);
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
