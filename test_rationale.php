<?php
require_once 'init.php';
require_once 'Classes/GeminiService.php';

echo "--- TESTING RATIONALE FORMATTING ---\n";

$osy = [
    'primary_skill' => 'Computer Literacy',
    'skills' => 'Typing, MS Word, basic internet',
    'interests' => 'Office work, administration',
    'education_level' => 'High School Graduate'
];

$opp = [
    'title' => 'Office Assistant',
    'description' => 'Help with clerical tasks, data entry, and filing.',
    'certification' => 'None required'
];

$gemini = new GeminiService($database);
$prompt = $gemini->formatMatchPrompt($osy, $opp);

echo "PROMPT:\n$prompt\n\n";

echo "Generating rationale...\n";
$rationale = $gemini->generateContent($prompt);

if ($rationale) {
    echo "RATIONALE:\n$rationale\n";
    if (strpos($rationale, '**Specific Benefit:**') !== false) {
        echo "SUCCESS: Found '**Specific Benefit:**' label!\n";
    } else {
        echo "FAILURE: Label '**Specific Benefit:**' is missing.\n";
    }
} else {
    echo "FAILED to generate rationale.\n";
}
