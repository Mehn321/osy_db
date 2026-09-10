<?php
// This script fetches the my-profile.php page content directly
// to verify the modal HTML is being sent to the browser

// Get file content
$content = file_get_contents(__DIR__ . '/pages/my-profile.php');

// Find the modal
$modalPos = strpos($content, 'profileEditModal');

echo "<h1>Profile Page Analysis</h1>";
echo "<p>File size: " . strlen($content) . " bytes</p>";
echo "<p>Modal found at position: " . ($modalPos ? $modalPos : 'NOT FOUND') . "</p>";

if ($modalPos) {
    echo "<h2>Modal HTML (first 500 chars):</h2>";
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    echo htmlspecialchars(substr($content, $modalPos - 50, 600));
    echo "</pre>";
    
    // Find the script section
    $scriptStart = strpos($content, 'function openProfileEditModal');
    echo "<h2>Script section check:</h2>";
    if ($scriptStart) {
        echo "<p style='color: green;'><strong>✓ Script functions found at position: $scriptStart</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        echo htmlspecialchars(substr($content, $scriptStart, 400));
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>✗ Script functions NOT found!</strong></p>";
    }
    
    // Check footer
    $footerPos = strpos($content, 'require_once __DIR__');
    $footerCount = substr_count($content, 'require_once');
    echo "<h2>Footer include check:</h2>";
    echo "<p>Footer includes found: $footerCount</p>";
    if ($footerPos !== false) {
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        echo htmlspecialchars(substr($content, $footerPos - 50, 200));
        echo "</pre>";
    }
    
} else {
    echo "<p style='color: red;'><strong>✗ Modal not found in file content!</strong></p>";
}
?>
