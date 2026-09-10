<?php
// Simulate logged-in session
$_SESSION = ['user_id' => 10, 'username' => 'testyouth', 'email' => 'test@example.com', 'role' => 'youth', 'status' => 'Active'];
$_SERVER['REQUEST_METHOD'] = 'GET';

// Capture output
ob_start();
include 'pages/my-profile.php';
$html = ob_get_clean();

// Check for modal
$modalPos = strpos($html, 'profileEditModal');
$mainOpenPos = strpos($html, '<main');
$mainClosePos = strrpos($html, '</main>');

echo "Modal position: " . ($modalPos !== false ? $modalPos : 'NOT FOUND') . "\n";
echo "Main open position: " . ($mainOpenPos !== false ? $mainOpenPos : 'NOT FOUND') . "\n";
echo "Main close position: " . ($mainClosePos !== false ? $mainClosePos : 'NOT FOUND') . "\n";

if ($modalPos !== false && $mainClosePos !== false) {
    echo "Modal is inside main: " . ($modalPos < $mainClosePos ? "YES" : "NO") . "\n";
}

// Get snippet around modal
if ($modalPos !== false) {
    $snippet = substr($html, max(0, $modalPos - 200), 400);
    echo "\nSnippet around modal:\n";
    echo htmlspecialchars($snippet) . "\n";
}
?>
