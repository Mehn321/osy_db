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

$templates = $notification->getAllTemplates();
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
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (!empty($templates)): ?>
    <?php foreach ($templates as $template): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all flex flex-col">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1"><?php echo htmlspecialchars($template['type']); ?></p>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($template['name']); ?></h3>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="openEditModal(<?php echo $template['id']; ?>)" class="p-2 hover:bg-slate-100 rounded-lg text-slate-600 hover:text-slate-900">
                            <span class="material-symbols-outlined text-xl">edit</span>
                        </button>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                            <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                            <button type="submit" name="delete_template" class="p-2 hover:bg-red-50 rounded-lg text-red-600 hover:text-red-700">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4 flex-1">
                <?php if ($template['subject']): ?>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Subject Line</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($template['subject']); ?></p>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Message Body</p>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                        "<?php echo htmlspecialchars($template['body']); ?>"
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php
                        $variables = ['name', 'opportunity', 'company', 'course', 'percentage', 'barangay'];
                        foreach ($variables as $var):
                        ?>
                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 rounded-md">
                                {{<?php echo $var; ?>}}
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end p-6">
                <button onclick="testTemplate(<?php echo $template['id']; ?>)" class="text-xs font-bold text-blue-900 hover:underline flex items-center gap-1">
                    Test Template
                    <span class="material-symbols-outlined text-sm">send</span>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">description</span>
        <p class="text-slate-500 font-semibold text-lg">No templates yet</p>
        <p class="text-sm text-slate-400 mt-1">Create your first notification template to get started.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Template Variables Guide -->
<section class="bg-slate-50 dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 mt-12">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined">info</span>
        Template Variables Guide
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{name}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">The full name of the OSY candidate.</p>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{opportunity}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">Title of the matched training or job.</p>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{company}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">Company name for job opportunities.</p>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{course}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">Name of the training course.</p>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{percentage}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">Percentage improvement in match score.</p>
        </div>
        <div class="space-y-1">
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{barangay}}</p>
            <p class="text-xs text-slate-600 dark:text-slate-300">The residential area of the youth.</p>
        </div>
    </div>
</section>

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
    var templatesData = {};
    <?php foreach ($templates as $t): ?>
        templatesData[<?php echo $t['id']; ?>] = <?php echo json_encode($t); ?>;
    <?php endforeach; ?>

    function openCreateModal() {
        document.getElementById('tmplModalTitle').textContent = 'Create New Template';
        document.getElementById('tmplAction').name = 'create_template';
        document.getElementById('tmplSubmitBtn').textContent = 'Create Template';
        document.getElementById('templateForm').reset();
        document.getElementById('templateModal').classList.remove('hidden');
    }

    function openEditModal(id) {
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

    function testTemplate(id) {
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>