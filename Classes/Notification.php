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
                     (title, message, type, recipient_type, recipient_id, status, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?, 'Sent', ?, NOW())";

            $recipientId = isset($data['recipient_id']) ? $data['recipient_id'] : null;

            $this->db->execute($query, [
                $data['title'],
                $data['message'],
                $data['type'], // 'Opportunity', 'Match', 'System', 'Reminder'
                $data['recipient_type'] ?? 'All', // 'All', 'OSY', 'Staff', 'Specific'
                $recipientId,
                $_SESSION['user_id']
            ], "ssssisi");

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
     * Send notification to specific user
     */
    public function sendToUser($user_id, $title, $message, $type = 'System')
    {
        return $this->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'recipient_type' => 'Specific',
            'recipient_id' => $user_id
        ]);
    }

    /**
     * Broadcast notification to all users with specific role
     */
    public function broadcastToRole($role, $title, $message, $type = 'System')
    {
        try {
            $query = "INSERT INTO {$this->table} (title, message, type, recipient_type, status, created_by, created_at)
                     SELECT ?, ?, ?, 'Specific', 'Sent', ?, NOW()
                     FROM users 
                     WHERE role = ? AND status = 'Active'";

            $this->db->execute($query, [$title, $message, $type, $_SESSION['user_id'], $role], "sssss");

            return [
                'success' => true,
                'message' => 'Notification broadcast to role successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Broadcast notification to users in specific barangay
     */
    public function broadcastToBarangay($barangay_id, $title, $message, $type = 'System')
    {
        try {
            $query = "INSERT INTO {$this->table} (title, message, type, recipient_type, status, created_by, created_at)
                     SELECT ?, ?, ?, 'Specific', 'Sent', ?, NOW()
                     FROM users 
                     WHERE barangay = ? AND status = 'Active'";

            $this->db->execute($query, [$title, $message, $type, $_SESSION['user_id'], $barangay_id], "sssss");

            return [
                'success' => true,
                'message' => 'Notification broadcast to barangay successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the role for a user
     */
    private function getUserRole($user_id)
    {
        $result = $this->db->fetchOne("SELECT role FROM users WHERE id = ? LIMIT 1", [$user_id], "i");
        return $result['role'] ?? null;
    }

    /**
     * Get notifications for current user
     */
    public function getUserNotifications($user_id, $limit = 20)
    {
        $role = $this->getUserRole($user_id);
        $query = "SELECT n.*, u.fullname as sender_name, IF(nr.read_at IS NOT NULL, 'Read', n.status) as status
                 FROM {$this->table} n
                 LEFT JOIN users u ON n.created_by = u.id
                 LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                 WHERE (n.recipient_type = 'All'
                        OR (n.recipient_type = 'Specific' AND n.recipient_id = ?)";
        $params = [$user_id, $user_id];
        $types = 'ii';

        if ($role === 'youth') {
            $query .= " OR n.recipient_type = 'OSY'";
        } elseif (in_array($role, ['lydo', 'sk_chairman', 'employer', 'training_provider', 'admin'], true)) {
            $query .= " OR n.recipient_type = 'Staff'";
        }

        $query .= ") ORDER BY n.created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        return $this->db->fetchAll($query, $params, $types);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notification_id, $user_id)
    {
        try {
            $notification = $this->getById($notification_id);
            if (!$notification) {
                throw new Exception("Notification not found");
            }

            $role = $this->getUserRole($user_id);
            $canAccess = false;

            if ($notification['recipient_type'] === 'All') {
                $canAccess = true;
            } elseif ($notification['recipient_type'] === 'Specific' && $notification['recipient_id'] == $user_id) {
                $canAccess = true;
            } elseif ($notification['recipient_type'] === 'OSY' && $role === 'youth') {
                $canAccess = true;
            } elseif ($notification['recipient_type'] === 'Staff' && in_array($role, ['lydo', 'sk_chairman', 'employer', 'training_provider', 'admin'], true)) {
                $canAccess = true;
            }

            if (!$canAccess) {
                throw new Exception("You do not have permission to mark this notification as read");
            }

            $query = "INSERT INTO notification_reads (notification_id, user_id, read_at)
                     VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE read_at = NOW()";
            $this->db->execute($query, [$notification_id, $user_id], "ii");

            return [
                'success' => true,
                'message' => 'Notification marked as read'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount($user_id)
    {
        $role = $this->getUserRole($user_id);
        $query = "SELECT COUNT(*) as count
                 FROM {$this->table} n
                 LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                 WHERE nr.id IS NULL
                   AND (n.recipient_type = 'All'
                        OR (n.recipient_type = 'Specific' AND n.recipient_id = ?)";
        $params = [$user_id, $user_id];
        $types = 'ii';

        if ($role === 'youth') {
            $query .= " OR n.recipient_type = 'OSY'";
        } elseif (in_array($role, ['lydo', 'sk_chairman', 'employer', 'training_provider', 'admin'], true)) {
            $query .= " OR n.recipient_type = 'Staff'";
        }

        $query .= ")";

        $result = $this->db->fetchOne($query, $params, $types);
        return $result['count'] ?? 0;
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
