<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/job-openings.php');

// 1. Remove synchronous data fetch
$c = preg_replace('/\$filters = \[\s*\'search\'.*?\];.*?(\/\/\s*Handle|require_once __DIR__ \. \'\/\.\.\/includes\/header\.php\';)/s', "$1", $c);

// 2. Replace the HTML grid with skeletons
$skeletons = '<!-- Opportunities Cards Grid -->
<div id="jobCardsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
            <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
        </div>
    <?php endfor; ?>
</div>
<div id="jobsLoading" class="hidden flex items-center justify-center gap-2 mt-4 text-sm text-slate-500">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading jobs...
</div>';

$c = preg_replace('/<!-- Opportunities Cards Grid -->.*?<\/div>\s*<!-- Create\/Edit Opportunity Modal -->/s', $skeletons . "\n\n<!-- Create/Edit Opportunity Modal -->", $c);

// 3. Update the JavaScript
$js = '
<script>
    var opportunitiesData = {};
    const userRole = <?php echo json_encode($_SESSION[\'role\']); ?>;

    function renderJobCard(opp) {
        const statusCls = opp.status === \'Open\' ? \'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400\' : \'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400\';
        const deadline = opp.deadline ? new Date(opp.deadline).toLocaleDateString(\'en-US\', {month:\'short\',day:\'numeric\',year:\'numeric\'}) : \'No deadline\';
        
        let html = `
            <div class="job-card bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">${opp.type}</p>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">${opp.title}</h3>
                        </div>
                        <span class="px-3 py-1 ${statusCls} rounded-full text-xs font-bold">${opp.status}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">location_on</span> ${opp.location}
                    </p>
                </div>
                <div class="p-6 space-y-4">`;
                
        if (opp.description) html += `<p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">${opp.description}</p>`;
        if (opp.compensation) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Compensation</p><p class="text-lg font-bold text-slate-900 dark:text-white">${opp.compensation}</p></div>`;
        if (opp.certification) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Certification</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>`;
        
        html += `
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Deadline</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${deadline}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Slots</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots} available</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex gap-2">
                    <button onclick="viewOpportunityDetail(${opp.id})" class="flex-1 py-2 px-3 bg-blue-900 text-white rounded-lg text-sm font-semibold hover:bg-blue-800 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span> View Details
                    </button>`;
                    
        if (userRole !== \'lydo\') {
            html += `<button onclick="openEditModal(${opp.id})" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors"><span class="material-symbols-outlined">edit</span></button>`;
        }
        html += `
                    <button onclick="deleteOpportunity(${opp.id})" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors"><span class="material-symbols-outlined">delete</span></button>
                </div>
            </div>`;
        return html;
    }

    function loadJobs() {
        const searchVal = document.getElementById(\'job_search\')?.value || \'\';
        const statusVal = document.getElementById(\'job_status\')?.value || \'All\';
        
        const loader = document.getElementById(\'jobsLoading\');
        const container = document.getElementById(\'jobCardsGrid\');
        
        loader.classList.remove(\'hidden\');
        container.innerHTML = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">` + 
            Array(4).fill(0).map(() => `
            <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
                <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
            </div>`).join(\'\') + `</div>`;

        fetch(`../api/get_opportunities_data.php?type=Job+Opening&search=${encodeURIComponent(searchVal)}&status=${encodeURIComponent(statusVal)}`)
            .then(r => r.json())
            .then(res => {
                loader.classList.add(\'hidden\');
                opportunitiesData = {};
                if (res.success && res.opportunities) {
                    res.opportunities.forEach(opp => { opportunitiesData[opp.id] = opp; });
                }
                
                if (!res.success || !res.opportunities.length) {
                    container.innerHTML = `<div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">work_off</span><p class="text-slate-500 font-semibold text-lg">No job openings found</p></div>`;
                    return;
                }
                
                container.innerHTML = res.opportunities.map(renderJobCard).join(\'\');
            });
    }

    (function() {
        loadJobs();
        
        var searchEl = document.getElementById(\'job_search\');
        if (searchEl) {
            let debounce;
            searchEl.addEventListener(\'input\', () => { clearTimeout(debounce); debounce = setTimeout(loadJobs, 400); });
        }
        var statusEl = document.getElementById(\'job_status\');
        if (statusEl) statusEl.addEventListener(\'change\', loadJobs);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get(\'create\')) setTimeout(() => openCreateModal(\'Job Opening\'), 500);
        if (urlParams.get(\'edit_id\')) setTimeout(() => openEditModal(parseInt(urlParams.get(\'edit_id\'))), 500);
        if (urlParams.get(\'view_id\')) setTimeout(() => viewOpportunityDetail(parseInt(urlParams.get(\'view_id\'))), 500);
    })();
';

$c = preg_replace('/<script>.*?var opportunitiesData = \{.*?\};.*?(function openCreateModal)/s', $js . "\n\n    $1", $c);
$c = preg_replace('/document\.addEventListener\(\'DOMContentLoaded\', function\(\) \{.*?\}\);/s', '', $c);
$c = preg_replace('/function filterJobs\(\) \{.*?\}/s', '', $c);

// Also update filter oninput/onchange in HTML
$c = str_replace('oninput="filterJobs()"', '', $c);
$c = str_replace('onchange="filterJobs()"', '', $c);

// Add skeleton CSS
$c = str_replace('</script>', "</script>\n<style>.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; } .dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; } @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/job-openings.php', $c);
echo "Done job-openings.php\n";

// Same for training-programs.php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/training-programs.php');
$c = preg_replace('/\$filters = \[\s*\'search\'.*?\];.*?(\/\/\s*Handle|require_once __DIR__ \. \'\/\.\.\/includes\/header\.php\';)/s', "$1", $c);

$skeletons = '<!-- Opportunities Cards Grid -->
<div id="progCardsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
            <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
        </div>
    <?php endfor; ?>
</div>
<div id="progsLoading" class="hidden flex items-center justify-center gap-2 mt-4 text-sm text-slate-500">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading programs...
</div>';

$c = preg_replace('/<!-- Opportunities Cards Grid -->.*?<\/div>\s*<!-- Create\/Edit Opportunity Modal -->/s', $skeletons . "\n\n<!-- Create/Edit Opportunity Modal -->", $c);

$js = '
<script>
    var opportunitiesData = {};
    const userRole = <?php echo json_encode($_SESSION[\'role\']); ?>;

    function renderProgCard(opp) {
        const statusCls = opp.status === \'Open\' ? \'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400\' : \'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400\';
        const deadline = opp.deadline ? new Date(opp.deadline).toLocaleDateString(\'en-US\', {month:\'short\',day:\'numeric\',year:\'numeric\'}) : \'No deadline\';
        
        let html = `
            <div class="prog-card bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">${opp.type}</p>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">${opp.title}</h3>
                        </div>
                        <span class="px-3 py-1 ${statusCls} rounded-full text-xs font-bold">${opp.status}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">location_on</span> ${opp.location}
                    </p>
                </div>
                <div class="p-6 space-y-4">`;
                
        if (opp.description) html += `<p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">${opp.description}</p>`;
        if (opp.training_provider) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Provider</p><p class="text-lg font-bold text-slate-900 dark:text-white">${opp.training_provider}</p></div>`;
        if (opp.certification) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Certification</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>`;
        
        html += `
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Deadline</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${deadline}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Slots</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots} available</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex gap-2">
                    <button onclick="viewOpportunityDetail(${opp.id})" class="flex-1 py-2 px-3 bg-indigo-900 text-white rounded-lg text-sm font-semibold hover:bg-indigo-800 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span> View Details
                    </button>`;
                    
        if (userRole !== \'lydo\') {
            html += `<button onclick="openEditModal(${opp.id})" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors"><span class="material-symbols-outlined">edit</span></button>`;
        }
        html += `
                    <button onclick="deleteOpportunity(${opp.id})" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors"><span class="material-symbols-outlined">delete</span></button>
                </div>
            </div>`;
        return html;
    }

    function loadProgs() {
        const searchVal = document.getElementById(\'prog_search\')?.value || \'\';
        const statusVal = document.getElementById(\'prog_status\')?.value || \'All\';
        
        const loader = document.getElementById(\'progsLoading\');
        const container = document.getElementById(\'progCardsGrid\');
        
        loader.classList.remove(\'hidden\');
        container.innerHTML = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">` + 
            Array(4).fill(0).map(() => `
            <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
                <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
            </div>`).join(\'\') + `</div>`;

        fetch(`../api/get_opportunities_data.php?type=Vocational+Training&search=${encodeURIComponent(searchVal)}&status=${encodeURIComponent(statusVal)}`)
            .then(r => r.json())
            .then(res => {
                loader.classList.add(\'hidden\');
                opportunitiesData = {};
                if (res.success && res.opportunities) {
                    res.opportunities.forEach(opp => { opportunitiesData[opp.id] = opp; });
                }
                
                if (!res.success || !res.opportunities.length) {
                    container.innerHTML = `<div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">school</span><p class="text-slate-500 font-semibold text-lg">No training programs found</p></div>`;
                    return;
                }
                
                container.innerHTML = res.opportunities.map(renderProgCard).join(\'\');
            });
    }

    (function() {
        loadProgs();
        
        var searchEl = document.getElementById(\'prog_search\');
        if (searchEl) {
            let debounce;
            searchEl.addEventListener(\'input\', () => { clearTimeout(debounce); debounce = setTimeout(loadProgs, 400); });
        }
        var statusEl = document.getElementById(\'prog_status\');
        if (statusEl) statusEl.addEventListener(\'change\', loadProgs);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get(\'create\')) setTimeout(() => openCreateModal(\'Vocational Training\'), 500);
        if (urlParams.get(\'edit_id\')) setTimeout(() => openEditModal(parseInt(urlParams.get(\'edit_id\'))), 500);
        if (urlParams.get(\'view_id\')) setTimeout(() => viewOpportunityDetail(parseInt(urlParams.get(\'view_id\'))), 500);
    })();
';

$c = preg_replace('/<script>.*?var opportunitiesData = \{.*?\};.*?(function openCreateModal)/s', $js . "\n\n    $1", $c);
$c = preg_replace('/document\.addEventListener\(\'DOMContentLoaded\', function\(\) \{.*?\}\);/s', '', $c);
$c = preg_replace('/function filterProgs\(\) \{.*?\}/s', '', $c);

$c = str_replace('oninput="filterProgs()"', '', $c);
$c = str_replace('onchange="filterProgs()"', '', $c);
$c = str_replace('</script>', "</script>\n<style>.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; } .dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; } @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/training-programs.php', $c);
echo "Done training-programs.php\n";
