-- ============================================================
-- Municipal KK Profiling System — Normalized Database Schema
-- Normalized: 1NF → 2NF → 3NF → BCNF → 4NF → 5NF
-- ============================================================

CREATE DATABASE IF NOT EXISTS `municipal_kk_profiling_normalized`;
USE `municipal_kk_profiling_normalized`;

-- ============================================================
-- LOOKUP TABLES (Reference Data)
-- ============================================================

-- TABLE: Barangays (replaces raw text in osy_profiles.barangay)
-- 2NF fix: eliminates partial dependency on non-key attribute
CREATE TABLE `barangays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_barangay_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Education Levels (replaces raw text in osy_profiles.education_level)
-- 2NF fix: eliminates partial dependency
CREATE TABLE `education_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sort_order` int(3) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_education_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Skills Catalog (replaces comma-separated osy_profiles.skills)
-- 1NF fix: atomic values
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'e.g. Technical, Creative, Service',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_skill_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Interests Catalog (replaces comma-separated osy_profiles.interests)
-- 1NF fix: atomic values
CREATE TABLE `interests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_interest_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Government ID Types (replaces raw text in osy_profiles.govt_id_type)
-- 2NF fix: normalized lookup
CREATE TABLE `govt_id_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_govt_id_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CORE TABLES
-- ============================================================

-- TABLE: Users (Admin & Staff Accounts)
-- Already in BCNF: id is the sole determinant, username and email are candidate keys
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','staff','manager','viewer') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Youth Profiles (formerly osy_profiles)
-- 1NF: skills, interests, govt_id extracted to junction tables
-- 2NF: barangay → barangay_id FK, education_level → education_level_id FK
-- 3NF: removed age (derived from date_of_birth), removed primary_skill (flag in junction)
CREATE TABLE `youth_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_profile_status` (`status`),
  KEY `idx_profile_barangay` (`barangay_id`),
  KEY `idx_profile_type` (`profile_type`),
  FOREIGN KEY (`barangay_id`) REFERENCES `barangays`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`education_level_id`) REFERENCES `education_levels`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- JUNCTION / BRIDGE TABLES (Multi-valued dependencies → 4NF)
-- ============================================================

-- TABLE: Profile ↔ Skills (1NF + 4NF fix)
-- Replaces osy_profiles.skills (comma-separated) and osy_profiles.primary_skill
-- 3NF: is_primary flag eliminates need for separate primary_skill column
-- 5NF: (profile_id, skill_id, is_primary) — is_primary depends on full composite key
CREATE TABLE `profile_skills` (
  `profile_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0 COMMENT '1 = this is the primary skill for this profile',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`, `skill_id`),
  KEY `idx_ps_skill` (`skill_id`),
  FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Profile ↔ Interests (1NF + 4NF fix)
-- Replaces osy_profiles.interests (comma-separated)
-- Independent multi-valued fact from skills → separate table (4NF)
CREATE TABLE `profile_interests` (
  `profile_id` int(11) NOT NULL,
  `interest_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`, `interest_id`),
  KEY `idx_pi_interest` (`interest_id`),
  FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`interest_id`) REFERENCES `interests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Profile Government IDs (1NF + 4NF fix)
-- Replaces osy_profiles.govt_id_type/govt_id_number/govt_id_image
-- Supports multiple IDs per person (4NF: multi-valued dependency)
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
  FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`govt_id_type_id`) REFERENCES `govt_id_types`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- OPPORTUNITIES (3NF: Type-specific subtype tables)
-- ============================================================

-- TABLE: Opportunities (base table — shared attributes only)
-- 3NF fix: type-specific nullable columns extracted to subtype tables
CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('Job Opening','Vocational Training','Scholarship') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_slots` int(5) DEFAULT 10,
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed','Pending') DEFAULT 'Open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_opp_type` (`type`),
  KEY `idx_opp_status` (`status`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Job Opening Details (3NF subtype for type='Job Opening')
CREATE TABLE `opportunity_job_details` (
  `opportunity_id` int(11) NOT NULL,
  `compensation` varchar(100) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL COMMENT 'Full-time, Part-time, Contractual',
  `work_schedule` varchar(50) DEFAULT NULL,
  `experience_req` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`opportunity_id`),
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Training Details (3NF subtype for type='Vocational Training')
CREATE TABLE `opportunity_training_details` (
  `opportunity_id` int(11) NOT NULL,
  `training_provider` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `modality` enum('Face-to-Face','Online','Blended') DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`opportunity_id`),
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Scholarship Details (3NF subtype for type='Scholarship')
CREATE TABLE `opportunity_scholarship_details` (
  `opportunity_id` int(11) NOT NULL,
  `coverage` varchar(255) DEFAULT NULL COMMENT 'e.g. Full tuition, Partial, Stipend',
  `eligibility_criteria` text DEFAULT NULL,
  `sponsoring_org` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`opportunity_id`),
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Opportunity Benefits (1NF fix — was comma-separated text)
CREATE TABLE `opportunity_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opportunity_id` int(11) NOT NULL,
  `benefit` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ob_opp` (`opportunity_id`),
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Skills Required by Opportunity (for matching algorithm)
-- Links opportunities to required skills for better match scoring
CREATE TABLE `opportunity_required_skills` (
  `opportunity_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `importance` enum('Required','Preferred','Nice-to-Have') DEFAULT 'Required',
  PRIMARY KEY (`opportunity_id`, `skill_id`),
  KEY `idx_ors_skill` (`skill_id`),
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MATCHING
-- ============================================================

-- TABLE: Profile ↔ Opportunity Matches
-- 5NF verified: cannot be losslessly decomposed — match_score, status, notes
-- all depend on the full (profile_id, opportunity_id) composite
CREATE TABLE `osy_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `match_score` int(3) DEFAULT 0,
  `status` enum('Pending','Accepted','Rejected','In Progress','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_match` (`profile_id`, `opportunity_id`),
  KEY `idx_match_score` (`match_score`),
  KEY `idx_match_status` (`status`),
  FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================

-- TABLE: Notifications
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('Opportunity','Match','System','Reminder') DEFAULT 'System',
  `recipient_type` enum('All','OSY','Staff','Specific') DEFAULT 'All',
  `status` enum('Sent','Read','Failed') DEFAULT 'Sent',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_type` (`type`),
  KEY `idx_notif_status` (`status`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Notification Recipients (4NF fix — specific recipient tracking)
-- When recipient_type = 'Specific', individual recipients are tracked here
CREATE TABLE `notification_recipients` (
  `notification_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`, `profile_id`),
  KEY `idx_nr_profile` (`profile_id`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`profile_id`) REFERENCES `youth_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLE: Notification Templates
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
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SYSTEM TABLES
-- ============================================================

-- TABLE: System Settings (key-value store — BCNF compliant)
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA: Lookup Tables
-- ============================================================

INSERT INTO `barangays` (`name`) VALUES
('Baga'), ('Bangko'), ('Camanucan'), ('Dela Paz'), ('Lutao'),
('Magsaysay'), ('Map-an'), ('Mohon'), ('Poblacion'), ('Punta'),
('Salimpuno'), ('San Andres'), ('San Juan'), ('San Roque'),
('Sumasap'), ('Villalin');

INSERT INTO `education_levels` (`name`, `sort_order`) VALUES
('No Formal Education', 1),
('Elementary Undergraduate', 2),
('Elementary Graduate', 3),
('High School Undergraduate', 4),
('High School Graduate', 5),
('Senior High School', 6),
('College Undergraduate', 7),
('College Graduate', 8),
('Vocational/Technical', 9),
('Post-Graduate', 10);

INSERT INTO `skills` (`name`, `category`) VALUES
('Automotive', 'Technical'),
('Carpentry', 'Technical'),
('Computer Literacy', 'IT'),
('Culinary', 'Service'),
('Electrical', 'Technical'),
('Hospitality', 'Service'),
('IT Support', 'IT'),
('Welding', 'Technical'),
('Plumbing', 'Technical'),
('Dressmaking', 'Creative'),
('Agriculture', 'Agriculture'),
('Web Development', 'IT'),
('Customer Service', 'Service'),
('Accounting', 'Office');

INSERT INTO `interests` (`name`) VALUES
('Sports'), ('Music'), ('Arts'), ('Technology'),
('Cooking'), ('Reading'), ('Business'), ('Community Service');

INSERT INTO `govt_id_types` (`name`) VALUES
('PhilSys / National ID'),
('Birth Certificate'),
('School ID'),
('Barangay ID'),
('Voter\'s ID'),
('Passport'),
('Driver\'s License'),
('SSS ID'),
('PhilHealth ID');

-- ============================================================
-- SEED DATA: Default System Settings
-- ============================================================

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('traccar_token', ''),
('gmail_user', ''),
('gmail_app_password', ''),
('system_name', 'Municipal KK Profiling System'),
('system_version', '1.0.0');

-- ============================================================
-- End of Normalized Schema
-- ============================================================
