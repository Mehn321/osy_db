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

    $defaultSettings = [
        'traccar_token' => '',
        'traccar_api_url' => 'http://192.168.100.41:8082/, http://172.18.11.218:8082/, https://www.traccar.org/sms/',
        'gmail_user' => '',
        'gmail_app_password' => ''
    ];
    foreach ($defaultSettings as $k => $val) {
        $conn->query("INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`)
                      VALUES ('" . $database->escape($k) . "', '" . $database->escape($val) . "')");
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
        'recipient_type' => "ALTER TABLE `messages` ADD COLUMN `recipient_type` ENUM('admin','osy') NOT NULL DEFAULT 'osy' AFTER `sender_id`",
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

    // ── 2. users table – extend RBAC fields and workflows ───────────────────────
    $existingUserCols = array_column($database->fetchAll("SHOW COLUMNS FROM `users`"), 'Field');
    $roleColumn = $database->fetchOne("SHOW COLUMNS FROM `users` LIKE 'role'");
    if ($roleColumn && strpos($roleColumn['Type'], 'lydo') === false) {
        $conn->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','lydo','sk_chairman','youth','employer','training_provider') DEFAULT 'lydo'");
    }

    if (!in_array('status', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `status` ENUM('Active','Pending','Declined','Suspended') DEFAULT 'Active' AFTER `is_active`");
    }

    if (!in_array('barangay', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `barangay` varchar(100) DEFAULT NULL AFTER `status`");
    }

    if (!in_array('provider_type', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `provider_type` ENUM('employer','training_provider') DEFAULT NULL AFTER `barangay`");
    }

    if (!in_array('provider_document_path', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `provider_document_path` varchar(255) DEFAULT NULL AFTER `provider_type`");
    }

    if (!in_array('temp_password_required', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `temp_password_required` TINYINT(1) DEFAULT 0 AFTER `provider_document_path`");
    }

    if (!in_array('approval_remark', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `approval_remark` TEXT DEFAULT NULL AFTER `temp_password_required`");
    }

    if (!in_array('created_by', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `approval_remark`");
    }

    if (!in_array('updated_at', $existingUserCols)) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
    }

    // ── 3. osy_profiles table – add youth verification workflow fields ─────────
    $existingProfileCols = array_column($database->fetchAll("SHOW COLUMNS FROM `osy_profiles`"), 'Field');

    if (!in_array('verification_status', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `verification_status` ENUM('Drafting','Pending','Verified','Action Required') DEFAULT 'Drafting' AFTER `registration_status`");
    }

    if (!in_array('verification_remark', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `verification_remark` TEXT DEFAULT NULL AFTER `verification_status`");
    }

    if (!in_array('consent_accepted', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `consent_accepted` TINYINT(1) DEFAULT 0 AFTER `verification_remark`");
    }

    if (!in_array('identity_document_path', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `identity_document_path` varchar(255) DEFAULT NULL AFTER `consent_accepted`");
    }

    if (!in_array('approved_by', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `approved_by` int(11) DEFAULT NULL AFTER `identity_document_path`");
    }

    if (!in_array('approved_at', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `approved_at` datetime DEFAULT NULL AFTER `approved_by`");
    }

    if (!in_array('barangay_id', $existingProfileCols)) {
        $conn->query("ALTER TABLE `osy_profiles` ADD COLUMN `barangay_id` int(11) DEFAULT NULL AFTER `barangay`");
    }

    // ── 4. notifications table – support specific recipients ────────────────────
    $existingNotifCols = array_column($database->fetchAll("SHOW COLUMNS FROM `notifications`"), 'Field');

    // Rename user_id to created_by if needed
    if (in_array('user_id', $existingNotifCols) && !in_array('created_by', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` CHANGE `user_id` `created_by` int(11) DEFAULT NULL");
    } elseif (!in_array('created_by', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `type`");
    }

    if (!in_array('recipient_id', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `recipient_id` int(11) DEFAULT NULL AFTER `recipient_type`");
    }

    if (!in_array('recipient_type', $existingNotifCols)) {
        $conn->query("ALTER TABLE `notifications` ADD COLUMN `recipient_type` ENUM('All','OSY','Specific') DEFAULT 'All' AFTER `type`");
    }

    // ── 5. audit_logs table – create action history ─────────────────────────────
    $conn->query("CREATE TABLE IF NOT EXISTS `audit_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `actor_id` int(11) DEFAULT NULL,
        `actor_role` varchar(50) DEFAULT NULL,
        `action` varchar(255) NOT NULL,
        `target_type` varchar(100) DEFAULT NULL,
        `target_id` int(11) DEFAULT NULL,
        `metadata` text DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_audit_actor` (`actor_id`),
        KEY `idx_audit_target` (`target_type`, `target_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ── 6. notification_templates – fix missing columns ────────────────────────
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

    // ── 6a. notification_reads table – add per-user read tracking ─────────────
    $existingNotifReadTable = $database->fetchAll("SHOW TABLES LIKE 'notification_reads'");
    if (empty($existingNotifReadTable)) {
        $conn->query("CREATE TABLE IF NOT EXISTS `notification_reads` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `notification_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `read_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_notification_user` (`notification_id`, `user_id`),
            KEY `idx_notification` (`notification_id`),
            KEY `idx_user` (`user_id`),
            FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // ── 7. opportunities – extra columns ──────────────────────────────────────
    $oppCols = array_column($database->fetchAll("SHOW COLUMNS FROM `opportunities`"), 'Field');
    $oppColMigrations = [
        'employment_type'   => "ALTER TABLE `opportunities` ADD COLUMN `employment_type` varchar(50) DEFAULT NULL AFTER `type`",
        'work_schedule'     => "ALTER TABLE `opportunities` ADD COLUMN `work_schedule` varchar(50) DEFAULT NULL AFTER `employment_type`",
        'experience_req'    => "ALTER TABLE `opportunities` ADD COLUMN `experience_req` varchar(50) DEFAULT NULL AFTER `work_schedule`",
        'training_provider' => "ALTER TABLE `opportunities` ADD COLUMN `training_provider` varchar(255) DEFAULT NULL AFTER `experience_req`",
        'duration'          => "ALTER TABLE `opportunities` ADD COLUMN `duration` varchar(100) DEFAULT NULL AFTER `training_provider`",
        'modality'          => "ALTER TABLE `opportunities` ADD COLUMN `modality` varchar(100) DEFAULT NULL AFTER `duration`",
        'provider_id'       => "ALTER TABLE `opportunities` ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `created_by`",
    ];
    foreach ($oppColMigrations as $col => $sql) {
        if (!in_array($col, $oppCols)) {
            $conn->query($sql);
        }
    }

    // ── 8. osy_matches – AI insights column ──────────────────────────────────
    $matchCols = array_column($database->fetchAll("SHOW COLUMNS FROM `osy_matches`"), 'Field');
    if (!in_array('ai_insight', $matchCols)) {
        $conn->query("ALTER TABLE `osy_matches` ADD COLUMN `ai_insight` TEXT DEFAULT NULL AFTER `notes`");
    }

    // ── 7. Seed sample notification templates if none exist ───────────────────
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

    // ── 8. ai_usage_log table – track Gemini API usage ─────────────────────────
    $conn->query("CREATE TABLE IF NOT EXISTS `ai_usage_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `endpoint` varchar(100) NOT NULL DEFAULT 'generateContent',
        `model` varchar(100) DEFAULT NULL,
        `prompt_tokens` int(11) DEFAULT 0,
        `response_tokens` int(11) DEFAULT 0,
        `total_tokens` int(11) DEFAULT 0,
        `http_status` int(5) DEFAULT 200,
        `success` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_ai_usage_date` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ── 9. Merge admin → lydo role ────────────────────────────────────────────
    $adminCount = $database->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin'");
    if (($adminCount['cnt'] ?? 0) > 0) {
        $conn->query("UPDATE users SET role = 'lydo' WHERE role = 'admin'");
    }
} catch (Exception $e) {
    // Silent – migrations must never interrupt page loads
    error_log('OSY Migration Error: ' . $e->getMessage());
}
