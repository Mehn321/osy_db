<?php
$conn = new mysqli('localhost', 'root', '', 'municipal_kk_profiling', 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Tables in database:\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    echo "  - " . $row[0] . "\n";
}

// Try to create osy_matches if it doesn't exist
echo "\nCreating osy_matches if missing...\n";
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
    echo "✓ osy_matches table created/verified\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
