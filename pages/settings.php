<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: lydo-login.php');
    exit;
}
requireRole('lydo');

require_once __DIR__ . '/../includes/header.php';

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'profile';
$matching = new Matching($database);
$syncStats = $matching->getGlobalSyncStats();

// Get current system settings
$sys_settings = [];
$raw_settings = $database->fetchAll("SELECT * FROM system_settings");
foreach ($raw_settings as $s) {
    $sys_settings[$s['setting_key']] = $s['setting_value'];
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This request has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
    } else {
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
                // Use INSERT ... ON DUPLICATE KEY UPDATE so new keys are created automatically
                $database->execute(
                    "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                    [$f, $_POST[$f] ?? ''],
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
        } elseif (isset($_POST['update_ai_settings'])) {
            $fields = ['gemini_api_key', 'ai_enabled'];

            // Handle AI enabled toggle
            $aiEnabled = isset($_POST['ai_enabled']) ? '1' : '0';
            $database->execute(
                "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                ['ai_enabled', $aiEnabled],
                "ss"
            );

            // Handle API key (only update if not empty)
            if (!empty($_POST['gemini_api_key'])) {
                $database->execute(
                    "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                    ['gemini_api_key', $_POST['gemini_api_key']],
                    "ss"
                );
            }

            $message = "AI settings updated successfully.";
            $messageType = "success";
            $activeTab = 'ai';

            // Refresh sys_settings array
            $sys_settings['ai_enabled'] = $aiEnabled;
            if (!empty($_POST['gemini_api_key'])) {
                $sys_settings['gemini_api_key'] = $_POST['gemini_api_key'];
            }
        }
    }
}

// Load AI usage data
require_once __DIR__ . '/../Classes/GeminiService.php';
$gemini = new GeminiService($database);
$aiStats = $gemini->getUsageStats();

// Load matching sync stats
$matching = new Matching($database);
$syncStats = $matching->getGlobalSyncStats();
$scoringPct = $syncStats['total_possible'] > 0
    ? round(($syncStats['existing_matches'] / $syncStats['total_possible']) * 100, 1)
    : 0;

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
                <button onclick="switchTab('ai')" id="tab-ai" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'ai' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    AI Services
                    <?php if ($aiStats['rate_limits']['daily_pct'] >= 80): ?>
                        <span class="ml-auto w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    <?php endif; ?>
                </button>
                <button onclick="switchTab('appearance')" id="tab-appearance" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'appearance' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">palette</span>
                    Appearance
                </button>
                <button onclick="switchTab('match-youth')" id="tab-match-youth" class="w-full text-left px-4 py-3 <?php echo $activeTab === 'match-youth' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'; ?> rounded-lg font-semibold flex items-center gap-3 transition-colors">
                    <span class="material-symbols-outlined">hub</span>
                    Match Youth
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
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
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
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password" required placeholder="Enter current password" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">New Password</label>
                        <div class="relative">
                            <input type="password" name="new_password" required placeholder="Enter new password (12+ chars, letters/numbers/symbols)" minlength="12" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" required placeholder="Confirm new password" minlength="12" class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
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
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                    <!-- Traccar SMS Section -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-blue-900 dark:text-blue-400 mb-4">
                            <span class="material-symbols-outlined text-xl">sms</span>
                            <h4 class="font-bold uppercase tracking-widest text-xs">Traccar SMS Gateway</h4>
                        </div>

                        <div class="p-5 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4">
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <p class="text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                    <span class="material-symbols-outlined text-sm mt-0.5">info</span>
                                    <span><strong>Cloud Mode:</strong> SMS is routed through Traccar's cloud relay. Requires a Cloud Token from the Traccar Android App &gt; Cloud &gt; Cloud Token.</span>
                                </p>
                            </div>

                            <div>
                                <label id="traccar-token-label" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Traccar Cloud Token</label>
                                <div class="relative">
                                    <input type="password" name="traccar_token" value="<?php echo htmlspecialchars($sys_settings['traccar_token'] ?? ''); ?>" placeholder="Enter Traccar Cloud Token" class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-2 italic">Found in Traccar Android App &gt; Cloud &gt; Cloud Token.</p>
                            </div>
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
                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Test SMS Gateway</label>
                            <div class="flex gap-2">
                                <input type="text" name="test_phone" placeholder="Enter phone (e.g. 0912...)" class="flex-1 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-sm rounded-lg" />
                                <button type="submit" name="test_sms" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800">Send Test</button>
                            </div>
                        </form>
                        <!-- Test Email form -->
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
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

        <!-- AI Services -->
        <div id="section-ai" class="<?php echo $activeTab !== 'ai' ? 'hidden' : ''; ?>">
            <?php
            $dailyPct = $aiStats['rate_limits']['daily_pct'];
            $barColor = $dailyPct >= 90 ? 'bg-red-500' : ($dailyPct >= 60 ? 'bg-amber-500' : 'bg-emerald-500');
            $statusColor = $dailyPct >= 90 ? 'text-red-600' : ($dailyPct >= 60 ? 'text-amber-600' : 'text-emerald-600');
            $statusBg = $dailyPct >= 90 ? 'bg-red-50 border-red-200' : ($dailyPct >= 60 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200');
            $statusLabel = $dailyPct >= 90 ? 'Critical' : ($dailyPct >= 60 ? 'Moderate' : 'Healthy');
            ?>

            <!-- AI Status Banner -->
            <div class="<?php echo $statusBg; ?> border rounded-xl p-5 flex items-center gap-4">
                <div class="p-3 rounded-lg <?php echo $dailyPct >= 90 ? 'bg-red-100' : ($dailyPct >= 60 ? 'bg-amber-100' : 'bg-emerald-100'); ?>">
                    <span class="material-symbols-outlined text-2xl <?php echo $statusColor; ?>">monitoring</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold <?php echo $statusColor; ?> text-sm">AI Service Status: <?php echo $statusLabel; ?></h4>
                    <p class="text-xs text-slate-600 mt-0.5">
                        <?php if ($dailyPct >= 90): ?>
                            Daily API limit is almost exhausted. Consider reducing AI operations until quota resets.
                        <?php elseif ($dailyPct >= 60): ?>
                            API usage is moderate. Monitor usage to avoid hitting limits.
                        <?php else: ?>
                            All AI services are operating normally within free tier limits.
                        <?php endif; ?>
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black <?php echo $statusColor; ?>"><?php echo $dailyPct; ?>%</span>
                    <p class="text-[10px] text-slate-500 font-medium">Daily Quota Used</p>
                </div>
            </div>

            <!-- Daily Usage Progress -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 mt-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">auto_awesome</span>
                        Google Gemini AI Usage
                    </h3>
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-[10px] font-bold rounded-full uppercase tracking-wider">
                        Free Tier
                    </span>
                </div>

                <!-- Model & Tier Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Active Model</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($aiStats['model']); ?></p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Rate Limit</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo $aiStats['rate_limits']['minute_limit']; ?> RPM</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Daily Cap</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo number_format($aiStats['rate_limits']['daily_limit']); ?> RPD</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Resets On</p>
                        <p class="text-sm font-bold text-emerald-600"><?php echo $aiStats['rate_limits']['resets_at']; ?></p>
                    </div>
                </div>

                <!-- Daily Usage Bar -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Today's API Calls</span>
                        <span class="text-sm font-mono font-bold <?php echo $statusColor; ?>">
                            <?php echo number_format($aiStats['rate_limits']['daily_used']); ?> / <?php echo number_format($aiStats['rate_limits']['daily_limit']); ?>
                        </span>
                    </div>
                    <div class="w-full h-4 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full <?php echo $barColor; ?> rounded-full transition-all duration-700 ease-out relative"
                            style="width: <?php echo min(100, $dailyPct); ?>%">
                            <?php if ($dailyPct > 15): ?>
                                <span class="absolute inset-0 flex items-center justify-center text-[9px] font-black text-white">
                                    <?php echo $dailyPct; ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-2 italic">Daily quota resets at midnight UTC. Free tier allows ~<?php echo number_format($aiStats['rate_limits']['daily_limit']); ?> requests/day for <?php echo htmlspecialchars($aiStats['model']); ?>.</p>
                </div>

                <!-- Per-Minute Rate -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Current Minute Rate</span>
                        <span class="text-sm font-mono font-bold <?php echo $aiStats['rate_limits']['minute_used'] >= $aiStats['rate_limits']['minute_limit'] ? 'text-red-600' : 'text-emerald-600'; ?>">
                            <?php echo $aiStats['rate_limits']['minute_used']; ?> / <?php echo $aiStats['rate_limits']['minute_limit']; ?> RPM
                        </span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <?php $rpmPct = $aiStats['rate_limits']['minute_limit'] > 0 ? min(100, round(($aiStats['rate_limits']['minute_used'] / $aiStats['rate_limits']['minute_limit']) * 100)) : 0; ?>
                        <div class="h-full <?php echo $rpmPct >= 90 ? 'bg-red-500' : 'bg-blue-500'; ?> rounded-full transition-all" style="width: <?php echo $rpmPct; ?>%"></div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Today -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/10 rounded-xl p-5 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-blue-600 text-lg">today</span>
                            <span class="text-xs font-bold text-blue-800 dark:text-blue-400 uppercase tracking-wider">Today</span>
                        </div>
                        <p class="text-3xl font-black text-blue-900 dark:text-white"><?php echo number_format($aiStats['today']['calls']); ?></p>
                        <p class="text-[10px] text-blue-700 dark:text-blue-400 font-medium mt-1">API calls made</p>
                        <div class="flex gap-4 mt-3 pt-3 border-t border-blue-200 dark:border-blue-700">
                            <div>
                                <p class="text-xs font-bold text-emerald-700"><?php echo number_format($aiStats['today']['success']); ?></p>
                                <p class="text-[9px] text-slate-500">Success</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-red-600"><?php echo number_format($aiStats['today']['failed']); ?></p>
                                <p class="text-[9px] text-slate-500">Failed</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo number_format($aiStats['today']['tokens']); ?></p>
                                <p class="text-[9px] text-slate-500">Tokens</p>
                            </div>
                        </div>
                    </div>

                    <!-- This Month -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/10 rounded-xl p-5 border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-purple-600 text-lg">calendar_month</span>
                            <span class="text-xs font-bold text-purple-800 dark:text-purple-400 uppercase tracking-wider">This Month</span>
                        </div>
                        <p class="text-3xl font-black text-purple-900 dark:text-white"><?php echo number_format($aiStats['month']['calls']); ?></p>
                        <p class="text-[10px] text-purple-700 dark:text-purple-400 font-medium mt-1">API calls made</p>
                        <div class="flex gap-4 mt-3 pt-3 border-t border-purple-200 dark:border-purple-700">
                            <div>
                                <p class="text-xs font-bold text-emerald-700"><?php echo number_format($aiStats['month']['success']); ?></p>
                                <p class="text-[9px] text-slate-500">Success</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo number_format($aiStats['month']['tokens']); ?></p>
                                <p class="text-[9px] text-slate-500">Tokens</p>
                            </div>
                        </div>
                    </div>

                    <!-- All Time -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700/30 dark:to-slate-700/10 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-slate-600 text-lg">database</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">All Time</span>
                        </div>
                        <p class="text-3xl font-black text-slate-900 dark:text-white"><?php echo number_format($aiStats['all_time']['calls']); ?></p>
                        <p class="text-[10px] text-slate-600 dark:text-slate-400 font-medium mt-1">Total API calls</p>
                        <div class="flex gap-4 mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                            <div>
                                <p class="text-xs font-bold text-emerald-700"><?php echo number_format($aiStats['all_time']['success']); ?></p>
                                <p class="text-[9px] text-slate-500">Success</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo number_format($aiStats['all_time']['tokens']); ?></p>
                                <p class="text-[9px] text-slate-500">Tokens</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scoring Coverage -->
                <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-6 border border-slate-200 dark:border-slate-700 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">verified</span>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">AI Scoring Coverage</h4>
                        </div>
                        <span class="text-2xl font-black <?php echo $scoringPct >= 100 ? 'text-emerald-600' : ($scoringPct >= 50 ? 'text-amber-600' : 'text-red-600'); ?>">
                            <?php echo $scoringPct; ?>%
                        </span>
                    </div>
                    <div class="w-full h-3 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-indigo-500 rounded-full transition-all duration-700" style="width: <?php echo min(100, $scoringPct); ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400">
                        <span><strong><?php echo number_format($syncStats['existing_matches']); ?></strong> scored out of <strong><?php echo number_format($syncStats['total_possible']); ?></strong> possible matches</span>
                        <span><?php echo number_format($syncStats['missing_matches']); ?> remaining</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                        <div class="text-center">
                            <p class="text-lg font-black text-slate-900 dark:text-white"><?php echo number_format($syncStats['profiles_count']); ?></p>
                            <p class="text-[9px] text-slate-500 font-medium uppercase tracking-wider">Youth Profiles</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-black text-slate-900 dark:text-white"><?php echo number_format($syncStats['opportunities_count']); ?></p>
                            <p class="text-[9px] text-slate-500 font-medium uppercase tracking-wider">Open Jobs</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-black text-slate-900 dark:text-white"><?php echo number_format($syncStats['existing_matches']); ?></p>
                            <p class="text-[9px] text-slate-500 font-medium uppercase tracking-wider">AI Scores Generated</p>
                        </div>
                    </div>
                </div>

                <!-- Last Call & Connection Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            Last API Call
                        </h4>
                        <?php if ($aiStats['last_call']): ?>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                <?php echo date('M d, Y \a\t h:i:s A', strtotime($aiStats['last_call']['created_at'])); ?>
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <?php if ($aiStats['last_call']['success']): ?>
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="text-xs text-green-700 font-medium">Success (HTTP <?php echo $aiStats['last_call']['http_status']; ?>)</span>
                                <?php else: ?>
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span class="text-xs text-red-700 font-medium">Failed (HTTP <?php echo $aiStats['last_call']['http_status']; ?>)</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-slate-500 italic">No API calls recorded yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-5 border border-slate-200 dark:border-slate-700">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">info</span>
                            Important Notes
                        </h4>
                        <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[14px] text-blue-500 mt-0.5">check_circle</span>
                                Free tier data may be used by Google for improvement
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[14px] text-blue-500 mt-0.5">check_circle</span>
                                Rate-limited to <?php echo $aiStats['rate_limits']['minute_limit']; ?> requests per minute
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[14px] text-amber-500 mt-0.5">warning</span>
                                HTTP 429 errors mean quota is temporarily exhausted
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- API Configuration -->
                <div class="border-t border-slate-200 dark:border-slate-700 pt-8 mt-8">
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">key</span>
                        API Configuration & Service Control
                    </h4>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

                        <!-- AI Service Toggle -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="font-bold text-slate-900 dark:text-white mb-1">AI Service Status</h5>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        <?php if ($sys_settings['ai_enabled'] ?? '1' === '1'): ?>
                                            ✅ AI services are <strong>enabled</strong>. System will use Gemini for skill matching and analysis.
                                        <?php else: ?>
                                            ⚠️ AI services are <strong>disabled</strong>. Skill matching will use rule-based scoring only.
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="ai_enabled" value="1" class="sr-only peer" <?php echo ($sys_settings['ai_enabled'] ?? '1') === '1' ? 'checked' : ''; ?> />
                                    <div class="w-14 h-8 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- API Key Field -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Google Gemini API Key</label>
                            <div class="relative">
                                <input
                                    type="password"
                                    name="gemini_api_key"
                                    placeholder="Leave blank to keep current key"
                                    value="<?php echo !empty($sys_settings['gemini_api_key']) ? str_repeat('*', strlen($sys_settings['gemini_api_key']) - 4) . substr($sys_settings['gemini_api_key'], -4) : ''; ?>"
                                    class="w-full px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-900" />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                Get your API key from <a href="https://ai.google.dev/tutorials/python_quickstart" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a>.
                                <?php if (!empty($sys_settings['gemini_api_key'])): ?>
                                    <span class="text-emerald-600 ml-2">✓ API key is configured</span>
                                <?php else: ?>
                                    <span class="text-amber-600 ml-2">⚠ No API key configured. AI features will not work.</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <button type="submit" name="update_ai_settings" value="1" class="px-8 py-3 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                            Save AI Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Appearance Settings -->
        <div id="section-appearance" class="<?php echo $activeTab !== 'appearance' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Appearance Settings</h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white mb-1">Dark Mode</h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Toggle dark mode interface theme.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="theme-toggle-checkbox" class="sr-only peer" <?php echo (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark') ? 'checked' : ''; ?> />
                            <div class="w-14 h-8 bg-slate-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Match Youth Settings -->
        <div id="section-match-youth" class="<?php echo $activeTab !== 'match-youth' ? 'hidden' : ''; ?>">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Match Youth</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">Create and synchronize youth-to-opportunity matches. Employers review and decide on candidates.</p>
                <div class="p-5 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <span class="material-symbols-outlined text-amber-600 text-3xl">sync</span>
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-900 dark:text-white">Match score synchronization</h4>
                            <p class="text-sm text-amber-800 dark:text-amber-300"><strong><?php echo (int)$syncStats['missing_matches']; ?></strong> potential matches need scores.</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <input id="autoSyncToggle" type="checkbox" class="rounded" /> Automatic
                        </label>
                        <button type="button" id="syncNowButton" class="px-4 py-2.5 bg-amber-600 text-white rounded-lg font-bold hover:bg-amber-700">Sync now</button>
                    </div>
                    <p id="syncStatus" class="hidden mt-3 text-sm text-blue-800 dark:text-blue-300"></p>
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
                        <span>Youth Profiling System</span>
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

    document.getElementById('theme-toggle-checkbox')?.addEventListener('change', function(e) {
        const theme = e.target.checked ? 'dark' : 'light';
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        fetch('../api/update_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    theme: theme
                })
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) console.error(d.message || 'Error updating theme');
            })
            .catch(console.error);
    });

    async function triggerGlobalSync(confirmBeforeStart = true) {
        const startSync = async () => {
            const status = document.getElementById('syncStatus');
            const button = document.getElementById('syncNowButton');
            if (button) button.disabled = true;
            if (status) {
                status.textContent = 'Synchronization started. Scores will update in the background.';
                status.classList.remove('hidden');
            }
            try {
                const response = await fetch('../api/trigger_global_sync.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                if (!result.success && status) status.textContent = result.message || 'Synchronization could not be started.';
            } catch (error) {
                if (status) status.textContent = 'Synchronization could not be started.';
            } finally {
                if (button) button.disabled = false;
            }
        };

        if (confirmBeforeStart) {
            customConfirm('Start synchronization for all youth matches? This may take several minutes.', (confirmed) => {
                if (confirmed) startSync();
            });
        } else {
            startSync();
        }
    }

    let autoSyncTimer = null;

    function configureAutoSync(enabled) {
        localStorage.setItem('matchingAutoSync', enabled ? '1' : '0');
        if (autoSyncTimer) clearInterval(autoSyncTimer);
        if (enabled) {
            triggerGlobalSync(false);
            autoSyncTimer = setInterval(() => triggerGlobalSync(false), 600000);
        }
    }

    document.getElementById('syncNowButton')?.addEventListener('click', () => triggerGlobalSync());
    document.getElementById('autoSyncToggle')?.addEventListener('change', function() {
        configureAutoSync(this.checked);
    });
    const autoSyncToggle = document.getElementById('autoSyncToggle');
    if (autoSyncToggle) {
        autoSyncToggle.checked = localStorage.getItem('matchingAutoSync') === '1';
        if (autoSyncToggle.checked) configureAutoSync(true);
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>