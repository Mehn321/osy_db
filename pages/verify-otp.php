<?php
$pageTitle = 'Email Verification';
require_once __DIR__ . '/../init.php';

if ($user->isLoggedIn()) {
    if (!empty($_SESSION['temp_password_required'])) {
        header('Location: password-reset.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

if (!$user->isOtpVerificationPending()) {
    $loginPage = function_exists('getLoginPageForRole') ? getLoginPageForRole() : 'youth-login.php';
    header('Location: ' . $loginPage);
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp = trim($_POST['otp_code'] ?? '');
        $result = $user->verifyEmailOtpForPendingLogin($otp);

        if ($result['success']) {
            if (!empty($_SESSION['temp_password_required'])) {
                header('Location: password-reset.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }

        $error = $result['message'] ?? 'Verification failed.';
    } elseif (isset($_POST['resend_otp'])) {
        $result = $user->resendEmailOtpForPendingLogin();
        if ($result['success']) {
            $success = $result['message'] ?? 'A new verification code was sent.';
        } else {
            $error = $result['message'] ?? 'Unable to resend verification code.';
        }
    }
}

$identityLabel = $user->getPendingOtpIdentityLabel();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Youth Profiling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center px-6 py-12 relative overflow-hidden">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full blur-3xl opacity-50"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-cyan-100 rounded-full blur-3xl opacity-50"></div>

    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-slate-200 p-8 relative z-10">
        <div class="text-center mb-8">
            <span class="material-symbols-outlined text-5xl text-blue-900 mb-2">mark_email_unread</span>
            <h1 class="text-2xl font-bold text-slate-900">Verify Your Login</h1>
            <p class="text-sm text-slate-600 mt-2">A 6-digit code was sent to <?php echo htmlspecialchars($identityLabel); ?>.</p>
            <p class="text-xs text-slate-500 mt-1">For security, OTP is sent through email only.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
            <label class="block text-xs font-bold uppercase tracking-widest text-slate-600">Verification Code</label>
            <input
                type="text"
                name="otp_code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                pattern="[0-9]{6}"
                required
                placeholder="Enter 6-digit code"
                class="w-full rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 text-center text-lg tracking-[0.35em] font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" name="verify_otp" class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-3 rounded-lg font-semibold hover:opacity-95 transition">Verify and Continue</button>
            <button type="submit" name="resend_otp" formnovalidate class="w-full border border-slate-300 text-slate-700 py-3 rounded-lg font-semibold hover:bg-slate-100 transition">Resend Code</button>
        </form>

        <div class="text-center mt-6">
            <a href="logout.php" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Cancel and return to login</a>
        </div>
    </div>
</body>

</html>