<?php

/**
 * TraccarNotificationService Class
 * 
 * Handles sending notifications specifically for the Traccar mobile app.
 * Supports notifications from LYDO, SK (Sangguniang Kabataan), and Opportunity Providers.
 * Stores notifications in the app's database for retrieval by the Traccar app.
 */

class TraccarNotificationService
{
    private $db;
    private $table = 'traccar_notifications';

    public function __construct($database)
    {
        $this->db = $database;
        $this->ensureTableExists();
    }

    /**
     * Ensure the traccar_notifications table exists
     */
    private function ensureTableExists()
    {
        try {
            $this->db->execute(
                "CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    sender_id INT NOT NULL,
                    sender_role VARCHAR(50) NOT NULL,
                    sender_name VARCHAR(255),
                    recipient_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message LONGTEXT NOT NULL,
                    notification_type VARCHAR(50) DEFAULT 'general',
                    is_read BOOLEAN DEFAULT FALSE,
                    read_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_recipient (recipient_id),
                    INDEX idx_created (created_at),
                    INDEX idx_read_status (is_read),
                    FOREIGN KEY (recipient_id) REFERENCES osy_profiles(id) ON DELETE CASCADE
                )",
                [],
                ""
            );
        } catch (Exception $e) {
            // Table might already exist
        }
    }

    /**
     * Send notification from LYDO to youth/recipients
     * 
     * @param int $senderId The LYDO user ID
     * @param int|array $recipientIds Single recipient ID or array of recipient IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (opportunity, alert, reminder, general)
     * @return array Success status and details
     */
    public function sendFromLydo($senderId, $recipientIds, $title, $message, $type = 'general')
    {
        return $this->sendNotification($senderId, 'lydo', $recipientIds, $title, $message, $type);
    }

    /**
     * Send notification from SK (Sangguniang Kabataan) to youth
     * 
     * @param int $senderId The SK user ID
     * @param int|array $recipientIds Single recipient ID or array of recipient IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (opportunity, alert, reminder, general)
     * @return array Success status and details
     */
    public function sendFromSK($senderId, $recipientIds, $title, $message, $type = 'general')
    {
        return $this->sendNotification($senderId, 'sk_chairman', $recipientIds, $title, $message, $type);
    }

    /**
     * Send notification from Opportunity Provider to youth
     * 
     * @param int $senderId The provider user ID
     * @param int|array $recipientIds Single recipient ID or array of recipient IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type (opportunity, alert, reminder, general)
     * @return array Success status and details
     */
    public function sendFromProvider($senderId, $recipientIds, $title, $message, $type = 'opportunity')
    {
        return $this->sendNotification($senderId, 'provider', $recipientIds, $title, $message, $type);
    }

    /**
     * Core notification sending logic
     * 
     * @param int $senderId The sender user ID
     * @param string $senderRole The sender's role (lydo, sk_chairman, provider)
     * @param int|array $recipientIds Single recipient ID or array of recipient IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $type Notification type
     * @return array Success status and details
     */
    private function sendNotification($senderId, $senderRole, $recipientIds, $title, $message, $type = 'general')
    {
        if (empty($senderId) || empty($senderRole) || empty($recipientIds)) {
            return ['success' => false, 'message' => 'Missing required parameters.'];
        }

        if (empty($title) || empty($message)) {
            return ['success' => false, 'message' => 'Title and message are required.'];
        }

        // Get sender information
        $sender = $this->db->fetchOne(
            "SELECT id, fullname as full_name FROM users WHERE id = ? LIMIT 1",
            [$senderId],
            "i"
        );

        if (!$sender) {
            return ['success' => false, 'message' => 'Sender not found.'];
        }

        $senderName = $sender['full_name'] ?? 'System';

        // Normalize recipient IDs to array
        if (!is_array($recipientIds)) {
            $recipientIds = [$recipientIds];
        }

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        foreach ($recipientIds as $recipientId) {
            try {
                $query = "INSERT INTO {$this->table} 
                         (sender_id, sender_role, sender_name, recipient_id, title, message, notification_type, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

                $this->db->execute(
                    $query,
                    [$senderId, $senderRole, $senderName, $recipientId, $title, $message, $type],
                    "ississs"
                );

                $successCount++;
            } catch (Exception $e) {
                $failureCount++;
                $errors[] = "Recipient ID $recipientId: " . $e->getMessage();
            }
        }

        $result = [
            'success' => $failureCount === 0,
            'message' => "Notifications sent to $successCount recipients" . ($failureCount > 0 ? ", $failureCount failed" : ""),
            'sent' => $successCount,
            'failed' => $failureCount,
        ];

        if (!empty($errors)) {
            $result['errors'] = array_slice($errors, 0, 5);
        }

        return $result;
    }

    /**
     * Get unread notifications for a recipient (youth)
     * 
     * @param int $recipientId The youth profile ID
     * @param int $limit Maximum number of notifications to return
     * @return array List of unread notifications
     */
    public function getUnreadNotifications($recipientId, $limit = 50)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE recipient_id = ? AND is_read = FALSE
             ORDER BY created_at DESC
             LIMIT ?",
            [$recipientId, $limit],
            "ii"
        ) ?: [];
    }

    /**
     * Get all notifications for a recipient
     * 
     * @param int $recipientId The youth profile ID
     * @param int $limit Maximum number of notifications to return
     * @return array List of notifications
     */
    public function getNotifications($recipientId, $limit = 100)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} 
             WHERE recipient_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$recipientId, $limit],
            "ii"
        ) ?: [];
    }

    /**
     * Get notification by ID
     * 
     * @param int $notificationId
     * @return array Notification details
     */
    public function getNotification($notificationId)
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1",
            [$notificationId],
            "i"
        );
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @return array Success status
     */
    public function markAsRead($notificationId)
    {
        try {
            $this->db->execute(
                "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() WHERE id = ?",
                [$notificationId],
                "i"
            );
            return ['success' => true, 'message' => 'Notification marked as read.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mark all notifications as read for a recipient
     * 
     * @param int $recipientId
     * @return array Success status
     */
    public function markAllAsRead($recipientId)
    {
        try {
            $this->db->execute(
                "UPDATE {$this->table} SET is_read = TRUE, read_at = NOW() WHERE recipient_id = ? AND is_read = FALSE",
                [$recipientId],
                "i"
            );
            return ['success' => true, 'message' => 'All notifications marked as read.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get unread notification count for a recipient
     * 
     * @param int $recipientId
     * @return int Count of unread notifications
     */
    public function getUnreadCount($recipientId)
    {
        try {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE recipient_id = ? AND is_read = FALSE",
                [$recipientId],
                "i"
            );
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Delete notification
     * 
     * @param int $notificationId
     * @return array Success status
     */
    public function deleteNotification($notificationId)
    {
        try {
            $this->db->execute(
                "DELETE FROM {$this->table} WHERE id = ?",
                [$notificationId],
                "i"
            );
            return ['success' => true, 'message' => 'Notification deleted.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Clear old notifications (older than specified days)
     * 
     * @param int $daysOld Delete notifications older than this many days
     * @return array Success status and count
     */
    public function clearOldNotifications($daysOld = 30)
    {
        try {
            $this->db->execute(
                "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$daysOld],
                "i"
            );
            return ['success' => true, 'message' => "Old notifications cleared."];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
