<?php
$pageTitle = 'Verify Registration';
require_once __DIR__ . '/../init.php';

$pending = $_SESSION['pending_signup_verification'] ?? [];
if (empty($pending['user_id'])) {
    header('Location: youth-signup.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $channel = isset($_POST['verify_email']) ? 'email' : 'phone';
    $result = $user->verifySignupOtp($channel, $_POST[$channel === 'email' ? 'email_code' : 'phone_code'] ?? '');
    if (!$result['success']) {
        $error = $result['message'];
    } elseif ($result['complete']) {
        $account = $database->fetchOne("SELECT role FROM users WHERE id = ?", [(int) $pending['user_id']], 'i');
        header('Location: ' . (($account['role'] ?? '') === 'youth' ? 'youth-login.php' : 'provider-login.php') . '?verified=1');
        exit;
    } else {
        $success = 'Code verified. Enter the code from your other channel.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration</title>
    <script src="<?php echo htmlspecialchars($basePath ?? ''); ?>/assets/js/tailwind.js"></script>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center px-6 py-12 text-slate-900">
    <main class="w-full max-w-md bg-white rounded-xl shadow-xl border border-slate-200 p-8">
        <h1 class="text-2xl font-bold mb-2">Verify your registration</h1>
        <p class="text-sm text-slate-600 mb-6">Enter both 6-digit codes sent to your email and mobile number.</p>
        <?php if ($error): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <form method="POST" class="space-y-4">
            <input name="email_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="Email code" class="w-full border border-slate-300 rounded-lg px-4 py-3">
            <button name="verify_email" value="1" class="w-full bg-blue-900 text-white rounded-lg py-3 font-semibold">Verify Email Code</button>
        </form>
        <form method="POST" class="space-y-4 mt-4">
            <input name="phone_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required placeholder="Mobile code" class="w-full border border-slate-300 rounded-lg px-4 py-3">
            <button name="verify_phone" value="1" class="w-full bg-slate-800 text-white rounded-lg py-3 font-semibold">Verify Mobile Code</button>
        </form>
    </main>
</body>

</html>