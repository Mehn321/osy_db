    <?php
    /**
     * Database Setup: Initialize Municipal KK Profiling System
     * This script will:
     * 1. Create the new database
     * 2. Create tables from SQL schema
     * 3. Apply migrations
     */

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_OLD_NAME', 'civic_horizon_osy');
    define('DB_NEW_NAME', 'municipal_kk_profiling');

    echo "🔄 Setting up Municipal KK Profiling System...\n\n";

    try {
        // Connect to MySQL (no database specified initially)
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($conn->connect_error) {
            die("❌ Connection failed: " . $conn->connect_error);
        }

        // Step 1: Check if old database exists and needs migration
        if ($conn->select_db(DB_OLD_NAME)) {
            echo "📦 Found existing database: " . DB_OLD_NAME . "\n";

            // Create new database
            if ($conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NEW_NAME)) {
                echo "✓ New database created: " . DB_NEW_NAME . "\n";
            }

            // Migrate data from old database
            echo "\n📋 Migrating data...\n";

            // Copy osy_profiles table
            $sql = "CREATE TABLE IF NOT EXISTS " . DB_NEW_NAME . ".osy_profiles LIKE " . DB_OLD_NAME . ".osy_profiles";
            if ($conn->query($sql)) {
                echo "✓ Profile table schema copied\n";
            }

            // Copy osy_profiles table with basic columns that should exist
            $sql = "INSERT IGNORE INTO " . DB_NEW_NAME . ".osy_profiles 
                    (id, first_name, last_name, email, phone, age, gender, education_level, barangay, primary_skill, skills, interests, govt_id_type, reason_for_not_in_school, status, date_of_birth, image_path, registration_status, created_by, created_at, updated_at)
                    SELECT id, first_name, last_name, email, phone, age, gender, education_level, barangay, primary_skill, skills, interests, govt_id_type, reason_for_not_in_school, status, date_of_birth, image_path, registration_status, created_by, created_at, updated_at 
                    FROM " . DB_OLD_NAME . ".osy_profiles";
            if ($conn->query($sql)) {
                $affected = $conn->affected_rows;
                echo "✓ Profiles migrated: $affected rows\n";
            } else {
                echo "⚠️  Profile migration warning: " . $conn->error . "\n";
                // Try with even fewer columns if this fails
                $sql = "INSERT IGNORE INTO " . DB_NEW_NAME . ".osy_profiles 
                        (id, first_name, last_name, email, phone, age, gender, status, created_at)
                        SELECT id, first_name, last_name, email, phone, age, gender, status, created_at 
                        FROM " . DB_OLD_NAME . ".osy_profiles";
                if ($conn->query($sql)) {
                    echo "✓ Basic profiles migrated: " . $conn->affected_rows . " rows\n";
                }
            }

            // Copy users table if it exists in old database
            $sql = "CREATE TABLE IF NOT EXISTS " . DB_NEW_NAME . ".users LIKE " . DB_OLD_NAME . ".users";
            if ($conn->query($sql)) {
                echo "✓ Users table schema copied\n";
            }

            $sql = "INSERT IGNORE INTO " . DB_NEW_NAME . ".users 
                    (id, username, email, password, fullname, role, is_active, created_at)
                    SELECT id, username, email, password, fullname, role, is_active, created_at 
                    FROM " . DB_OLD_NAME . ".users";
            if ($conn->query($sql)) {
                $affected = $conn->affected_rows;
                echo "✓ Users migrated: $affected rows\n";
            } else {
                echo "⚠️  User migration warning: " . $conn->error . "\n";
            }

            // Copy opportunities table if it exists in old database
            $sql = "CREATE TABLE IF NOT EXISTS " . DB_NEW_NAME . ".opportunities LIKE " . DB_OLD_NAME . ".opportunities";
            if ($conn->query($sql)) {
                echo "✓ Opportunities table schema copied\n";
            }

            $sql = "INSERT IGNORE INTO " . DB_NEW_NAME . ".opportunities 
                    (id, title, type, location, description, total_slots, status, created_by, created_at)
                    SELECT id, title, type, location, description, total_slots, status, created_by, created_at 
                    FROM " . DB_OLD_NAME . ".opportunities";
            if ($conn->query($sql)) {
                $affected = $conn->affected_rows;
                echo "✓ Opportunities migrated: $affected rows\n";
            } else {
                echo "⚠️  Opportunity migration warning: " . $conn->error . "\n";
            }
        } else {
            echo "ℹ️  No existing database found, creating new one...\n";
            if ($conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NEW_NAME)) {
                echo "✓ Database created: " . DB_NEW_NAME . "\n";
            }
        }

        // Step 2: Select new database and apply schema
        $conn->select_db(DB_NEW_NAME);

        // Run the complete database schema from database_dump.sql
        echo "\n🔧 Creating all tables from schema...\n";

        $schemaFile = __DIR__ . '/database_dump.sql';
        if (file_exists($schemaFile)) {
            $schemaContent = file_get_contents($schemaFile);

            // Remove the CREATE DATABASE and USE statements since we're already in the DB
            $schemaContent = preg_replace('/CREATE DATABASE.*;/i', '', $schemaContent);
            $schemaContent = preg_replace('/USE.*;/i', '', $schemaContent);

            // Split into individual statements - improved handling
            $statements = preg_split('/;(?=\s*\n|\s*$)/m', $schemaContent);
            $statements = array_filter(array_map('trim', $statements), function($s) {
                return !empty($s) && !preg_match('/^--/', $s);
            });

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    // Skip CREATE INDEX statements to avoid duplicate key errors
                    if (preg_match('/CREATE INDEX/i', $statement)) {
                        continue;
                    }

                    if ($conn->query($statement)) {
                        // Success - table created or data inserted
                    } else {
                        // Check for expected errors
                        $error = $conn->error;
                        if (
                            strpos($error, "Duplicate entry") !== false ||
                            strpos($error, "Duplicate key name") !== false ||
                            strpos($error, "already exists") !== false ||
                            strpos($error, "Table") !== false && strpos($error, "already exists") !== false
                        ) {
                            // Ignore expected duplicate errors
                        } else {
                            echo "⚠️  Warning: " . $error . "\n";
                        }
                    }
                }
            }
            echo "✓ All tables and sample data applied\n";
        } else {
            echo "❌ Schema file not found: $schemaFile\n";
            exit(1);
        }

        // Step 2b: Ensure critical tables exist
        echo "🔨 Verifying critical tables...\n";
        
        // Helper function to check if table exists
        $tableExists = function($tableName) use ($conn) {
            $result = $conn->query("SHOW TABLES LIKE '$tableName'");
            return $result && $result->num_rows > 0;
        };
        
        // Create osy_matches if missing
        if (!$tableExists('osy_matches')) {
            $sql = "CREATE TABLE IF NOT EXISTS `osy_matches` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($conn->query($sql)) {
                echo "✓ osy_matches table created\n";
            }
        } else {
            echo "✓ osy_matches table exists\n";
        }
        
        // Create notifications if missing
        if (!$tableExists('notifications')) {
            $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `user_id` int(11) NOT NULL,
              `type` varchar(50) DEFAULT 'info',
              `is_read` tinyint(1) DEFAULT 0,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($conn->query($sql)) {
                echo "✓ notifications table created\n";
            }
        } else {
            echo "✓ notifications table exists\n";
        }
        
        // Create notification_templates if missing
        if (!$tableExists('notification_templates')) {
            $sql = "CREATE TABLE IF NOT EXISTS `notification_templates` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `body` text NOT NULL,
              `type` varchar(50) NOT NULL,
              `active` tinyint(1) DEFAULT 1,
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($conn->query($sql)) {
                echo "✓ notification_templates table created\n";
            }
        } else {
            echo "✓ notification_templates table exists\n";
        }

        // Step 3: Apply additional schema changes if needed
        echo "\n🔨 Applying additional schema changes...\n";

        $migrations = [
            "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS profile_type ENUM('OSY','Regular') DEFAULT 'Regular' AFTER id",
            "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS middle_name VARCHAR(50) DEFAULT NULL AFTER first_name",
            "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS govt_id_number VARCHAR(100) DEFAULT NULL AFTER govt_id_type",
            "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS govt_id_image VARCHAR(255) DEFAULT NULL AFTER govt_id_number",
            "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS engagement_status VARCHAR(100) DEFAULT NULL AFTER reason_for_not_in_school"
        ];

        foreach ($migrations as $sql) {
            if ($conn->query($sql)) {
                echo "✓ Schema update applied\n";
            } else {
                if (strpos($conn->error, "Duplicate column name") !== false || strpos($conn->error, "already exists") !== false) {
                    echo "✓ Column already exists\n";
                } else {
                    echo "⚠️  Migration warning: " . $conn->error . "\n";
                }
            }
        }

        // Step 4: Data migration from old database (if exists)
        echo "\n📊 Migrating existing data...\n";

        if ($conn->select_db(DB_OLD_NAME)) {
            // Migrate middle_initial to middle_name for existing profiles
            $sql = "UPDATE " . DB_NEW_NAME . ".osy_profiles op 
                    LEFT JOIN " . DB_OLD_NAME . ".osy_profiles old_p ON op.id = old_p.id 
                    SET op.middle_name = old_p.middle_initial 
                    WHERE old_p.middle_initial IS NOT NULL AND op.middle_name IS NULL";
            if ($conn->query($sql)) {
                echo "✓ Middle name data migrated\n";
            }

            // Set existing profiles as OSY if not already set
            $sql = "UPDATE " . DB_NEW_NAME . ".osy_profiles SET profile_type = 'OSY' WHERE profile_type = 'Regular'";
            if ($conn->query($sql)) {
                echo "✓ Profile types set to OSY for existing data\n";
            }
        } else {
            echo "ℹ️  No old database to migrate from\n";
        }

        // Step 4b: Fix sample profiles type (set initial defaults to OSY)
        $conn->select_db(DB_NEW_NAME);
        $sql = "UPDATE osy_profiles SET profile_type = 'OSY' WHERE (created_by IS NULL OR created_by = 0) AND profile_type = 'Regular'";
        if ($conn->query($sql)) {
            $affected = $conn->affected_rows;
            if ($affected > 0) {
                echo "✓ Set $affected sample profiles to OSY\n";
            }
        }

        // Step 5: Create directories
        echo "\n📁 Setting up file directories...\n";

        $dirs = [
            __DIR__ . '/uploads/profiles',
            __DIR__ . '/uploads/govt_ids'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "✓ Created: $dir\n";
            } else {
                echo "✓ Exists: $dir\n";
            }
        }

        echo "\n✅ Municipal KK Profiling System setup completed!\n";
        echo "\n📋 Summary:\n";
        echo "- Database: " . DB_NEW_NAME . "\n";
        echo "- System rebranded from OSY to Municipal KK Profiling\n";
        echo "- Profile types: OSY vs Regular\n";
        echo "- Enhanced fields: middle_name, govt_id_number, govt_id_image\n";
        echo "- Non-OSY engagement tracking available\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
    ?>