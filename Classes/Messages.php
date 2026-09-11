<?php

/**
 * Messages Class
 * 
 * Handles in-app messaging between Admin and OSY/KK members
 */

class Messages
{
    private $db;
    private $table = 'messages';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Send a message
     */
    public function sendMessage($data)
    {
        try {
            $sms_status = $data['sms_status'] ?? 'none';
            $email_status = $data['email_status'] ?? 'none';
            $sms_error = $data['sms_error'] ?? null;
            $email_error = $data['email_error'] ?? null;

            $query = "INSERT INTO {$this->table} 
                     (sender_type, sender_id, recipient_type, recipient_id, message, 
                      sms_status, email_status, sms_error, email_error,
                      is_read, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";

            $this->db->execute($query, [
                $data['sender_type'],
                $data['sender_id'],
                $data['recipient_type'],
                $data['recipient_id'],
                $data['message'],
                $sms_status,
                $email_status,
                $sms_error,
                $email_error
            ], "sisisssss");

            return [
                'success' => true,
                'message' => 'Message sent successfully',
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
     * Get conversation between admin and specific OSY profile
     */
    public function getConversation($osy_id)
    {
        $admin_id = $_SESSION['user_id'];

        $query = "SELECT * FROM {$this->table} 
                 WHERE (sender_type = 'admin' AND sender_id = ? AND recipient_type = 'osy' AND recipient_id = ?)
                 OR (sender_type = 'osy' AND sender_id = ? AND recipient_type = 'admin' AND recipient_id = ?)
                 ORDER BY created_at ASC";

        return $this->db->fetchAll($query, [$admin_id, $osy_id, $osy_id, $admin_id], "iiii");
    }

    /**
     * Get all OSY members who have messages with admin or are available to chat
     */
    public function getChatList()
    {
        $query = "SELECT p.id, p.first_name, p.last_name, p.image_path, p.profile_type, u.phone,
                 (SELECT message FROM {$this->table} 
                  WHERE (sender_type = 'osy' AND sender_id = p.id) 
                  OR (recipient_type = 'osy' AND recipient_id = p.id) 
                  ORDER BY created_at DESC LIMIT 1) as last_message,
                 (SELECT created_at FROM {$this->table} 
                  WHERE (sender_type = 'osy' AND sender_id = p.id) 
                  OR (recipient_type = 'osy' AND recipient_id = p.id) 
                  ORDER BY created_at DESC LIMIT 1) as last_message_time,
                 (SELECT COUNT(*) FROM {$this->table} 
                  WHERE sender_type = 'osy' AND sender_id = p.id AND is_read = 0) as unread_count
                 FROM osy_profiles p LEFT JOIN users u ON u.id = p.created_by
                 ORDER BY last_message_time DESC, p.first_name ASC";

        return $this->db->fetchAll($query);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($osy_id)
    {
        $query = "UPDATE {$this->table} SET is_read = 1 
                 WHERE sender_type = 'osy' AND sender_id = ? AND recipient_type = 'admin'";
        return $this->db->execute($query, [$osy_id], "i");
    }

    /**
     * Get total unread count for admin
     */
    public function getTotalUnreadCount()
    {
        $result = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE recipient_type = 'admin' AND is_read = 0");
        return $result['cnt'] ?? 0;
    }
}
