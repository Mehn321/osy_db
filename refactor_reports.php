<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/reports.php');

// Remove synchronous calls
$c = preg_replace('/\$report = new Report\(\$database\);\s*\$stats = \$report->generateMatchingStats\(\$profilingFilters\);/s', '$report = new Report($database); $stats = ["total_matches_made"=>"...", "pending_matches"=>"...", "average_match_score"=>"...", "employment_success_rate"=>"..."];', $c);
$c = preg_replace('/\$profilingCount = count\(\$report->getYouthProfilingProfiles\(\$profilingFilters\)\);/s', '$profilingCount = 0;', $c);

// Wrap profilingCount in span
$c = preg_replace('/<\?php echo \(int\) \$profilingCount; \?>/', '<span id="profilingCountSpan">...</span>', $c);
$c = preg_replace('/<\?php echo \$profilingCount === 1 \? \'\' : \'s\'; \?>/', '<span id="profilingCountPlural">s</span>', $c);

// Wrap stats in spans
$c = preg_replace('/<\?php echo \$stats\[\'total_matches_made\'\]; \?>/', '<span id="stat_total_matches">...</span>', $c);
$c = preg_replace('/<\?php echo \$stats\[\'pending_matches\'\]; \?>/', '<span id="stat_pending_matches">...</span>', $c);
$c = preg_replace('/<\?php echo round\(\$stats\[\'average_match_score\'\] \?\? 0\); \?>%/', '<span id="stat_avg_score">...</span>%', $c);
$c = preg_replace('/<\?php echo \$stats\[\'employment_success_rate\'\]; \?>%/', '<span id="stat_success_rate">...</span>%', $c);

// Update JS inside renderCharts
$jsAdd = "
            if (data.stats) {
                document.getElementById('stat_total_matches').textContent = data.stats.total_matches_made;
                document.getElementById('stat_pending_matches').textContent = data.stats.pending_matches;
                document.getElementById('stat_avg_score').textContent = Math.round(data.stats.average_match_score || 0);
                document.getElementById('stat_success_rate').textContent = data.stats.employment_success_rate;
            }
            if (data.profilingCount !== undefined) {
                document.getElementById('profilingCountSpan').textContent = data.profilingCount;
                document.getElementById('profilingCountPlural').textContent = data.profilingCount === 1 ? '' : 's';
            }
";
$c = str_replace('// 1. Profile Types Chart', $jsAdd . "\n            // 1. Profile Types Chart", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/reports.php', $c);
echo "Done reports.php\n";
