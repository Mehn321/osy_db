<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/notification-templates.php');

// Remove synchronous call
$c = preg_replace('/\$templates\s*=\s*\$notification->getAllTemplates\(\);/s', '', $c);

// Replace PHP foreach with skeletons
$skeletons = '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="templatesGrid">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col h-48">
            <div class="skeleton-pulse h-4 w-16 mb-2 rounded"></div>
            <div class="skeleton-pulse h-6 w-48 mb-6 rounded"></div>
            <div class="skeleton-pulse h-4 w-full mb-2 rounded"></div>
            <div class="skeleton-pulse h-4 w-3/4 rounded"></div>
        </div>
    <?php endfor; ?>
</div>';

$c = preg_replace('/<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">.*?<\?php endif; \?>\n<\/div>/s', $skeletons, $c);

$js = '
<script>
(function() {
    function loadTemplates() {
        fetch(`../api/get_notifications_data.php?view=templates`)
            .then(r => r.json())
            .then(res => {
                const grid = document.getElementById(\'templatesGrid\');
                if (!res.success || !res.templates || res.templates.length === 0) {
                    grid.innerHTML = `<div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">description</span><p class="text-slate-500 font-semibold text-lg">No templates yet</p><p class="text-sm text-slate-400 mt-1">Create your first notification template to get started.</p></div>`;
                    return;
                }
                
                window.templatesData = {};
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                
                grid.innerHTML = res.templates.map(template => {
                    window.templatesData[template.id] = template;
                    
                    const subjectHtml = template.subject ? `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Subject Line</p><p class="text-sm font-bold text-slate-900 dark:text-white">${escapeHtml(template.subject)}</p></div>` : \'\';
                    
                    const varsHtml = [\'name\', \'opportunity\', \'company\', \'course\', \'percentage\', \'barangay\'].map(v => `<span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 rounded-md">{{${v}}}</span>`).join(\'\');
                    
                    return `
                    <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all flex flex-col">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">${escapeHtml(template.type)}</p>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">${escapeHtml(template.name)}</h3>
                                </div>
                                <div class="flex gap-1">
                                    <button onclick="openEditModal(${template.id})" class="p-2 hover:bg-slate-100 rounded-lg text-slate-600 hover:text-slate-900">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this template?\')">
                                        <input type="hidden" name="template_id" value="${template.id}">
                                        <input type="hidden" name="form_nonce" value="${nonce}">
                                        <button type="submit" name="delete_template" class="p-2 hover:bg-red-50 rounded-lg text-red-600 hover:text-red-700">
                                            <span class="material-symbols-outlined text-xl">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4 flex-1">
                            ${subjectHtml}
                            <div>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Message Body</p>
                                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">"${escapeHtml(template.body)}"</p>
                                <div class="mt-4 flex flex-wrap gap-2">${varsHtml}</div>
                            </div>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end p-6">
                            <button onclick="testTemplate(${template.id})" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
                                Test Template <span class="material-symbols-outlined text-sm">send</span>
                            </button>
                        </div>
                    </div>`;
                }).join(\'\');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return \'\';
        return String(str).replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/"/g, \'&quot;\');
    }
    
    loadTemplates();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

// Remove old PHP initialization of templatesData
$c = preg_replace('/var templatesData = \{\};\s*<\?php foreach \(\$templates as \$t\): \?>\s*templatesData\[<\?php echo \$t\[\'id\'\]; \?>\] = <\?php echo json_encode\(\$t\); \?>;\s*<\?php endforeach; \?>/s', '', $c);

// Attach the JS functions to window object
$c = preg_replace('/function openCreateModal/', 'window.openCreateModal = function', $c);
$c = preg_replace('/function openEditModal/', 'window.openEditModal = function', $c);
$c = preg_replace('/function testTemplate/', 'window.testTemplate = function', $c);

$c = str_replace('<script>', "<script>\n(function() {", $c);
$c = str_replace('</script>', "})();\n</script>", $c);
// The last script tags:
$c = str_replace("})();\n</script>\n\n<?php require_once", "</script>\n\n<?php require_once", $c);

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/notification-templates.php', $c);
echo "Done notification-templates.php\n";
