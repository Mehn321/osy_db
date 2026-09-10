<?php
// Simple test to check if the file can be included
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting debug...\n";
echo "Checking if my-profile.php exists...\n";

if (file_exists(__DIR__ . '/pages/my-profile.php')) {
    echo "File exists!\n";
    
    // Try to read it as a string
    $content = file_get_contents(__DIR__ . '/pages/my-profile.php');
    echo "File size: " . strlen($content) . " bytes\n";
    
    // Search for <script>
    if (strpos($content, '<script>') !== false) {
        echo "Found <script> tag in file\n";
        echo "Position: " . strpos($content, '<script>') . "\n";
    } else {
        echo "NO <script> tag found in file!\n";
    }
    
    // Count PHP tags
    echo "Number of <?php tags: " . substr_count($content, '<?php') . "\n";
    echo "Number of ?> tags: " . substr_count($content, '?>') . "\n";
} else {
    echo "File does not exist!\n";
}

echo "\nDone!\n";
?>
