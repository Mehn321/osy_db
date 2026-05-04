<?php
/**
 * api/ai_analyze_match.php
 * 
 * AJAX endpoint to generate or fetch AI insights for a specific match
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/GeminiService.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get input
$data = json_decode(file_get_contents('php://input'), true);
$match_id = isset($data['match_id']) ? intval($data['match_id']) : 0;

if (!$match_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Match ID']);
    exit;
}

try {
    // 1. Check if insight already exists (Caching)
    $match = $database->fetchOne(
        "SELECT m.id, m.osy_id, m.opportunity_id, m.ai_insight,
                p.primary_skill, p.skills, p.interests, p.education_level,
                o.title, o.description, o.certification
         FROM osy_matches m
         JOIN osy_profiles p ON m.osy_id = p.id
         JOIN opportunities o ON m.opportunity_id = o.id
         WHERE m.id = ? LIMIT 1",
        [$match_id],
        "i"
    );

    if (!$match) {
        throw new Exception("Match record not found.");
    }

    if (!empty($match['ai_insight'])) {
        echo json_encode([
            'success' => true,
            'insight' => $match['ai_insight'],
            'cached' => true
        ]);
        exit;
    }

    // 2. Generate Insight via Gemini
    $gemini = new GeminiService($database);
    
    // Explicitly separate data for cleaner prompt generation
    $osyData = [
        'primary_skill' => $match['primary_skill'],
        'skills' => $match['skills'],
        'interests' => $match['interests'],
        'education_level' => $match['education_level']
    ];
    
    $oppData = [
        'title' => $match['title'],
        'description' => $match['description'],
        'certification' => $match['certification']
    ];
    
    $prompt = $gemini->formatMatchPrompt($osyData, $oppData);
    
    $insight = $gemini->generateContent($prompt);

    if (!$insight) {
        throw new Exception("AI was unable to generate a response. Please try again later.");
    }

    // 3. Save to database for caching
    $database->execute(
        "UPDATE osy_matches SET ai_insight = ? WHERE id = ?",
        [$insight, $match_id],
        "si"
    );

    echo json_encode([
        'success' => true,
        'insight' => $insight,
        'cached' => false
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
