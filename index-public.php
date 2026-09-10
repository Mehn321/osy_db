<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youth Profiling System - Youth Skills Matching & Employment Opportunities</title>
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="assets/css/design-system.css" rel="stylesheet">
</head>

<body class="bg-white font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-900 flex items-center justify-center text-white">
                    <span class="material-symbols-outlined">account_balance</span>
                </div>
                <span class="text-xl font-bold text-blue-900">Municipal KK</span>
            </div>
            <a href="pages/login.php" class="px-6 py-2 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors">
                Login
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 text-white py-20 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl font-bold mb-6 leading-tight">
                        Empowering Out-of-School Youth Through Technology
                    </h1>
                    <p class="text-xl text-blue-100 mb-8">
                        Connect youth with meaningful opportunities through AI-powered skills matching and employment pathways.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="pages/login.php" class="px-8 py-3 bg-white text-blue-900 rounded-lg font-bold hover:bg-blue-50 transition-colors inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">login</span>
                            Get Started
                        </a>
                        <button class="px-8 py-3 border-2 border-white text-white rounded-lg font-bold hover:bg-blue-900/20 transition-colors inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">info</span>
                            Learn More
                        </button>
                    </div>
                </div>
                <div class="hidden lg:flex items-center justify-center">
                    <div class="w-80 h-80 rounded-full bg-blue-400/20 flex items-center justify-center">
                        <div class="w-64 h-64 rounded-full bg-blue-500/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-8xl text-blue-200">psychology</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-4xl font-bold text-center mb-12">Key Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-xl">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Profile Management</h3>
                    <p class="text-slate-600">Comprehensive OSY profile data management with skills tracking and education history.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-xl">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Smart Matching</h3>
                    <p class="text-slate-600">AI-powered algorithm to match youth skills with available opportunities and training programs.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-xl">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Analytics & Reports</h3>
                    <p class="text-slate-600">Real-time dashboards and comprehensive reports for data-driven decision making.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="bg-slate-100 py-16 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-black text-blue-900 mb-2">12</div>
                    <p class="text-slate-600">OSY Registered</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-green-900 mb-2">8</div>
                    <p class="text-slate-600">Active Opportunities</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-purple-900 mb-2">15</div>
                    <p class="text-slate-600">Successful Matches</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-orange-900 mb-2">86%</div>
                    <p class="text-slate-600">Employment Success</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-16 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-6">Ready to Transform Youth Employment?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Join us in building a more connected and skilled workforce for the community.
            </p>
            <a href="pages/login.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-900 rounded-lg font-bold text-lg hover:bg-blue-50 transition-colors">
                <span class="material-symbols-outlined">arrow_forward</span>
                Access the Platform
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="font-bold text-white mb-4">Municipal KK</h4>
                    <p class="text-sm">Empowering out-of-school youth through skills matching and employment pathways.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Security</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Privacy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-sm">
                <p>&copy; 2026 Municipal KK. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
