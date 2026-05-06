<?php
require_once __DIR__ . '/init.php';

try {
    $conn = $database->getConnection();
    
    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS `system_references` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category` varchar(50) NOT NULL,
        `value` varchar(255) NOT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($sql);
    echo "Created system_references table.\n";
    
    // Check if empty
    $res = $conn->query("SELECT COUNT(*) as cnt FROM system_references");
    $row = $res->fetch_assoc();
    if ($row['cnt'] == 0) {
        $references = [
            'govt_id_type' => ['SSS', 'GSIS', 'PhilHealth', 'Pag-IBIG', 'Passport', 'Driver\'s License', 'Postal ID', 'Voter\'s ID', 'National ID'],
            'barangay' => ['Baga', 'Bangko', 'Camanucan', 'Dela Paz', 'Lutao', 'Magsaysay', 'Map-an', 'Mohon', 'Poblacion', 'Punta', 'Salimpuno', 'San Andres', 'San Juan', 'San Roque', 'Sumasap', 'Villalin'],
            'education_level' => ['Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'College Undergraduate', 'College Graduate', 'Vocational', 'No Formal Education'],
            'reason' => ['Financial Problem', 'Lack of Interest', 'Family Problem', 'Illness/Disability', 'Employment', 'Marriage/Pregnancy', 'Distance of School', 'Others']
        ];
        
        $insertQuery = "INSERT INTO `system_references` (`category`, `value`) VALUES ";
        $values = [];
        foreach ($references as $category => $items) {
            foreach ($items as $item) {
                $values[] = "('" . $database->escape($category) . "', '" . $database->escape($item) . "')";
            }
        }
        $insertQuery .= implode(', ', $values);
        $conn->query($insertQuery);
        echo "Populated system_references table.\n";
    } else {
        echo "system_references table already has data.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
