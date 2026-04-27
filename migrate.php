<?php
/**
 * migrate.php – idempotent migrations, safe to run every request.
 * Included by init.php automatically. $database must already be defined.
 */
if (!isset($database)) return;

try {
    $conn = $database->getConnection();

    // ── 1. system_settings table ──────────────────────────────────────────────
    $conn->query("CREATE TABLE IF NOT EXISTS `system_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` TEXT DEFAULT NULL,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    foreach (['traccar_token', 'gmail_user', 'gmail_app_password'] as $k) {
        $conn->query("INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`)
                      VALUES ('" . $database->escape($k) . "', '')");
    }

    // ── 2. messages table ─────────────────────────────────────────────────────
    $conn->query("CREATE TABLE IF NOT EXISTS `messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sender_type` ENUM('admin','osy') NOT NULL,
        `sender_id` int(11) NOT NULL,
        `recipient_type` ENUM('admin','osy') NOT NULL,
        `recipient_id` int(11) NOT NULL,
        `message` TEXT NOT NULL,
        `sms_status` ENUM('none','pending','success','failed') DEFAULT 'none',
        `email_status` ENUM('none','pending','success','failed') DEFAULT 'none',
        `sms_error` TEXT DEFAULT NULL,
        `email_error` TEXT DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_messages_recipient` (`recipient_type`, `recipient_id`),
        KEY `idx_messages_sender` (`sender_type`, `sender_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Add any missing columns to messages
    $existingMsgCols = array_column($database->fetchAll("SHOW COLUMNS FROM `messages`"), 'Field');
    $msgColMigrations = [
        'sender_type'   => "ALTER TABLE `messages` ADD COLUMN `sender_type` ENUM('admin','osy') NOT NULL DEFAULT 'admin' AFTER `id`",
        'recipient_type'=> "ALTER TABLE `messages` ADD COLUMN `recipient_type` ENUM('admin','osy') NOT NULL DEFAULT 'osy' AFTER `sender_id`",
        'recipient_id'  => "ALTER TABLE `messages` ADD COLUMN `recipient_id` int(11) NOT NULL DEFAULT 0 AFTER `recipient_type`",
        'sms_status'    => "ALTER TABLE `messages` ADD COLUMN `sms_status` ENUM('none','pending','success','failed') DEFAULT 'none' AFTER `message`",
        'email_status'  => "ALTER TABLE `messages` ADD COLUMN `email_status` ENUM('none','pending','success','failed') DEFAULT 'none' AFTER `sms_status`",
        'sms_error'     => "ALTER TABLE `messages` ADD COLUMN `sms_error` TEXT DEFAULT NULL AFTER `email_status`",
        'email_error'   => "ALTER TABLE `messages` ADD COLUMN `email_error` TEXT DEFAULT NULL AFTER `sms_error`",
        'is_read'       => "ALTER TABLE `messages` ADD COLUMN `is_read` TINYINT(1) DEFAULT 0 AFTER `email_error`",
    ];
    foreach ($msgColMigrations as $col => $sql) {
        if (!in_array($col, $existingMsgCols)) {
            $conn->query($sql);
        }
    }

    // ── 3. notifications table – fix schema differences ─────────────────────────
    $existingNotifCols = array_column($database->fetchAll("SHOW COLUMNS FROM `notifications`"), 'Field');
    
    // Rename user_id to created_by if needed
    if (in_array('user_id', $existingNotifCols) && !in_array('created_by', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` CHANGE `user_id` `created_by` int(11) DEFAULT NULL");
    } elseif (!in_array('created_by', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `type`");
    }

    // Rename is_read to status if needed
    if (in_array('is_read', $existingNotifCols) && !in_array('status', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` CHANGE `is_read` `status` varchar(50) DEFAULT 'Sent'");
    } elseif (!in_array('status', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `status` varchar(50) DEFAULT 'Sent' AFTER `type`");
    }

    if (!in_array('recipient_type', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `recipient_type` ENUM('All','OSY','Staff','Specific') DEFAULT 'All' AFTER `type`");
    }


    // ── 4. notification_templates – fix missing columns ────────────────────────
    $existingTmplCols = array_column($database->fetchAll("SHOW COLUMNS FROM `notification_templates`"), 'Field');
    $tmplColMigrations = [
        'subject'    => "ALTER TABLE `notification_templates` ADD COLUMN `subject` varchar(255) DEFAULT NULL AFTER `name`",
        'type'       => "ALTER TABLE `notification_templates` ADD COLUMN `type` ENUM('SMS','Email','SMS/Email') DEFAULT 'SMS' AFTER `body`",
        'created_by' => "ALTER TABLE `notification_templates` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `type`",
        'updated_at' => "ALTER TABLE `notification_templates` ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`",
    ];
    foreach ($tmplColMigrations as $col => $sql) {
        if (!in_array($col, $existingTmplCols)) {
            $conn->query($sql);
        }
    }

    // ── 5. opportunities – extra columns ──────────────────────────────────────
    $oppCols = array_column($database->fetchAll("SHOW COLUMNS FROM `opportunities`"), 'Field');
    $oppColMigrations = [
        'employment_type'   => "ALTER TABLE `opportunities` ADD COLUMN `employment_type` varchar(50) DEFAULT NULL AFTER `type`",
        'work_schedule'     => "ALTER TABLE `opportunities` ADD COLUMN `work_schedule` varchar(50) DEFAULT NULL AFTER `employment_type`",
        'experience_req'    => "ALTER TABLE `opportunities` ADD COLUMN `experience_req` varchar(50) DEFAULT NULL AFTER `work_schedule`",
        'training_provider' => "ALTER TABLE `opportunities` ADD COLUMN `training_provider` varchar(255) DEFAULT NULL AFTER `experience_req`",
        'duration'          => "ALTER TABLE `opportunities` ADD COLUMN `duration` varchar(100) DEFAULT NULL AFTER `training_provider`",
        'modality'          => "ALTER TABLE `opportunities` ADD COLUMN `modality` varchar(100) DEFAULT NULL AFTER `duration`",
    ];
    foreach ($oppColMigrations as $col => $sql) {
        if (!in_array($col, $oppCols)) {
            $conn->query($sql);
        }
    }

    // ── 6. Seed sample notification templates if none exist ───────────────────
    $tmplCount = $database->fetchOne("SELECT COUNT(*) as cnt FROM `notification_templates`");
    if (($tmplCount['cnt'] ?? 0) == 0) {
        $conn->query("INSERT INTO `notification_templates` (`name`, `subject`, `body`, `type`, `created_by`, `created_at`) VALUES
            ('Training Invitation', 'New Training Opportunity for You!', 'Hi {{name}}, maganda na balita! Nahanap kami ng training na para sa inyong skills: {{opportunity}}. Pumunta na sa Barangay Hall para mag-enroll. Salamat!', 'SMS/Email', 1, NOW()),
            ('Job Match Alert', 'Job Opportunity Found!', 'Hello {{name}}, isang bagong trabaho sa {{company}} ay nagrekomenda para sa iyo batay sa iyong skills. Mag-apply na ngayon sa pamamagitan ng aming Opportunity Hub!', 'Email', 1, NOW()),
            ('Registration Confirmation', NULL, 'Hi {{name}}, matagumpay na na-register ang inyong profile sa Municipal OSY Program. Ikaw na ay bahagi na ng aming Skills Matching System. Maraming salamat!', 'SMS', 1, NOW()),
            ('Skill Upgrade Suggestion', 'Upskill Recommendation', 'Hi {{name}}, ang pagkumpleto ng {{course}} course ay magpapataas ng iyong match score ng {{percentage}}%. Bisitahin ang Barangay Hall para sa detalye!', 'Email', 1, NOW()),
            ('Deadline Reminder', 'Application Deadline Soon!', 'Hi {{name}}, ang deadline para sa {{opportunity}} ay malapit na. Huwag palampasin ang pagkakataong ito! Makipag-ugnayan sa aming tanggapan agad.', 'SMS/Email', 1, NOW()),
            ('Employment Congratulations', 'Congratulations on Your Employment!', 'Maligayang Bati, {{name}}! Matagumpay kang natanggap bilang empleyado. Ito ay bunga ng iyong pagsisikap at ng iyong pagsali sa aming OSY Program. Patuloy kang lumago!', 'SMS/Email', 1, NOW())
        ");
    }

    // ── 7. Seed sample notifications if none exist ────────────────────────────
    $notifCount = $database->fetchOne("SELECT COUNT(*) as cnt FROM `notifications`");
    if (($notifCount['cnt'] ?? 0) == 0) {
        $conn->query("INSERT INTO `notifications` (`title`, `message`, `type`, `recipient_type`, `status`, `created_by`, `created_at`) VALUES
            ('New Opportunity Posted', 'TESDA NCII Welding training is now available with 15 slots. Qualified candidates will be notified.', 'Opportunity', 'OSY', 'Sent', 1, NOW()),
            ('Skills Match Found', 'You have been matched with a job opportunity in Logistics!', 'Match', 'Specific', 'Sent', 1, NOW()),
            ('Training Started', 'Congratulations! You have been enrolled in the Basic Web Design course.', 'System', 'Specific', 'Read', 1, NOW()),
            ('Application Deadline Reminder', 'STEM University Grant applications close in 5 days. Apply now!', 'Reminder', 'OSY', 'Sent', 1, NOW())
        ");
    }

} catch (Exception $e) {
    // Silent – migrations must never interrupt page loads
    error_log('OSY Migration Error: ' . $e->getMessage());
}
