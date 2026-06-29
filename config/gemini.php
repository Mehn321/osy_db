<?php
/**
 * Gemini API Configuration
 */
// Load local configuration if available
$localConfigFile = __DIR__ . '/local.php';
$localConfig = file_exists($localConfigFile) ? require $localConfigFile : [];

return [
    'api_key' => $localConfig['gemini']['api_key'] ?? '',
    'model' => $localConfig['gemini']['model'] ?? 'gemini-1.5-flash',
    'api_url' => $localConfig['gemini']['api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/'
];
