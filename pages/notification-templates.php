<?php
$pageTitle = 'Notification Templates';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole('lydo');

require_once __DIR__ . '/../includes/header.php';

$notification = new Notification($database);

// Handle form submissions
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        if (isset($_POST['create_template'])) {
            $result = $notification->createTemplate([
                'name' => $_POST['name'],
                'subject' => $_POST['subject'],
                'body' => $_POST['body'],
                'type' => $_POST['type']
            ]);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                header('Location: notification-templates.php?success=created');
                exit;
            }
        } elseif (isset($_POST['update_template'])) {
            $result = $notification->updateTemplate(intval($_POST['template_id']), [
                'name' => $_POST['name'],
                'subject' => $_POST['subject'],
                'body' => $_POST['body'],
                'type' => $_POST['type']
            ]);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                header('Location: notification-templates.php?success=updated');
                exit;
            }
        } elseif (isset($_POST['delete_template'])) {
            $result = $notification->deleteTemplate($_POST['template_id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                header('Location: notification-templates.php?success=deleted');
                exit;
            }
        }
    }
}

if (isset($_GET['success'])) {
    $actions = ['created' => 'Template created successfully!', 'updated' => 'Template updated successfully!', 'deleted' => 'Template deleted successfully!'];
    $message = $actions[$_GET['success']] ?? 'Action completed!';
    $messageType = 'success';
}


?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-600 mb-2">
            <span>Communications</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900 font-semibold">Notification Templates</span>
        </nav>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Notification Templates</h2>
        <p class="text-slate-600 mt-1">Manage standardized messages for youth communication.</p>
    </div>
    <a href="notifications.php" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-800 px-5 py-3 text-sm font-bold text-blue-900 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
        <span class="material-symbols-outlined text-base">campaign</span> Notifications
    </a>
    <button onclick="openCreateModal()" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-900 to-blue-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined">add</span>
        Create Template
    </button>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Templates Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="templatesGrid">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col h-48">
            <div class="skeleton-pulse h-4 w-16 mb-2 rounded"></div>
            <div class="skeleton-pulse h-6 w-48 mb-6 rounded"></div>
            <div class="skeleton-pulse h-4 w-full mb-2 rounded"></div>
            <div class="skeleton-pulse h-4 w-3/4 rounded"></div>
        </div>
    <?php endfor; ?>
</div>

<!-- Create/Edit Template Modal -->
<div id="templateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 id="tmplModalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Create New Template</h3>
            </div>
            <form id="templateForm" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                <input type="hidden" id="tmplId" name="template_id">
                <input type="hidden" id="tmplAction" name="create_template" value="1">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Template Name</label>
                    <input type="text" name="name" id="tmplName" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Type</label>
                    <select name="type" id="tmplType" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="SMS">SMS</option>
                        <option value="Email">Email</option>
                        <option value="SMS/Email">SMS/Email</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Subject Line (Email only)</label>
                    <input type="text" name="subject" id="tmplSubject" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Message Body</label>
                    <textarea name="body" id="tmplBody" rows="4" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('templateModal').classList.add('hidden')" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" id="tmplSubmitBtn" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Template Modal -->
<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Template Preview</h3>
                <button onclick="document.getElementById('testModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="p-6 space-y-4">
                <div id="testSubjectContainer" class="hidden">
                    <p class="text-xs font-bold text-slate-500 uppercase">Subject</p>
                    <p id="testSubject" class="font-bold text-slate-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase mb-2">Message Preview</p>
                    <div id="testBody" class="bg-slate-50 dark:bg-slate-700 p-4 rounded-xl text-sm text-slate-700 dark:text-slate-300 leading-relaxed"></div>
                </div>
                <p class="text-xs text-slate-400 italic">Variables replaced with sample data for preview.</p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    

    window.openCreateModal = function() {
        document.getElementById('tmplModalTitle').textContent = 'Create New Template';
        document.getElementById('tmplAction').name = 'create_template';
        document.getElementById('tmplSubmitBtn').textContent = 'Create Template';
        document.getElementById('templateForm').reset();
        document.getElementById('templateModal').classList.remove('hidden');
    }

    window.openEditModal = function(id) {
        const t = templatesData[id];
        if (t) {
            document.getElementById('tmplModalTitle').textContent = 'Edit Template';
            document.getElementById('tmplAction').name = 'update_template';
            document.getElementById('tmplSubmitBtn').textContent = 'Update Template';
            document.getElementById('tmplId').value = id;
            document.getElementById('tmplName').value = t.name;
            document.getElementById('tmplType').value = t.type;
            document.getElementById('tmplSubject').value = t.subject || '';
            document.getElementById('tmplBody').value = t.body;
            document.getElementById('templateModal').classList.remove('hidden');
        }
    }

    window.testTemplate = function(id) {
        var t = templatesData[id];
        if (t) {
            var sampleData = {
                'name': 'Juan Dela Cruz',
                'opportunity': 'TESDA NCII Welding',
                'company': 'AutoWorks Ltd.',
                'course': 'Basic Web Design',
                'percentage': '15',
                'barangay': 'Barangay 1'
            };

            let body = t.body;
            let subject = t.subject || '';

            for (const [key, value] of Object.entries(sampleData)) {
                body = body.replace(new RegExp('\\{\\{' + key + '\\}\\}', 'g'), '<strong class="text-blue-900">' + value + '</strong>');
                subject = subject.replace(new RegExp('\\{\\{' + key + '\\}\\}', 'g'), value);
            }

            if (subject) {
                document.getElementById('testSubjectContainer').classList.remove('hidden');
                document.getElementById('testSubject').textContent = subject;
            } else {
                document.getElementById('testSubjectContainer').classList.add('hidden');
            }

            document.getElementById('testBody').innerHTML = body;
            document.getElementById('testModal').classList.remove('hidden');
        }
    }
</script>


<script>
(function() {
    function loadTemplates() {
        fetch(`../api/get_notifications_data.php?view=templates`)
            .then(r => r.json())
            .then(res => {
                const grid = document.getElementById('templatesGrid');
                if (!res.success || !res.templates || res.templates.length === 0) {
                    grid.innerHTML = `<div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">description</span><p class="text-slate-500 font-semibold text-lg">No templates yet</p><p class="text-sm text-slate-400 mt-1">Create your first notification template to get started.</p></div>`;
                    return;
                }
                
                window.templatesData = {};
                const nonce = document.querySelector('input[name="form_nonce"]')?.value || '<?php echo htmlspecialchars(getFormNonce()); ?>';
                
                grid.innerHTML = res.templates.map(template => {
                    window.templatesData[template.id] = template;
                    
                    const subjectHtml = template.subject ? `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Subject Line</p><p class="text-sm font-bold text-slate-900 dark:text-white">${escapeHtml(template.subject)}</p></div>` : '';
                    
                    const varsHtml = ['name', 'opportunity', 'company', 'course', 'percentage', 'barangay'].map(v => `<span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 rounded-md">{{${v}}}</span>`).join('');
                    
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
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
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
                }).join('');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    loadTemplates();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>