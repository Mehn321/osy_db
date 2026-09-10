<?php
$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../init.php';

$error = '';
$success = '';
$step = $_GET['step'] ?? 'request';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $error = 'Duplicate or invalid form submission detected.';
    } else {
        if ($step === 'request') {
            $identifier = $_POST['identifier'] ?? '';
            if (empty($identifier)) {
                $error = 'Please enter your username or email address.';
            } else {
                $result = $user->initiatePasswordReset($identifier);
                if ($result['success']) {
                    header('Location: forgot-password.php?step=verify');
                    exit;
                } else {
                    $error = $result['message'] ?? 'An error occurred.';
                }
            }
        } elseif ($step === 'verify') {
            $otp = $_POST['otp'] ?? '';
            if (empty($otp)) {
                $error = 'Please enter the verification code.';
            } else {
                $result = $user->verifyPasswordResetOtp($otp);
                if ($result['success']) {
                    header('Location: forgot-password.php?step=reset');
                    exit;
                } else {
                    $error = $result['message'] ?? 'Invalid code.';
                }
            }
        } elseif ($step === 'reset') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($new_password) || empty($confirm_password)) {
                $error = 'All fields are required.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } elseif (strlen($new_password) < 12) {
                $error = 'Password must be at least 12 characters and include letters, numbers, and symbols.';
            } else {
                $result = $user->completePasswordReset($new_password);
                if ($result['success']) {
                    $success = 'Password successfully reset! Redirecting to login...';
                    header("refresh:2;url=youth-login.php");
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Youth Profiling System</title>
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
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
            <h2 class="text-2xl font-bold text-slate-900">Reset Password</h2>
            <?php if ($step === 'request'): ?>
                <p class="text-slate-600 text-sm mt-1">Enter your username or email address to receive an OTP.</p>
            <?php elseif ($step === 'verify'): ?>
                <p class="text-slate-600 text-sm mt-1">Enter the 6-digit verification code sent to your email.</p>
            <?php elseif ($step === 'reset'): ?>
                <p class="text-slate-600 text-sm mt-1">Create a new, strong password.</p>
            <?php endif; ?>
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

        <?php if (!$success): ?>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">

            <?php if ($step === 'request'): ?>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Username or Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">person</span></span>
                        <input type="text" name="identifier" required class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="Enter username or email">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98] mt-8">
                    Send Verification Code
                </button>
            <?php elseif ($step === 'verify'): ?>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Verification Code (OTP)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">pin</span></span>
                        <input type="text" name="otp" required pattern="\d{6}" maxlength="6" class="block w-full pl-10 pr-4 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm text-center tracking-widest font-mono text-xl" placeholder="123456">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98] mt-8">
                    Verify Code
                </button>
            <?php elseif ($step === 'reset'): ?>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">New Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">lock</span></span>
                        <input type="password" name="new_password" required minlength="12" class="block w-full pl-10 pr-12 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="Use 12+ chars with letters, numbers, symbols">
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Confirm New Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><span class="material-symbols-outlined text-lg">lock</span></span>
                        <input type="password" name="confirm_password" required minlength="12" class="block w-full pl-10 pr-12 py-2.5 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg text-sm" placeholder="Re-enter new password">
                        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98] mt-8">
                    Update Password
                </button>
            <?php endif; ?>
        </form>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="youth-login.php" class="text-xs text-slate-500 hover:text-slate-800 hover:underline font-bold uppercase tracking-wider">Back to Login</a>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.querySelectorAll('button.toggle-password-btn').forEach(btn => {
            const container = btn.closest('div');
            const input = container.querySelector('input');
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.innerHTML = '<span class="material-symbols-outlined">visibility_off</span>';
                } else {
                    input.type = 'password';
                    btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';
                }
            });
        });
    </script>
</body>
</html>
