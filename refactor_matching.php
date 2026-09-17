<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/matching.php');

// Remove synchronous fetch of matches
$c = preg_replace('/\$matches_for_opportunity\s*=\s*\$selectedOpportunityId\s*\?\s*\$matching->getMatchesForOpportunity.*?;\s*/s', '', $c);

// Also remove count of matches_for_opportunity
$c = preg_replace('/<\?php echo count\(\$matches_for_opportunity\); \?>/s', '<span id="totalMatchesCount">0</span>', $c);

// Remove the pending/accepted/rejected arrays PHP block
$c = preg_replace('/<\?php\s*\$pendingMatches = \[\];\s*\$acceptedMatches = \[\];.*?\$rejectedMatches\[\] = \$match;\s*\}\s*\}\s*\?>/s', '', $c);

// Replace the columns with skeletons
$c = preg_replace('/<div id="colPending" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">.*?<\/div>\s*<div id="colRejected" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4 hidden">.*?<\/div>/s', '
            <div id="colPending" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
            </div>
            <div id="colRejected" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4 hidden"></div>', $c);
            
$c = preg_replace('/<div id="colAccepted" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">.*?<\/div>/s', '
            <div id="colAccepted" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
            </div>', $c);

// Replace counts with spans
$c = preg_replace('/<span id="pendingCount"><\?php echo count\(\$pendingMatches\); \?><\/span>/s', '<span id="pendingCount">0</span>', $c);
$c = preg_replace('/<span id="rejectedCount"><\?php echo count\(\$rejectedMatches\); \?><\/span>/s', '<span id="rejectedCount">0</span>', $c);
$c = preg_replace('/<span id="acceptedCount".*?>.*?<\/span>/s', '<span id="acceptedCount" class="text-xs font-bold text-blue-700 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded-full">0</span>', $c);
$c = preg_replace('/<\?php echo count\(\$matches_for_opportunity\); \?> candidates found/s', '<span id="heroMatchCountInner">0</span> candidates found', $c);

// Remove the PHP renderMatchCard function
$c = preg_replace('/<\?php\s*\/\/\s*Extracted card rendering logic.*?\}\s*\?>/s', '', $c);

// Fix JavaScript update for count
$jsAdd = "
    document.getElementById('totalMatchesCount').textContent = result.data.length;
    document.getElementById('pendingCount').textContent = result.data.filter(m => m.status === 'Pending').length;
    const rejEl = document.getElementById('rejectedCount'); if (rejEl) rejEl.textContent = result.data.filter(m => m.status === 'Rejected').length;
    document.getElementById('acceptedCount').textContent = result.data.filter(m => m.status === 'Accepted').length;
    
    // Check if initial load
    if (!window.initialMatchLoaded && currentOpportunityId) {
        window.initialMatchLoaded = true;
    }
";

$c = str_replace("document.getElementById('heroMatchCount').textContent = result.data.length + ' candidates found';", "document.getElementById('heroMatchCount').textContent = result.data.length + ' candidates found';\n" . $jsAdd, $c);

// Add init call
$c = str_replace('// Initial run', "// Initial run\n    if (currentOpportunityId) { loadMatchesAJAX(currentOpportunityId); }", $c);

// Add IIFE and var changes to avoid duplicate declaration errors since this is SPA
$c = preg_replace('/var currentOpportunityId = /', 'window.currentOpportunityId = ', $c);
$c = preg_replace('/var currentOpportunityTitle = /', 'window.currentOpportunityTitle = ', $c);
$c = preg_replace('/var sampleName = /', 'window.sampleName = ', $c);
$c = preg_replace('/var oppName = /', 'window.oppName = ', $c);

$c = str_replace('currentOpportunityId = id;', 'window.currentOpportunityId = id;', $c);
$c = str_replace('oppName = result.opportunity.title;', 'window.oppName = result.opportunity.title;', $c);
$c = preg_replace('/function handleOpportunityChange/', 'window.handleOpportunityChange = function', $c);
$c = preg_replace('/function loadMatchesAJAX/', 'window.loadMatchesAJAX = async function', $c);
$c = preg_replace('/function renderMatchCardJS/', 'window.renderMatchCardJS = function', $c);
$c = preg_replace('/function applyTemplate/', 'window.applyTemplate = function', $c);
$c = preg_replace('/function updateLivePreview/', 'window.updateLivePreview = function', $c);
$c = preg_replace('/function switchTab/', 'window.switchTab = function', $c);
$c = preg_replace('/function showDetailsModal/', 'window.showDetailsModal = function', $c);

$c = str_replace('</script>', "})();\n</script>", $c);
$c = str_replace('<script>', "<script>\n(function() {", $c);

// Add skeleton CSS
$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', "<style>.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; } .dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; } @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/matching.php', $c);
echo "Done matching.php\n";
