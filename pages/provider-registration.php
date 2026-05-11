<?php
$pageTitle = 'Provider Registration';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/Reference.php';

$userModel = new User($database);
$reference = new Reference($database);
$barangays = $reference->getByCategory('barangay');

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_provider'])) {
    try {
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'fullname' => trim($_POST['fullname']),
            'role' => $_POST['provider_type'],
            'barangay' => trim($_POST['barangay']),
            'provider_type' => trim($_POST['provider_type']),
            'provider_document_path' => null // Will be uploaded later if needed
        ];

        $result = $userModel->createUser($data);

        if ($result['success']) {
            $message = 'Provider registration submitted successfully. Your account is pending approval by the LYDO. You will receive a notification once approved.';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Registration - Youth Profiling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    <main class="flex-grow flex items-center justify-center px-6 py-12 relative overflow-hidden">
        <!-- Decorative Background -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-100 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-cyan-100 rounded-full blur-3xl opacity-50"></div>

        <div class="w-full max-w-[1100px] grid md:grid-cols-2 bg-white rounded-xl overflow-hidden shadow-2xl shadow-blue-200 border border-slate-200">
            <!-- Left Side: Branding -->
            <div class="hidden md:flex flex-col justify-between p-12 bg-gradient-to-br from-blue-900 to-blue-800 relative overflow-hidden text-white">
                <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2220%22 cy=%2220%22 r=%225%22 fill=%22white%22/></svg>')]"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-8">
                        <span class="material-symbols-outlined text-4xl">account_balance</span>
                        <span class="text-2xl font-bold tracking-tighter">Municipal KK</span>
                    </div>
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight mb-4">
                        Join Our<br />Provider Network.
                    </h1>
                    <p class="text-blue-100 text-lg max-w-sm font-medium">
                        Register as an employer or training provider to post opportunities and connect with qualified youth in your community.
                    </p>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md">
                        <div class="w-12 h-12 rounded-full bg-green-400 flex items-center justify-center text-blue-900">
                            <span class="material-symbols-outlined">verified</span>
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Verified Providers</p>
                            <p class="text-blue-100 text-xs">All providers undergo approval by the Local Youth Development Officer.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Registration Form -->
            <div class="p-8 md:p-16 flex flex-col justify-center">
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Provider Registration</h2>
                    <p class="text-slate-600 font-medium">Create your account to start posting opportunities.</p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
                        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
                            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="register_provider" value="1">

                    <!-- Provider Type Selection -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Provider Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="provider_type" value="employer" checked class="w-4 h-4 text-blue-900">
                                <span class="text-sm font-semibold text-slate-900">Employer</span>
                                <span class="text-xs text-slate-500 ml-auto">Post job openings</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="provider_type" value="training_provider" class="w-4 h-4 text-blue-900">
                                <span class="text-sm font-semibold text-slate-900">Training Provider</span>
                                <span class="text-xs text-slate-500 ml-auto">Offer training programs</span>
                            </label>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Full Name / Company Name</label>
                        <input type="text" name="fullname" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Enter your full name or company name">
                    </div>

                    <!-- Username -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Username</label>
                        <input type="text" name="username" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Choose a username">
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Email Address</label>
                        <input type="email" name="email" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="your@email.com">
                    </div>

                    <!-- Barangay -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Barangay</label>
                        <select name="barangay" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900">
                            <option value="">Select your barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <option value="<?php echo htmlspecialchars($barangay); ?>"><?php echo htmlspecialchars($barangay); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-blue-900 text-white py-3 px-6 rounded-xl text-sm font-semibold hover:bg-blue-800 transition-colors">
                        Submit Registration
                    </button>

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-sm text-slate-600">
                            Already have an account?
                            <a href="login.php" class="text-blue-900 font-semibold hover:underline">Sign in here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Auto-generate username from fullname
        document.querySelector('input[name="fullname"]').addEventListener('input', function() {
            const fullname = this.value;
            const username = fullname.toLowerCase()
                .replace(/[^a-z0-9]/g, '')
                .substring(0, 20);
            document.querySelector('input[name="username"]').value = username;
        });
    </script>
</body>

</html>