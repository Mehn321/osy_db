<?php

/**
 * GeminiService Class
 * 
 * Handles all communication with the Google Gemini API
 */
class GeminiService
{
    private $apiKey;
    private $model;
    private $apiUrl;
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
        $config = require __DIR__ . '/../config/gemini.php';
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->apiUrl = $config['api_url'];
    }

    /**
     * Generate content from a prompt
     * 
     * @param string $prompt The prompt to send to the AI
     * @return string|false The AI response or false on failure
     */
    public function generateContent($prompt)
    {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        // Disable SSL verification if in local environment with issues
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Log usage regardless of outcome
        $promptTokens = 0;
        $responseTokens = 0;
        $totalTokens = 0;
        $success = false;
        $responseText = false;

        if (!$error && $httpCode === 200) {
            $result = json_decode($response, true);
            
            // Extract token usage from metadata if available
            if (isset($result['usageMetadata'])) {
                $promptTokens = $result['usageMetadata']['promptTokenCount'] ?? 0;
                $responseTokens = $result['usageMetadata']['candidatesTokenCount'] ?? 0;
                $totalTokens = $result['usageMetadata']['totalTokenCount'] ?? 0;
            }
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = trim($result['candidates'][0]['content']['parts'][0]['text']);
                $success = true;
            }
        }

        // Estimate tokens if metadata not provided (rough: 1 token ≈ 4 chars)
        if ($promptTokens === 0) {
            $promptTokens = (int) ceil(strlen($prompt) / 4);
        }
        if ($responseTokens === 0 && $responseText) {
            $responseTokens = (int) ceil(strlen($responseText) / 4);
        }
        if ($totalTokens === 0) {
            $totalTokens = $promptTokens + $responseTokens;
        }

        // Log to ai_usage_log
        $this->logUsage($promptTokens, $responseTokens, $totalTokens, $httpCode, $success);

        if ($error) {
            error_log('Gemini API cURL Error: ' . $error);
            return false;
        }

        if ($httpCode !== 200) {
            error_log('Gemini API Error (HTTP ' . $httpCode . '): ' . $response);
            return false;
        }

        if (!$responseText) {
            error_log('Gemini API Unexpected Response Format: ' . $response);
            return false;
        }

        return $responseText;
    }

    /**
     * Log an API call to the usage tracking table
     */
    private function logUsage($promptTokens, $responseTokens, $totalTokens, $httpStatus, $success)
    {
        try {
            $this->db->execute(
                "INSERT INTO ai_usage_log (endpoint, model, prompt_tokens, response_tokens, total_tokens, http_status, success) 
                 VALUES ('generateContent', ?, ?, ?, ?, ?, ?)",
                [$this->model, $promptTokens, $responseTokens, $totalTokens, $httpStatus, $success ? 1 : 0],
                "siiiii"
            );
        } catch (Exception $e) {
            // Silent – logging must never break core functionality
            error_log('AI Usage Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper to format a prompt for match analysis
     */
    public function formatMatchPrompt($osy, $opportunity)
    {
        return "Act as a professional career counselor. Analyze the match between this Youth Profile and Opportunity.
        
        PROFILE:
        - Skills: {$osy['primary_skill']}, {$osy['skills']}
        - Interests: {$osy['interests']}
        - Edu: {$osy['education_level']}
        
        OPPORTUNITY:
        - Title: {$opportunity['title']}
        - Desc: {$opportunity['description']}
        - Req: {$opportunity['certification']}
        
        TASK:
        Provide a concise 2-sentence explanation of the alignment. 
        Follow it with a separate line starting with '**Specific Benefit:**' followed by one clear benefit.
        Tone: Encouraging.";
    }

    /**
     * Calculate ONLY a numerical match score to save credits
     */
    public function calculateScoreOnly($osy, $opportunity)
    {
        $prompt = "Act as a matching algorithm. Rate the match between this Youth Profile and Opportunity from 0 to 100.
        
        PROFILE:
        - Skills: {$osy['primary_skill']}, {$osy['skills']}
        - Interests: {$osy['interests']}
        - Edu: {$osy['education_level']}
        
        OPPORTUNITY:
        - Title: {$opportunity['title']}
        - Desc: {$opportunity['description']}
        - Req: {$opportunity['certification']}
        
        TASK:
        Return ONLY a single number from 0 to 100. No text, no explanation.";

        $response = $this->generateContent($prompt);
        
        if ($response === false) return false;
        
        // Extract first number found in response
        if (preg_match('/(\d+)/', $response, $matches)) {
            return (int)$matches[1];
        }
        
        return false;
    }

    /**
     * Get comprehensive AI usage statistics
     */
    public function getUsageStats()
    {
        try {
            // Today's usage
            $today = $this->db->fetchOne(
                "SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as tokens, COALESCE(SUM(success), 0) as success_count
                 FROM ai_usage_log WHERE DATE(created_at) = CURDATE()"
            );

            // This month's usage
            $month = $this->db->fetchOne(
                "SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as tokens, COALESCE(SUM(success), 0) as success_count
                 FROM ai_usage_log WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
            );

            // All time usage
            $allTime = $this->db->fetchOne(
                "SELECT COUNT(*) as calls, COALESCE(SUM(total_tokens), 0) as tokens, COALESCE(SUM(success), 0) as success_count
                 FROM ai_usage_log"
            );

            // Last 24 hours by hour
            $hourly = $this->db->fetchAll(
                "SELECT HOUR(created_at) as hour, COUNT(*) as calls 
                 FROM ai_usage_log 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                 GROUP BY HOUR(created_at) 
                 ORDER BY hour"
            );

            // Last API call
            $lastCall = $this->db->fetchOne(
                "SELECT created_at, http_status, success FROM ai_usage_log ORDER BY id DESC LIMIT 1"
            );

            // Failed calls today
            $failedToday = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM ai_usage_log WHERE DATE(created_at) = CURDATE() AND success = 0"
            );

            // Rate limit info: Gemini free tier limits
            $freeTierDailyLimit = 1500; // Gemini Flash free tier ~1500 RPD
            $freeTierMinuteLimit = 15;  // ~15 RPM

            // Calls in the last minute
            $lastMinute = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM ai_usage_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
            );

            return [
                'today' => [
                    'calls' => (int) ($today['calls'] ?? 0),
                    'tokens' => (int) ($today['tokens'] ?? 0),
                    'success' => (int) ($today['success_count'] ?? 0),
                    'failed' => (int) ($failedToday['cnt'] ?? 0),
                ],
                'month' => [
                    'calls' => (int) ($month['calls'] ?? 0),
                    'tokens' => (int) ($month['tokens'] ?? 0),
                    'success' => (int) ($month['success_count'] ?? 0),
                ],
                'all_time' => [
                    'calls' => (int) ($allTime['calls'] ?? 0),
                    'tokens' => (int) ($allTime['tokens'] ?? 0),
                    'success' => (int) ($allTime['success_count'] ?? 0),
                ],
                'hourly' => $hourly,
                'last_call' => $lastCall,
                'rate_limits' => [
                    'daily_limit' => $freeTierDailyLimit,
                    'daily_used' => (int) ($today['calls'] ?? 0),
                    'daily_pct' => $freeTierDailyLimit > 0 ? round(((int) ($today['calls'] ?? 0) / $freeTierDailyLimit) * 100, 1) : 0,
                    'minute_limit' => $freeTierMinuteLimit,
                    'minute_used' => (int) ($lastMinute['cnt'] ?? 0),
                    'resets_at' => date('M d, Y', strtotime('+1 day', strtotime('today'))),
                ],
                'model' => $this->model,
            ];
        } catch (Exception $e) {
            error_log('AI Usage Stats Error: ' . $e->getMessage());
            return [
                'today' => ['calls' => 0, 'tokens' => 0, 'success' => 0, 'failed' => 0],
                'month' => ['calls' => 0, 'tokens' => 0, 'success' => 0],
                'all_time' => ['calls' => 0, 'tokens' => 0, 'success' => 0],
                'hourly' => [],
                'last_call' => null,
                'rate_limits' => [
                    'daily_limit' => 1500, 'daily_used' => 0, 'daily_pct' => 0,
                    'minute_limit' => 15, 'minute_used' => 0, 'resets_at' => date('M d, Y', strtotime('tomorrow')),
                ],
                'model' => $this->model,
            ];
        }
    }
}

