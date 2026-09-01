<?php
/**
 * This generic login page has been retired.
 * Each user type has its own dedicated login page.
 * Redirect visitors to the default (youth) login page.
 */
header('HTTP/1.1 301 Moved Permanently');
header('Location: youth-login.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Youth Profiling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">
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
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-cyan-100 rounded-full blur-3xl opacity-50">
        </div>

        <div
            class="w-full max-w-[1100px] grid md:grid-cols-2 bg-white rounded-xl overflow-hidden shadow-2xl shadow-blue-200 border border-slate-200">
            <!-- Left Side: Branding -->
            <div
                class="hidden md:flex flex-col justify-between p-12 bg-gradient-to-br from-blue-900 to-blue-800 relative overflow-hidden text-white">
                <div
                    class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2220%22 cy=%2220%22 r=%225%22 fill=%22white%22/></svg>')]">
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-8">
                        <span class="material-symbols-outlined text-4xl">groups</span>
                        <span class="text-2xl font-bold tracking-tighter">Youth Profiling</span>
                    </div>
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight mb-4">
                        Opportunities & Skills<br />for Our Youth.
                    </h1>
                    <p class="text-blue-100 text-lg max-w-sm font-medium">
                        Securely connect with employment opportunities, training programs, and community support
                        services.
                    </p>
                </div>
                <div class="relative z-10">
                    <div
                        class="flex items-center gap-4 p-4 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md">
                        <div class="w-12 h-12 rounded-full bg-green-400 flex items-center justify-center text-blue-900">
                            <span class="material-symbols-outlined">security</span>
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Secure Access</p>
                            <p class="text-blue-100 text-xs">Your data is protected by industry-standard protocols.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="p-8 md:p-16 flex flex-col justify-center">
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Welcome Back</h2>
                    <p class="text-slate-600 font-medium">Enter your credentials to access the portal.</p>
                </div>

                <?php if ($login_error): ?>
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm font-medium">
                        <span class="material-symbols-outlined text-base align-middle mr-2">error</span>
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">

                    <!-- Username Input -->
                    <div class="space-y-2">
                        <label
                            class="block text-xs font-bold uppercase tracking-widest text-slate-600 ml-1">Username</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-slate-400 text-xl">person</span>
                            </div>
                            <input type="text" name="username" placeholder="admin1" required
                                class="block w-full pl-12 pr-4 py-3 bg-slate-100 border border-transparent focus:border-blue-900 focus:ring-0 rounded-lg transition-all text-slate-900 placeholder:text-slate-400 font-medium" />
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label
                                class="block text-xs font-bold uppercase tracking-widest text-slate-600">Password</label>
                            <a href="forgot-password.php"
                                class="text-xs font-bold text-blue-900 hover:underline uppercase tracking-widest">Forgot?</a>
                        </div>
                        <div class="relative">
                            <input type="password" name="password" placeholder="••••••••" required
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700 toggle-password-btn">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-900 to-blue-800 text-white py-2 px-4 text-sm rounded-md font-medium shadow-md hover:shadow-lg transition-all active:scale-[0.98] mt-4">
                        <span class="flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">login</span>
                            Sign In
                        </span>
                    </button>
                </form>

                <!-- Youth & Provider Registration Links -->
                <div class="mt-8 space-y-3 text-center border-t border-slate-200 pt-8">
                    <div>
                        <p class="text-sm text-slate-600 mb-2">Are you a youth looking for opportunities?</p>
                        <a href="youth-signup.php"
                            class="inline-flex items-center gap-2 text-blue-900 font-semibold hover:underline">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            Youth Sign Up
                        </a>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600 mb-2">Are you an employer or training provider?</p>
                        <a href="provider-registration.php"
                            class="inline-flex items-center gap-2 text-blue-900 font-semibold hover:underline">
                            <span class="material-symbols-outlined text-base">business</span>
                            Register as Provider
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize password visibility toggles for login page
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('button.toggle-password-btn').forEach(btn => {
                if (btn.dataset.bound) return;
                btn.dataset.bound = '1';
                const container = btn.closest('div') || btn.parentNode;
                const input = container.querySelector('input[type="password"]');
                if (!input) return;
                if (input.dataset.hasToggle) return;
                input.dataset.hasToggle = '1';
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.innerHTML = '<span class="material-symbols-outlined">visibility_off</span>';
                    } else {
                        input.type = 'password';
                        btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';
                    }
                });
            });
        });
    </script>
</body>

</html>