<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$output = [];
$output[] = "=== HTML Output Debug ===\n";

try {
    ob_start();
    include __DIR__ . '/pages/my-profile.php';
    $html = ob_get_clean();
    $output[] = "HTML loaded successfully. Length: " . strlen($html) . " bytes\n";
    
    // Find <script> tag
    if (strpos($html, '<script>') !== false) {
        $output[] = "<script> tag FOUND in HTML\n";
        
        // Extract portion around script
        $pos = strpos($html, '<script>');
        $output[] = "Script starts at position: " . $pos . "\n";
        $output[] = "200 chars before script: " . htmlspecialchars(substr($html, max(0, $pos - 200), 200)) . "\n";
        $output[] = "Script tag and first 500 chars: " . htmlspecialchars(substr($html, $pos, 500)) . "\n";
    } else {
        $output[] = "<script> tag NOT FOUND in HTML\n";
        $output[] = "Last 500 chars of HTML: " . htmlspecialchars(substr($html, -500)) . "\n";
    }
} catch (Exception $e) {
    $output[] = "ERROR: " . $e->getMessage() . "\n";
    $output[] = "Trace: " . $e->getTraceAsString() . "\n";
}

file_put_contents(__DIR__ . '/debug_output.txt', implode('', $output));
echo "Debug output written to debug_output.txt\n";
?>
