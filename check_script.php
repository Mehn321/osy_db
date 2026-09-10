<?php
// Check what's in my-profile.php
$file = __DIR__ . '/pages/my-profile.php';
$content = file_get_contents($file);

// Find the script section
$start = strpos($content, '<script>');
$end = strpos($content, '</script>', $start);

if ($start !== false && $end !== false) {
    $scriptContent = substr($content, $start + 8, $end - $start - 8);
    echo '<pre>';
    echo htmlspecialchars($scriptContent);
    echo '</pre>';
} else {
    echo 'Script tag not found!';
}
?>
