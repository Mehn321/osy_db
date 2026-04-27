<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/../includes/header.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'profile';

// Get current system settings
$sys_settings = [];
$raw_settings = $database->fetchAll("SELECT * FROM system_settings");
foreach ($raw_settings as $s) {
    $sys_settings[$s['setting_key']] = $s['setting_value'];
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = $user->updateProfile(
            $_SESSION['user_id'],
            $_POST['fullname'],
            $_POST['email']
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        if ($result['success']) {
            // Update session variables
            $_SESSION['fullname'] = $_POST['fullname'];
            $_SESSION['email'] = $_POST['email'];
        }
        $activeTab = 'profile';
    } elseif (isset($_POST['change_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $message = 'New password and confirmation do not match';
            $messageType = 'error';
        } else {
            $result = $user->changePassword(
                $_SESSION['user_id'],
                $_POST['current_password'],
                $_POST['new_password']
            );
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
        $activeTab = 'security';
    } elseif (isset($_POST['update_notifications'])) {
        $fields = ['traccar_token', 'gmail_user', 'gmail_app_password'];
        foreach ($fields as $f) {
            $database->execute(
                "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?",
                [$_POST[$f] ?? '', $f],
                "ss"
            );
        }
        $message = "Notification settings updated successfully.";
        $messageType = "success";
        $activeTab = 'notifications';

        // Refresh sys_settings array
        foreach ($fields as $f) {
            $sys_settings[$f] = $_POST[$f] ?? '';
        }
    } elseif (isset($_POST['test_sms'])) {
        require_once __DIR__ . '/../Classes/SmsService.php';
        $sms = new SmsService($database);
        // We'll test with the admin's own phone if available, or just a dummy
        $testResult = $sms->send($_POST['test_phone'] ?? '', "Profiling System: This is a test SMS message via Traccar.");
        $message = $testResult['message'];
        $messageType = $testResult['success'] ? 'success' : 'error';
        $activeTab = 'notifications';
    } elseif (isset($_POST['test_email'])) {
        require_once __DIR__ . '/../Classes/EmailService.php';
        $email = new EmailService($database);
        $testResult = $email->send($_POST['test_email_addr'] ?? '', "System Test", "<h1>Test Successful</h1><p>Your Gmail SMTP setup is working perfectly!</p>");
        $message = $testResult['message'];
        $messageType = $testResult['success'] ? 'success' : 'error';
        $activeTab = 'notifications';
    }
}

?>

<!-- Page Header -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Settings</h2>
    <p class="text-slate-600 dark:text-slate-400">Manage your account and system settings.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Sidebar Navigation -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <nav class="space-y-1 p-4">
                <button onclick="switchTab('profile')" id="tab-profile" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'profile' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">account_circle</span>
                    Profile
                </button>
                <button onclick="switchTab('security')" id="tab-security" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'security' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">security</span>
                    Security
                </button>
                <button onclick="switchTab('notifications')" id="tab-notifications" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'notifications' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">notifications_active</span>
                    Notifications
                </button>
                <button onclick="switchTab('about')" id="tab-about" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'about' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">info</span>
                    About System
                </button>
            </nav>
        </div>
    </div>

    <!-- Main Settings Content -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Profile Settings -->
        <div id="section-profile" class="<?php echo $activeTab !== 'profile' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Profile Information</h3>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'N/A'); ?>" disabled class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg disabled:opacity-60" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Full Name</label>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? 'N/A'); ?>" required class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?>" required class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Account Role</label>
                        <input type="text" value="<?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'N/A')); ?>" disabled class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg disabled:opacity-60" />
                    </div>

                    <button type="submit" name="update_profile" value="1" class="px-8 py-3 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Security Settings -->
        <div id="section-security" class="<?php echo $activeTab !== 'security' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Security Settings</h3>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Current Password</label>
                        <input type="password" name="current_password" required placeholder="Enter current password" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">New Password</label>
                        <input type="password" name="new_password" required placeholder="Enter new password (min 6 chars)" minlength="6" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" required placeholder="Confirm new password" minlength="6" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                    </div>

                    <button type="submit" name="change_password" value="1" class="px-8 py-3 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification Settings -->
        <div id="section-notifications" class="<?php echo $activeTab !== 'notifications' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">External Notification Services</h3>
                    <span class="px-2 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded uppercase tracking-wider">Manual Only</span>
                </div>

                <form method="POST" class="space-y-10">
                    <!-- Traccar SMS Section -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-blue-900 dark:text-blue-400 mb-4">
                            <span class="material-symbols-outlined text-xl">sms</span>
                            <h4 class="font-bold uppercase tracking-widest text-xs">Traccar SMS Gateway (Cloud Mode)</h4>
                        </div>
                        <div class="p-5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-200 dark:border-slate-700">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Traccar Cloud Token</label>
                            <input type="password" name="traccar_token" value="<?php echo htmlspecialchars($sys_settings['traccar_token'] ?? ''); ?>" placeholder="Enter Traccar Cloud Token" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            <p class="text-[10px] text-slate-500 mt-2 italic">Found in Traccar Android App > Cloud > Cloud Token.</p>
                        </div>
                    </div>

                    <!-- Gmail SMTP Section -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-red-600 dark:text-red-400 mb-4">
                            <span class="material-symbols-outlined text-xl">mail</span>
                            <h4 class="font-bold uppercase tracking-widest text-xs">Gmail Official SMTP</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-200 dark:border-slate-700">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Gmail Address</label>
                                <input type="email" name="gmail_user" value="<?php echo htmlspecialchars($sys_settings['gmail_user'] ?? ''); ?>" placeholder="example@gmail.com" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Gmail App Password</label>
                                <input type="password" name="gmail_app_password" value="<?php echo htmlspecialchars($sys_settings['gmail_app_password'] ?? ''); ?>" placeholder="16-character code" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-between items-center border-t border-slate-100 dark:border-slate-700">
                        <p class="text-xs text-slate-500">Changes affect the entire Municipal system.</p>
                        <button type="submit" name="update_notifications" class="bg-blue-900 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-800 transition-all shadow-md">
                            Save Notification Settings
                        </button>
                    </div>
                </form>

                <!-- Connection Testing Panel -->
                <div class="mt-12 pt-12 border-t-2 border-dashed border-slate-100 dark:border-slate-800">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-400">test_connect</span>
                        Connection Testing
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Test SMS form -->
                        <form method="POST" class="space-y-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Test SMS Gateway</label>
                            <div class="flex gap-2">
                                <input type="text" name="test_phone" placeholder="Enter phone (e.g. 0912...)" class="flex-1 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-sm rounded-lg" />
                                <button type="submit" name="test_sms" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800">Send Test</button>
                            </div>
                        </form>
                        <!-- Test Email form -->
                        <form method="POST" class="space-y-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Test Gmail SMTP</label>
                            <div class="flex gap-2">
                                <input type="email" name="test_email_addr" placeholder="Enter recipient email" class="flex-1 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-sm rounded-lg" />
                                <button type="submit" name="test_email" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800">Send Test</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- About System -->
        <div id="section-about" class="<?php echo $activeTab !== 'about' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">About System</h3>
                <div class="space-y-4 text-sm text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between py-3 border-b border-slate-200 dark:border-slate-700">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">System Name</span>
                        <span>Municipal KK Profiling System</span>
                    </div>
                    <div class="flex justify-between py-3 border-b border-slate-200 dark:border-slate-700">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Version</span>
                        <span>1.0.0</span>
                    </div>
                    <div class="flex justify-between py-3 border-b border-slate-200 dark:border-slate-700">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Database</span>
                        <span>MySQL (<?php echo DB_HOST; ?>)</span>
                    </div>
                    <div class="flex justify-between py-3 border-b border-slate-200 dark:border-slate-700">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">PHP Version</span>
                        <span><?php echo phpversion(); ?></span>
                    </div>
                    <div class="flex justify-between py-3">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Server Time</span>
                        <span><?php echo date('M d, Y H:i:s'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        // Hide all sections
        document.querySelectorAll('[id^="section-"]').forEach(s => s.classList.add('hidden'));
        // Deactivate all tabs
        document.querySelectorAll('[id^="tab-"]').forEach(t => {
            t.className = t.className.replace(/bg-blue-50|dark:bg-blue-900\/20|text-blue-900|dark:text-blue-400/g, '');
            t.classList.add('text-slate-700', 'dark:text-slate-300', 'hover:bg-slate-50', 'dark:hover:bg-slate-700/50');
        });
        // Show target section
        document.getElementById('section-' + tab).classList.remove('hidden');
        // Activate target tab
        const tabBtn = document.getElementById('tab-' + tab);
        tabBtn.classList.remove('text-slate-700', 'dark:text-slate-300', 'hover:bg-slate-50', 'dark:hover:bg-slate-700/50');
        tabBtn.classList.add('bg-blue-50', 'dark:bg-blue-900/20', 'text-blue-900', 'dark:text-blue-400');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>