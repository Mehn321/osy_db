<?php
/**
 * Local Configuration Example
 * Copy this file to local.php and fill in your local settings.
 */
return [
    'db' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'name' => 'municipal_kk_profiling',
        'port' => 3306,
    ],
    'gemini' => [
        'api_key' => 'YOUR_GEMINI_API_KEY',
        'model' => 'gemini-1.5-flash',
        'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
    ]
];
