<?php
/**
 * test_gemini.php
 * 
 * Simple script to test if the Gemini API integration is working.
 */
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/Classes/GeminiService.php';

echo "<h1>Gemini API Test</h1>";

try {
    $gemini = new GeminiService($database);
    
    echo "<p>Testing API with a simple prompt...</p>";
    $prompt = "Hello Gemini! I am testing an integration in a PHP application for a Municipal Youth Profiling system. Reply with exactly one sentence saying 'Integration Successful!'.";
    
    $response = $gemini->generateContent($prompt);
    
    if ($response) {
        echo "<div style='padding: 20px; background: #e6fffa; border: 1px solid #38b2ac; color: #234e52; border-radius: 8px;'>";
        echo "<strong>AI Response:</strong><br>" . htmlspecialchars($response);
        echo "</div>";
        echo "<p style='color: green; font-weight: bold;'>✔ API Key and Service are working correctly!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✘ API Call failed. Check your error log (email_log.txt or PHP error log).</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✘ Error: " . $e->getMessage() . "</p>";
}
