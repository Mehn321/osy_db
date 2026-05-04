<?php
/**
 * api/background_recalculate_scores.php
 * 
 * Background process to update AI scores without blocking the UI
 */

if (php_sapi_name() !== 'cli' && !isset($argv)) {
    die("This script must be run from the command line.");
}

// Get OSY ID from command line arguments
$osy_id = isset($argv[1]) ? intval($argv[1]) : 0;

if (!$osy_id) {
    error_log("Background Scoring Error: No OSY ID provided.");
    exit;
}

// Set time limit to infinity for this process
set_time_limit(0);

// Use a simplified initialization to avoid session/header issues in CLI
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Matching.php';

error_log("Background Scoring: Starting for OSY ID $osy_id");

$matching = new Matching($database);
$success = $matching->updateAllScoresForOSY($osy_id);

if ($success) {
    error_log("Background Scoring: Successfully completed for OSY ID $osy_id");
} else {
    error_log("Background Scoring: Failed for OSY ID $osy_id");
}
