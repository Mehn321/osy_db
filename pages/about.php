<?php
$pageTitle = 'About Us';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title><?= htmlspecialchars($pageTitle); ?> - Youth Profiling System</title>
</head>

<body class="bg-slate-50 text-slate-900 font-['Inter']">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="../index.php" class="flex items-center gap-3 font-bold text-blue-900"><span class="material-symbols-outlined">account_balance</span>Youth Profiling System</a>
            <a href="../index.php" class="text-sm font-semibold text-blue-900 hover:text-blue-700">Back to Home</a>
        </div>
    </header>
    <main class="mx-auto max-w-4xl px-6 py-16">
        <p class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-blue-700">About the platform</p>
        <h1 class="mb-6 text-4xl font-extrabold tracking-tight md:text-5xl">Connecting young people with a clearer next step.</h1>
        <div class="space-y-6 text-lg leading-8 text-slate-600">
            <p>The Youth Profiling System helps local communities understand the skills, interests, and goals of out-of-school youth.</p>
            <p>Young people can build a profile and discover relevant training, internships, and community opportunities. Providers can share programs and connect with motivated local talent.</p>
            <p>Our goal is simple: make support easier to find, make skills more visible, and help communities create better pathways into learning and work.</p>
        </div>
        <section class="mt-12 grid gap-6 md:grid-cols-3">
            <div class="border-t-4 border-blue-700 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-bold">Youth first</h2>
                <p class="text-sm leading-6 text-slate-600">Profiles and pathways designed around real goals.</p>
            </div>
            <div class="border-t-4 border-orange-500 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-bold">Local connection</h2>
                <p class="text-sm leading-6 text-slate-600">Community providers and programs in one place.</p>
            </div>
            <div class="border-t-4 border-emerald-600 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-bold">Practical progress</h2>
                <p class="text-sm leading-6 text-slate-600">Skills matching that turns information into action.</p>
            </div>
        </section>
    </main>
</body>

</html>