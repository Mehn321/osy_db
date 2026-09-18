<?php
$pageTitle = 'Provider Registration';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';

$userModel = new User($database);
$message = '';
$messageType = 'success';
$errors = [];

// Initialize variables so form doesn't crash on initial GET
$providerType = 'employer';
$fullname = '';
$username = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_provider'])) {

    // Unconditionally capture POST data to retain inputs
    $providerType = $_POST['provider_type'] ?? 'employer';
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Prevent duplicate submissions using server-side form nonce
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session security token has expired. Please refresh the page and try again.';
    } elseif (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $errors[] = 'This form has already been submitted or the session expired. Please refresh the page and try again.';
    } else {
        try {
            if (!in_array($providerType, ['employer', 'training_provider'], true)) {
                throw new Exception('Please select a valid provider type.');
            }
            // Validate password fields
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($password)) {
                throw new Exception('Password is required.');
            }
            if (strlen($password) < 12) {
                throw new Exception('Password must be at least 12 characters and include letters, numbers, and symbols.');
            }
            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('A valid email address is required.');
            }
            if ($phone === '') {
                throw new Exception('Phone number is required.');
            }

            $documentPath = null;
            if (!empty($_FILES['provider_document']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if ($_FILES['provider_document']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error uploading proof of legitimacy document.');
                }
                $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                $detectedType = $fileInfo->file($_FILES['provider_document']['tmp_name']);
                if (!in_array($detectedType, $allowedTypes, true)) {
                    throw new Exception('Document must be a PDF, PNG, or JPEG file.');
                }
                if ($_FILES['provider_document']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Document must be smaller than 5MB.');
                }

                $uploadDir = __DIR__ . '/../uploads/providers';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'application/pdf' => 'pdf',
                ];
                $extension = $extensions[$detectedType];
                $filename = 'provider_doc_' . time() . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . '/' . $filename;

                if (!move_uploaded_file($_FILES['provider_document']['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to save the uploaded document.');
                }

                $documentPath = '/uploads/providers/' . $filename;
            } else {
                throw new Exception('Please upload a proof of legitimacy document.');
            }

            $data = [
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'fullname' => $fullname,
                'role' => $providerType,
                'barangay' => $address,
                'provider_type' => $providerType,
                'provider_document_path' => $documentPath
            ];

            $result = $userModel->createUser($data);

            if ($result['success']) {
                $verificationResult = $userModel->beginSignupVerification($result['user_id'], $email, $phone);
                if (!$verificationResult['success']) {
                    throw new Exception($verificationResult['message']);
                }
                header('Location: verify-signup.php');
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
            $messageType = 'error';
            $message = 'There was an issue submitting your registration. Please fix the highlighted errors and try again.';
        }
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
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
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
                        <img src="../assets/images/panaon-kk-youth-registry.jpg" alt="Panaon KK logo" class="w-12 h-12 rounded-lg object-cover">
                        <span class="text-2xl font-bold tracking-tighter">Panaon KK</span>
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

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-sm text-red-800">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="register_provider" value="1">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

                    <!-- Provider Type Selection -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Provider Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="provider_type" value="employer" <?php echo $providerType === 'employer' ? 'checked' : ''; ?> class="w-4 h-4 text-blue-900">
                                <span class="text-sm font-semibold text-slate-900">Employer</span>
                                <span class="text-xs text-slate-500 ml-auto">Post job openings</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="provider_type" value="training_provider" <?php echo $providerType === 'training_provider' ? 'checked' : ''; ?> class="w-4 h-4 text-blue-900">
                                <span class="text-sm font-semibold text-slate-900">Training Provider</span>
                                <span class="text-xs text-slate-500 ml-auto">Offer training programs</span>
                            </label>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Full Name / Company Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Enter your full name or company name">
                    </div>

                    <!-- Username -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Choose a username">
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="your@email.com">
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Mobile Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="09XXXXXXXXX">
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Choose a secure password">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
                        <p class="text-xs text-slate-500">Use at least 12 characters with uppercase, lowercase, numbers, and symbols.</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Confirm your password">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 toggle-password-btn"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
                        <p id="password-match-error" class="text-xs text-red-600 hidden">Passwords do not match.</p>
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required class="w-full bg-slate-100 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900" placeholder="Enter your address">
                    </div>

                    <!-- Proof of Legitimacy Document -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Proof of Legitimacy</label>
                        <input type="file" name="provider_document" accept="application/pdf,image/png,image/jpeg" required class="w-full text-sm text-slate-900">
                        <p class="text-xs text-slate-500">Upload a business permit, training certification, or other valid document (PDF, JPEG, PNG).</p>
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

        // Real-time password matching validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordMatchError = document.getElementById('password-match-error');

        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                passwordMatchError.classList.remove('hidden');
            } else {
                passwordMatchError.classList.add('hidden');
            }
        });

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