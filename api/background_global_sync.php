<?php
/**
 * api/background_global_sync.php
 * 
 * background process to sync AI scores for all profiles against all jobs
 */

if (php_sapi_name() !== 'cli' && !isset($argv)) {
    die("This script must be run from the command line.");
}

set_time_limit(0);
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Matching.php';

error_log("Global AI Sync: Starting mass recalculation...");

$matching = new Matching($database);
$osys = $database->fetchAll("SELECT id FROM osy_profiles");

foreach ($osys as $osy) {
    error_log("Global AI Sync: Processing OSY ID {$osy['id']}...");
    $matching->updateAllScoresForOSY($osy['id']);
    
    // Extra buffer between profiles to be safe with free tier
    sleep(2); 
}

error_log("Global AI Sync: Mass recalculation complete.");
