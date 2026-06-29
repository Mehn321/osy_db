<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../init.php';

// Ensure user is logged in
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $result = $user->changePassword($_SESSION['user_id'], $current_password, $new_password);
        if ($result['success']) {
            $_SESSION['temp_password_required'] = 0;
            $success = 'Password successfully reset! Redirecting to dashboard...';
            header("refresh:2;url=dashboard.php");
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Temporary Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col justify-center items-center px-4 relative overflow-hidden">
    <!-- Decorative Background -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-cyan-100 rounded-full blur-3xl opacity-50"></div>

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 border border-slate-200 relative z-10">
        <div class="text-center mb-8">
            <span class="material-symbols-outlined text-5xl text-blue-900 mb-2">lock_reset</span>
            <h2 class="text-2xl font-bold text-slate-900">Change Password Required</h2>
            <p class="text-slate-600 text-sm mt-1">You are logging in with a temporary password. You must change your password to continue.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-start gap-2">
                <span class="material-symbols-outlined text-base mt-0.5">error</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-start gap-2">
                <span class="material-symbols-outlined text-base mt-0.5">check_circle</span>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Hidden CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Current Temporary Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">vpn_key</span></span>
                    <input type="password" name="current_password" required class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="Enter temporary password">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">lock</span></span>
                    <input type="password" name="new_password" required minlength="6" class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="At least 6 characters">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Confirm New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">lock</span></span>
                    <input type="password" name="confirm_password" required minlength="6" class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="Re-enter new password">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98] mt-8">
                Update Password & Continue
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="logout.php" class="text-xs text-red-600 hover:underline font-bold uppercase tracking-wider">Cancel and Logout</a>
        </div>
    </div>
</body>
</html>
