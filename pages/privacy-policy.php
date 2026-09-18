<?php
$pageTitle = 'Privacy Policy';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title><?= htmlspecialchars($pageTitle); ?> - Youth Profiling System</title>
    <style>
    .skeleton-pulse{background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);background-size:200% 100%;animation:skeleton-shimmer 1.4s ease-in-out infinite;display:block;}
    @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
    </style>
</head>

<body class="bg-slate-50 text-slate-900 font-['Inter']">
    <div id="skeleton-loader-pp" style="padding:4rem 2rem;max-width:56rem;margin:0 auto">
        <div class="skeleton-pulse" style="height:1rem;width:30%;margin-bottom:1rem;border-radius:6px"></div>
        <div class="skeleton-pulse" style="height:2.5rem;width:60%;margin-bottom:1rem;border-radius:6px"></div>
        <div class="skeleton-pulse" style="height:1rem;width:25%;margin-bottom:2rem;border-radius:6px"></div>
        <div class="skeleton-pulse" style="height:1rem;width:80%;margin-bottom:.5rem;border-radius:6px"></div>
        <div class="skeleton-pulse" style="height:1rem;width:65%;border-radius:6px"></div>
    </div>
    <div id="real-content-pp" style="display:none">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5"><a href="../index.php" class="flex items-center gap-3 font-bold text-blue-900"><span class="material-symbols-outlined">account_balance</span>Youth Profiling System</a><a href="../index.php" class="text-sm font-semibold text-blue-900 hover:text-blue-700">Back to Home</a></div>
    </header>
    <main class="mx-auto max-w-4xl px-6 py-16">
        <p class="mb-3 text-sm font-bold uppercase tracking-[0.2em] text-blue-700">Your information</p>
        <h1 class="mb-4 text-4xl font-extrabold tracking-tight">Privacy Policy</h1>
        <p class="mb-10 text-sm text-slate-500">Last updated: August 20, 2026</p>
        <div class="space-y-8 leading-7 text-slate-600">
            <section>
                <h2 class="mb-2 text-xl font-bold text-slate-900">Information we collect</h2>
                <p>We collect the information you provide when creating a profile, registering as a provider, applying to an opportunity, or contacting the system team. This may include contact details, education, skills, interests, and application information.</p>
            </section>
            <section>
                <h2 class="mb-2 text-xl font-bold text-slate-900">How we use information</h2>
                <p>Information is used to maintain profiles, provide skills matching, coordinate training and opportunities, communicate with users, and produce aggregated community reports. We do not sell personal information.</p>
            </section>
            <section>
                <h2 class="mb-2 text-xl font-bold text-slate-900">Access and protection</h2>
                <p>Access is limited according to account role. We use account controls and reasonable technical and administrative safeguards to protect stored information. No online service can guarantee absolute security.</p>
            </section>
            <section>
                <h2 class="mb-2 text-xl font-bold text-slate-900">Your choices</h2>
                <p>You may request corrections to your information or ask questions about its use by contacting the system administrator through the support channel provided by your local office.</p>
            </section>
        </div>
    </main>
    </div>
    <script>
    (function(){
        var sk = document.getElementById('skeleton-loader-pp');
        var rc = document.getElementById('real-content-pp');
        if(sk) sk.style.display = 'none';
        if(rc) rc.style.display = '';
    })();
    </script>
</body>

</html>