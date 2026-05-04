<?php
require_once 'init.php';
require_once 'Classes/Matching.php';
require_once 'Classes/OSYProfile.php';

echo "--- TESTING AUTOMATED SCORING ---\n";

// 1. Get an existing OSY ID
$osy = $database->fetchOne("SELECT id FROM osy_profiles LIMIT 1");
if (!$osy) {
    die("No OSY profiles found to test with.\n");
}
$osy_id = $osy['id'];
echo "Testing with OSY ID: $osy_id\n";

// 2. Clear existing matches for this OSY to see them repopulate
echo "Clearing existing matches for testing...\n";
$database->execute("DELETE FROM osy_matches WHERE osy_id = ?", [$osy_id], "i");

// 3. Trigger recalculation
$matching = new Matching($database);
echo "Recalculating scores for all open opportunities (this may take a few seconds)...\n";
$start = microtime(true);
$matching->updateAllScoresForOSY($osy_id);
$end = microtime(true);

echo "Recalculation finished in " . round($end - $start, 2) . " seconds.\n";

// 4. Verify results
$matches = $database->fetchAll("SELECT * FROM osy_matches WHERE osy_id = ?", [$osy_id], "i");
echo "Found " . count($matches) . " matches in database.\n";

foreach ($matches as $m) {
    echo " - Opportunity ID {$m['opportunity_id']}: Score {$m['match_score']}%\n";
}

if (count($matches) > 0) {
    echo "SUCCESS: Automated scoring is working!\n";
} else {
    echo "FAILURE: No matches were created.\n";
}
