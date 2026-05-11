-- ============================================
-- Municipal KK Profiling System
-- Database Dump with Sample Data
-- Supports All Youth (15-30) + OSY Specialization
-- Created: 2024
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `municipal_kk_profiling`;
USE `municipal_kk_profiling`;

-- ============================================
-- TABLE: Users (Admin & Staff Accounts)
-- ============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','staff','manager','viewer') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: Youth Profiles (Municipal KK Registry)
-- Supports All Youth 15-30 + OSY Specialization
-- ============================================
CREATE TABLE `osy_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_type` enum('OSY','Regular') DEFAULT 'Regular',
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Solo Parent') DEFAULT 'Single',
  `education_level` varchar(100) DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `primary_skill` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `govt_id_type` varchar(50) DEFAULT NULL,
  `govt_id_number` varchar(100) DEFAULT NULL,
  `govt_id_image` varchar(255) DEFAULT NULL,
  `reason_for_not_in_school` text DEFAULT NULL,
  `engagement_status` varchar(100) DEFAULT NULL,
  `status` enum('Active','In Training','Employed','Inactive') DEFAULT 'Active',
  `date_of_birth` date DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `registration_status` enum('Drafting','Submitted','Approved') DEFAULT 'Drafting',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: Opportunities
-- ============================================
CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('Job Opening','Vocational Training','Scholarship') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `compensation` varchar(100) DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_slots` int(5) DEFAULT 10,
  `deadline` date DEFAULT NULL,
  `status` enum('Open','Closed','Pending') DEFAULT 'Open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: OSY Matches
-- ============================================
CREATE TABLE `osy_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osy_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `match_score` int(3) DEFAULT 0,
  `status` enum('Pending','Accepted','Rejected','In Progress','Completed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`osy_id`) REFERENCES `osy_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_match` (`osy_id`, `opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: Notifications
-- ============================================
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
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: Notification Templates
-- ============================================
CREATE TABLE `notification_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `type` enum('SMS','Email','SMS/Email') DEFAULT 'SMS',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA: Admin & Staff Users (4 Admin Accounts)
-- Password Hashes (bcrypt): All passwords shown in comment
-- ============================================
-- Admin 1: admin1 / Admin@123
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `role`, `is_active`) VALUES
('admin1', 'admin1@civichorizon.ph', '$2y$10$TG43P5/sT628EvnF.agoeufWjP/AzrWhROwZoAaoeyO51Z2lcb46G', 'Senior Administrator', 'admin', 1);

-- Admin 2: admin2 / Admin@456
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `role`, `is_active`) VALUES
('admin2', 'admin2@civichorizon.ph', '$2y$10$a3QPnmKLlwC/4k0q6clwju7aEZCkB/xpnZgy0ILElzfzCDyPYYw8W', 'System Manager', 'admin', 1);

-- Admin 3: admin3 / Admin@789 
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `role`, `is_active`) VALUES
('admin3', 'admin3@civichorizon.ph', '$2y$10$mQ9PL5gA5MesA6AEBX5my.m8YUg/1MtLURUN55AygeKfew3zUmUUi', 'Database Administrator', 'admin', 1);

-- Admin 4: admin4 / Admin@999
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `role`, `is_active`) VALUES
('admin4', 'admin4@civichorizon.ph', '$2y$10$8rPiBNciUDFcyVe7kxlWxekm0dyomVQc5T.JglhAI2STl6KD9G5ve', 'Operations Lead', 'admin', 1);

-- Regular Staff Users (All passwords: Staff@123)
INSERT INTO `users` (`username`, `email`, `password`, `fullname`, `role`, `is_active`) VALUES
('jsmith', 'jsmith@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'John Smith', 'staff', 1),
('mgarcia', 'mgarcia@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Maria Garcia', 'staff', 1),
('rsantos', 'rsantos@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Ricardo Santos', 'manager', 1),
('acruz', 'acruz@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Angela Cruz', 'staff', 1),
('blopez', 'blopez@civichorizon.ph', '$2y$10$6.nh1uzJzWTAy0z5ct4JNeKiXHFFTEEklpDmYdo/58g8/m7DDHw8G', 'Benjamin Lopez', 'viewer', 1);

-- ============================================
-- SAMPLE DATA: OSY Profiles (Sample Data)
-- ============================================
INSERT INTO `osy_profiles` 
(`profile_type`, `first_name`, `last_name`, `email`, `phone`, `age`, `gender`, `education_level`, `barangay`, `primary_skill`, `status`, `date_of_birth`, `created_by`)
VALUES
('OSY', 'Ricardo', 'Santos', 'rsantos.osy@email.com', '09171234567', 21, 'Male', 'High School Graduate', 'Barangay 1', 'Automotive', 'Active', '2003-05-15', 1),
('OSY', 'Maria Elena', 'Dela Cruz', 'm.delacruz@email.com', '09175678901', 19, 'Female', 'Elementary Graduate', 'Barangay 3', 'Culinary', 'Employed', '2005-08-22', 1),
('OSY', 'Roberto', 'Garcia', 'garcia.rob@email.com', '09179876543', 17, 'Male', 'High School Undergraduate', 'Barangay 2', 'IT Support', 'Active', '2006-11-30', 2),
('OSY', 'Patricia', 'Lozano', 'p.lozano@email.com', '09178765432', 22, 'Female', 'High School Graduate', 'Barangay 1', 'Hospitality', 'Inactive', '2002-03-18', 1),
('OSY', 'Juan', 'Dela Cruz', 'jdelacruz@email.com', '09177654321', 20, 'Male', 'High School Graduate', 'Barangay 4', 'Welding', 'Active', '2004-01-10', 2),
('OSY', 'Ana', 'Rivera', 'arivera@email.com', '09176543210', 18, 'Female', 'High School Graduate', 'Barangay 2', 'Welding', 'In Training', '2006-07-25', 2),
('OSY', 'Maria', 'Santos', 'msantos@email.com', '09175432109', 19, 'Female', 'High School Graduate', 'Barangay 1', 'Welding', 'Active', '2005-09-12', 1),
('OSY', 'Ricardo', 'Bautista', 'rbautista@email.com', '09174321098', 21, 'Male', 'High School Graduate', 'Barangay 7', 'Welding', 'Active', '2003-04-08', 2),
('OSY', 'Sofia', 'Reyes', 'sreyes@email.com', '09173210987', 20, 'Female', 'High School Graduate', 'Barangay 3', 'Computer Literacy', 'Active', '2004-06-20', 1),
('OSY', 'Carlos', 'Mendoza', 'cmendoza@email.com', '09172109876', 19, 'Male', 'High School Graduate', 'Barangay 5', 'Carpentry', 'Active', '2005-02-14', 2),
('OSY', 'Rosa', 'Fernandez', 'rfernandez@email.com', '09171098765', 22, 'Female', 'High School Graduate', 'Barangay 2', 'Culinary', 'Employed', '2002-11-28', 1),
('OSY', 'Miguel', 'Torres', 'mtorres@email.com', '09170987654', 20, 'Male', 'High School Undergraduate', 'Barangay 4', 'Electrical', 'Active', '2004-12-05', 2);

-- ============================================
-- SAMPLE DATA: Opportunities
-- ============================================
INSERT INTO `opportunities` 
(`title`, `type`, `location`, `compensation`, `benefits`, `certification`, `description`, `total_slots`, `deadline`, `status`, `created_by`)
VALUES
('TESDA NCII Cookery', 'Vocational Training', 'TESDA-MisOr Hub', NULL, 'Free training materials', 'National Certificate II (NCII)', 'Comprehensive culinary arts and food safety training program', 20, '2024-10-24', 'Open', 1),
('Logistics Assistant', 'Job Opening', 'Port Logistics Corp.', '₱14,500 - ₱16,000', 'Full HMO', NULL, 'Inventory management and logistics support', 5, '2024-11-05', 'Open', 2),
('STEM University Grant', 'Scholarship', 'City Education Board', NULL, 'Full tuition coverage', NULL, 'For students with grade 85+ and indigent status', 50, '2024-11-15', 'Open', 1),
('Basic Web Design', 'Vocational Training', 'Digital Arts Institute', NULL, 'Certificate of Completion', 'Certificate of Completion', 'UI/UX principles and Figma training', 12, '2024-10-01', 'Closed', 2),
('Welding Specialist - NC II', 'Vocational Training', 'TESDA-MisOr Hub', NULL, 'Tools provided', 'NC II Certification', 'Industrial-grade welding expertise for infrastructure projects', 15, '2024-11-30', 'Open', 1),
('BPO Customer Service', 'Job Opening', 'TechCorp Solutions', '₱18,000 - ₱22,000', 'Medical benefits, Meal allowance', NULL, 'Virtual customer support representative', 10, '2024-11-20', 'Open', 2),
('Automotive Technician', 'Job Opening', 'AutoWorks Ltd.', '₱16,000 - ₱20,000', 'HMO, Hazard pay', NULL, 'Vehicle maintenance and repair technician', 8, '2024-11-25', 'Open', 1),
('Computer Literacy & Basic IT', 'Vocational Training', 'Barangay Tech Center', NULL, 'Free', 'Completion Certificate', 'MS Office, Internet basics, Email management', 25, '2024-12-15', 'Open', 2);

-- ============================================
-- SAMPLE DATA: Matches
-- ============================================
INSERT INTO `osy_matches` 
(`osy_id`, `opportunity_id`, `match_score`, `status`)
VALUES
(1, 2, 85, 'Accepted'),
(2, 1, 92, 'Accepted'),
(3, 8, 88, 'Pending'),
(4, 5, 94, 'Accepted'),
(5, 5, 88, 'Pending'),
(6, 5, 87, 'Pending'),
(7, 5, 82, 'Pending'),
(8, 6, 79, 'Pending'),
(9, 8, 84, 'Accepted'),
(10, 7, 81, 'Pending'),
(11, 1, 89, 'Accepted'),
(12, 5, 86, 'Pending'),
(1, 7, 76, 'Pending'),
(2, 6, 73, 'Pending'),
(3, 1, 80, 'Pending');

-- ============================================
-- SAMPLE DATA: Notifications
-- ============================================
INSERT INTO `notifications` 
(`title`, `message`, `type`, `recipient_type`, `status`, `created_by`)
VALUES
('New Opportunity Posted', 'TESDA NCII Welding training is now available with 15 slots. Qualified candidates will be notified.', 'Opportunity', 'OSY', 'Sent', 1),
('Skills Match Found', 'You have been matched with a job opportunity in Logistics!', 'Match', 'Specific', 'Sent', 2),
('Training Started', 'Congratulations! You have been enrolled in the Basic Web Design course.', 'System', 'Specific', 'Read', 1),
('Application Deadline Reminder', 'STEM University Grant applications close in 5 days. Apply now!', 'Reminder', 'OSY', 'Sent', 1),
('Employment Success', 'Maria Elena Dela Cruz has been successfully employed through the system!', 'System', 'All', 'Sent', 2),
('New Admin Registered', 'A new admin account has been created: admin4', 'System', 'All', 'Sent', 1);

-- ============================================
-- SAMPLE DATA: Notification Templates
-- ============================================
INSERT INTO `notification_templates`
(`name`, `subject`, `body`, `type`, `created_by`)
VALUES
('Training Invitation', 'New Training Opportunity', 'Hi {{name}}, we found a training match for you: {{opportunity}}. Please visit the Barangay Hall to enroll.', 'SMS/Email', 1),
('Job Match Alert', 'Job Opportunity Found', 'Hello {{name}}, a new job opportunity at {{company}} matches your skills. Apply now through the Opportunity Hub!', 'Email', 1),
('Registration Confirmation', 'Welcome to Barangay OSY', 'Hi {{name}}, your profile has been successfully registered. You are now part of our skills matching program.', 'SMS', 1),
('Skill Upgrade Suggestion', 'Upskill Recommendation', 'Hi {{name}}, completing the {{course}} course can increase your matching score by {{percentage}}%. Check it out!', 'Email', 1);

-- ============================================
-- Create Indexes for Performance
-- ============================================
CREATE INDEX `idx_osy_status` ON `osy_profiles`(`status`);
CREATE INDEX `idx_osy_skill` ON `osy_profiles`(`primary_skill`);
CREATE INDEX `idx_osy_barangay` ON `osy_profiles`(`barangay`);
CREATE INDEX `idx_opportunity_type` ON `opportunities`(`type`);
CREATE INDEX `idx_opportunity_status` ON `opportunities`(`status`);
CREATE INDEX `idx_matches_osy` ON `osy_matches`(`osy_id`);
CREATE INDEX `idx_matches_opportunity` ON `osy_matches`(`opportunity_id`);
CREATE INDEX `idx_matches_score` ON `osy_matches`(`match_score`);
CREATE INDEX `idx_matches_status` ON `osy_matches`(`status`);
CREATE INDEX `idx_user_role` ON `users`(`role`);
CREATE INDEX `idx_notification_type` ON `notifications`(`type`);

-- ============================================
-- End of Database Dump
-- ============================================
