<?php
$pageTitle = 'About Us';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
        }
        ::-webkit-scrollbar { display: none; }

        /* ── Hover animations & Gradients (Copied from index) ── */
        .grad-text {
            background: linear-gradient(135deg, #00288e 0%, #6b538c 60%, #525c87 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tilt-card { transition: transform .35s cubic-bezier(.25,.46,.45,.94), box-shadow .35s ease; }
        .tilt-card:hover { transform: translateY(-6px) rotate(-0.5deg); box-shadow: 0 20px 60px rgba(0,40,142,.12); }
        @keyframes floatBlob {
            0%,100% { transform: translateY(0) scale(1); }
            50%      { transform: translateY(-24px) scale(1.04); }
        }
        .blob { animation: floatBlob 7s ease-in-out infinite; }
        .blob-2 { animation: floatBlob 9s 2s ease-in-out infinite; }
        
        /* ── Footer gradient ── */
        footer { background: linear-gradient(160deg, #1a1e2e 0%, #0d1422 100%) !important; }
        footer, footer a, footer p, footer h4, footer span { color: #c5c6d0 !important; }
        footer a:hover { color: #a8b4ff !important; }
        footer .text-primary { color: #a8b4ff !important; }
        footer .border-outline-variant\/15 { border-color: rgba(255,255,255,.08) !important; }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-variant": "#e1e2e4",
                        "primary": "#00288e",
                        "primary-container": "#1e40af",
                        "on-primary": "#ffffff",
                        "secondary": "#525c87",
                        "on-secondary": "#ffffff",
                        "background": "#f8f9fb",
                        "surface": "#f8f9fb",
                        "on-surface": "#191c1e",
                        "surface-container-low": "#f3f4f6",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#edeef0",
                        "surface-container-high": "#e7e8ea",
                        "surface-container-highest": "#e1e2e4",
                        "surface-bright": "#f8f9fb",
                        "outline": "#757684",
                        "outline-variant": "#c5c6d0",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",
                        "tertiary": "#6b538c",
                        "on-surface-variant": "#44474f",
                        "on-background": "#191c1e"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    fontFamily: {
                        headline: ["Inter"],
                        display: ["Inter"],
                        body: ["Inter"],
                        label: ["Inter"]
                    }
                }
            }
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <title><?= htmlspecialchars($pageTitle); ?> - Youth Profiling System</title>
</head>

<body class="bg-surface font-body text-on-surface">

    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,14,83,0.04)]">
        <div class="h-16 max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[20px]">account_balance</span>
                </div>
                <span class="text-lg font-headline font-bold tracking-tight text-primary">Youth Profiling System</span>
            </div>
            <nav class="hidden md:flex items-center gap-10">
                <a href="../index.php" class="text-sm text-on-surface-variant hover:text-on-surface transition-colors">Home</a>
                <a href="about.php" class="transition-colors text-primary font-semibold">About</a>
            </nav>
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
            </div>
        </div>
    </header>

    <div class="pt-16"></div>
    
    <!-- Hero / Header Section for About -->
    <section class="relative w-full py-24 lg:py-32 overflow-hidden bg-surface-container-low" style="background: linear-gradient(160deg, #f8f9fb 0%, #eef1ff 100%);">
        <!-- Floating decorative blobs -->
        <div class="blob absolute top-0 right-10 w-64 h-64 rounded-full z-0 pointer-events-none" style="background:radial-gradient(circle,rgba(0,40,142,.08) 0%,transparent 70%);"></div>
        <div class="blob-2 absolute bottom-10 left-10 w-72 h-72 rounded-full z-0 pointer-events-none" style="background:radial-gradient(circle,rgba(107,83,140,.06) 0%,transparent 70%);"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <p class="mb-4 text-sm font-label font-bold uppercase tracking-[0.2em] text-primary" data-aos="fade-up">About the platform</p>
            <h1 class="mb-6 text-4xl lg:text-5xl lg:leading-tight font-display font-extrabold tracking-tight text-on-surface" data-aos="fade-up" data-aos-delay="100">
                Connecting young people with a <span class="grad-text">clearer next step.</span>
            </h1>
            <p class="text-xl leading-relaxed text-on-surface-variant max-w-2xl mx-auto font-medium" data-aos="fade-up" data-aos-delay="200">
                The Youth Profiling System helps local communities understand the skills, interests, and goals of out-of-school youth in the Philippines.
            </p>
        </div>
    </section>

    <main class="w-full bg-surface pb-24">
        <div class="max-w-4xl mx-auto px-6 relative -mt-12 z-20">
            <!-- Content Block -->
            <div class="bg-surface-container-lowest p-8 lg:p-12 rounded-2xl shadow-xl border border-outline-variant/15 space-y-6 text-lg leading-relaxed text-on-surface-variant" data-aos="fade-up" data-aos-delay="300">
                <p>
                    Young people can build a profile and discover relevant training (like TESDA), internships, and community opportunities tailored specifically to their location and career aspirations.
                </p>
                <p>
                    Local providers, businesses, and organizations can share programs and connect directly with motivated local talent, ensuring that opportunities reach those who need them most.
                </p>
                <p class="font-headline font-semibold text-primary">
                    Our goal is simple: make support easier to find, make skills more visible, and help communities create better pathways into learning and work.
                </p>
            </div>
            
            <!-- Value Prop Cards -->
            <section class="mt-16 grid gap-8 md:grid-cols-3">
                <div class="tilt-card border-t-4 border-primary bg-surface-container-lowest p-8 shadow-sm rounded-xl" data-aos="fade-up">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-primary mb-4" style="background:linear-gradient(135deg,#eef1ff,#dce1ff);">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <h2 class="mb-2 font-headline font-bold text-xl text-on-surface">Youth first</h2>
                    <p class="text-sm leading-relaxed text-on-surface-variant">Profiles and pathways designed around real goals, prioritizing the needs of young job-seekers.</p>
                </div>
                <div class="tilt-card border-t-4 border-tertiary bg-surface-container-lowest p-8 shadow-sm rounded-xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-tertiary mb-4" style="background:linear-gradient(135deg,#f3eeff,#e9d8ff);">
                        <span class="material-symbols-outlined">map</span>
                    </div>
                    <h2 class="mb-2 font-headline font-bold text-xl text-on-surface">Local connection</h2>
                    <p class="text-sm leading-relaxed text-on-surface-variant">Community providers and localized training programs aggregated in one accessible place.</p>
                </div>
                <div class="tilt-card border-t-4 border-secondary bg-surface-container-lowest p-8 shadow-sm rounded-xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-secondary mb-4" style="background:linear-gradient(135deg,#e8eaf6,#c5cae9);">
                        <span class="material-symbols-outlined">trending_up</span>
                    </div>
                    <h2 class="mb-2 font-headline font-bold text-xl text-on-surface">Practical progress</h2>
                    <p class="text-sm leading-relaxed text-on-surface-variant">Smart skills matching that turns user information into concrete, actionable next steps.</p>
                </div>
            </section>
        </div>
    </main>

    <footer class="w-full bg-surface-container-low pt-16 pb-12 mt-auto">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                <!-- Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-headline font-bold tracking-tight text-primary">Youth Profiling System</span>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed max-w-xs">
                        Empowering the next generation through digital transformation and community engagement.
                    </p>
                </div>

                <!-- Navigation -->
                <div class="space-y-4">
                    <h4 class="text-xs font-label font-bold uppercase tracking-widest text-on-surface-variant">Navigation</h4>
                    <nav class="flex flex-col gap-2">
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="../index.php">Home</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="about.php">About</a>
                    </nav>
                </div>

                <!-- Support -->
                <div class="space-y-4">
                    <h4 class="text-xs font-label font-bold uppercase tracking-widest text-on-surface-variant">Support</h4>
                    <nav class="flex flex-col gap-2">
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="#">Contact Support</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="privacy-policy.php">Privacy Policy</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="terms-of-service.php">Terms of Service</a>
                    </nav>
                </div>
            </div>

            <div class="pt-8 border-t border-outline-variant/15 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-label text-on-surface-variant">
                <p>© <?= date('Y'); ?> Youth Profiling System.</p>
                <div class="flex gap-6">
                    <a class="hover:text-primary" href="#">Facebook</a>
                    <a class="hover:text-primary" href="#">Twitter</a>
                    <a class="hover:text-primary" href="#">Instagram</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        if (typeof AOS !== 'undefined') {
            AOS.init({ once: true });
        }
    </script>
</body>

</html>