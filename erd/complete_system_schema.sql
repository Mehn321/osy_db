-- ============================================================
-- Municipal KK Profiling System — Final & Complete Database Schema
-- Represents the finished state of the application with all features:
-- RBAC, AI Matching, Notifications, Auditing, Messaging, etc.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `municipal_kk_profiling_final`;
USE `municipal_kk_profiling_final`;

-- ============================================================
-- 1. SYSTEM & CONFIGURATION
-- ============================================================

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_references` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT 'e.g., skill_category, civil_status, document_type',
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sys_ref_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. LOOKUP TABLES (Reference Data)
-- ============================================================

CREATE TABLE `barangays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_barangay_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `education_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sort_order` int(3) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_education_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_skill_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_interest_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `govt_id_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_govt_id_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. CORE: USERS & AUTHENTICATION (RBAC)
-- ============================================================

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('lydo','sk_chairman','youth','employer','training_provider') DEFAULT 'youth',
  `status` enum('Active','Pending','Declined','Suspended') DEFAULT 'Active',
  `barangay` varchar(100) DEFAULT NULL,
  `provider_type` enum('employer','training_provider') DEFAULT NULL,
  `provider_document_path` varchar(255) DEFAULT NULL,
  `temp_password_required` tinyint(1) DEFAULT 0,
  `approval_remark` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_user_email` (`email`),
  KEY `idx_user_role` (`role`),
  CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. CORE: PROFILES (Youth / OSY)
-- ============================================================

CREATE TABLE `youth_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'Links to youth account if registered online',
  `profile_type` enum('OSY','Regular') DEFAULT 'Regular',
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Solo Parent') DEFAULT 'Single',
  `barangay_id` int(11) DEFAULT NULL,
  `education_level_id` int(11) DEFAULT NULL,
  `reason_for_not_in_school` text DEFAULT NULL,
  `engagement_status` enum('Studying','Working','Self-Employed','Seeking Employment','Unemployed','Homemaker') DEFAULT NULL,
  `status` enum('Active','In Training','Employed','Inactive') DEFAULT 'Active',
  `image_path` varchar(255) DEFAULT NULL,
  `registration_status` enum('Drafting','Submitted','Approved') DEFAULT 'Drafting',
  `verification_status` enum('Drafting','Pending','Verified','Action Required') DEFAULT 'Drafting',
  `verification_remark` text DEFAULT NULL,
  `consent_accepted` tinyint(1) DEFAULT 0,
  `identity_document_path` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_profile_user` (`user_id`),
  KEY `idx_profile_status` (`status`),
  KEY `idx_profile_barangay` (`barangay_id`),
  KEY `idx_profile_type` (`profile_type`),
  CONSTRAINT `fk_yp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_yp_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_yp_education` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_yp_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_yp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PROFILE JUNCTION TABLES (Multi-valued attributes)
-- ============================================================

CREATE TABLE `profile_skills` (
  `profile_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`, `skill_id`),
  KEY `idx_ps_skill` (`skill_id`),
  CONSTRAINT `fk_ps_profile` FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profile_interests` (
  `profile_id` int(11) NOT NULL,
  `interest_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`, `interest_id`),
  KEY `idx_pi_interest` (`interest_id`),
  CONSTRAINT `fk_pi_profile` FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_interest` FOREIGN KEY (`interest_id`) REFERENCES `interests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profile_govt_ids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `govt_id_type_id` int(11) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `id_image_path` varchar(255) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_profile_id_type` (`profile_id`, `govt_id_type_id`),
  KEY `idx_pgi_profile` (`profile_id`),
  CONSTRAINT `fk_pgi_profile` FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pgi_govt_id` FOREIGN KEY (`govt_id_type_id`) REFERENCES `govt_id_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. OPPORTUNITIES & PROGRAMS
-- ============================================================

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('Job Opening','Vocational Training','Scholarship') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_slots` int(5) DEFAULT 10,
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed','Pending') DEFAULT 'Open',
  
  -- Flattened from subtypes for performance, or kept separated based on standard
  `employment_type` varchar(50) DEFAULT NULL,
  `work_schedule` varchar(50) DEFAULT NULL,
  `experience_req` varchar(50) DEFAULT NULL,
  `training_provider` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `modality` varchar(100) DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `compensation` varchar(100) DEFAULT NULL,
  
  `provider_id` int(11) DEFAULT NULL COMMENT 'Employer or Training Provider User ID',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_opp_type` (`type`),
  KEY `idx_opp_status` (`status`),
  KEY `idx_opp_provider` (`provider_id`),
  CONSTRAINT `fk_opp_provider` FOREIGN KEY (`provider_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_opp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `opportunity_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opportunity_id` int(11) NOT NULL,
  `benefit` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ob_opp` (`opportunity_id`),
  CONSTRAINT `fk_ob_opp` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `opportunity_required_skills` (
  `opportunity_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `importance` enum('Required','Preferred','Nice-to-Have') DEFAULT 'Required',
  PRIMARY KEY (`opportunity_id`, `skill_id`),
  KEY `idx_ors_skill` (`skill_id`),
  CONSTRAINT `fk_ors_opp` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ors_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. MATCHING SYSTEM (AI Driven)
-- ============================================================

CREATE TABLE `osy_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `match_score` int(3) DEFAULT 0,
  `status` enum('Pending','Accepted','Rejected','In Progress','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `ai_insight` text DEFAULT NULL COMMENT 'Insights generated by Gemini AI',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_match` (`profile_id`, `opportunity_id`),
  KEY `idx_match_score` (`match_score`),
  KEY `idx_match_status` (`status`),
  CONSTRAINT `fk_om_profile` FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_om_opp` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. COMMUNICATIONS: MESSAGES & NOTIFICATIONS
-- ============================================================

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_type` enum('admin','osy','system') NOT NULL DEFAULT 'admin',
  `sender_id` int(11) NOT NULL,
  `recipient_type` enum('admin','osy') NOT NULL DEFAULT 'osy',
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sms_status` enum('none','pending','success','failed') DEFAULT 'none',
  `email_status` enum('none','pending','success','failed') DEFAULT 'none',
  `sms_error` text DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_recipient` (`recipient_type`, `recipient_id`),
  KEY `idx_messages_sender` (`sender_type`, `sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `type` enum('SMS','Email','SMS/Email') DEFAULT 'SMS',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_nt_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('Opportunity','Match','System','Reminder') DEFAULT 'System',
  `recipient_type` enum('All','OSY','Specific') DEFAULT 'All',
  `recipient_id` int(11) DEFAULT NULL COMMENT 'Specific user ID if recipient_type = Specific',
  `status` enum('Sent','Read','Failed') DEFAULT 'Sent',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_type` (`type`),
  KEY `idx_notif_status` (`status`),
  CONSTRAINT `fk_notif_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_notification_user` (`notification_id`, `user_id`),
  KEY `idx_notification` (`notification_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_nr_notif` FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_nr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_recipients` (
  `notification_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`, `profile_id`),
  KEY `idx_nr_profile` (`profile_id`),
  CONSTRAINT `fk_nrec_notif` FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_nrec_profile` FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. AUDITING & LOGS
-- ============================================================

CREATE TABLE `audit_logs` (
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
  KEY `idx_audit_target` (`target_type`, `target_id`),
  CONSTRAINT `fk_al_actor` FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ai_usage_log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Required Configuration & Lookup
-- ============================================================

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('traccar_token', ''),
('gmail_user', ''),
('gmail_app_password', ''),
('system_name', 'Municipal KK Profiling System'),
('system_version', '2.0.0 (Final)');

INSERT INTO `barangays` (`name`) VALUES
('Baga'), ('Bangko'), ('Camanucan'), ('Dela Paz'), ('Lutao'),
('Magsaysay'), ('Map-an'), ('Mohon'), ('Poblacion'), ('Punta'),
('Salimpuno'), ('San Andres'), ('San Juan'), ('San Roque'),
('Sumasap'), ('Villalin');

INSERT INTO `education_levels` (`name`, `sort_order`) VALUES
('No Formal Education', 1), ('Elementary Undergraduate', 2), ('Elementary Graduate', 3), 
('High School Undergraduate', 4), ('High School Graduate', 5), ('Senior High School', 6), 
('College Undergraduate', 7), ('College Graduate', 8), ('Vocational/Technical', 9), 
('Post-Graduate', 10);

-- End of File
