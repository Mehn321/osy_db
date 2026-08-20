<?php
/*  index.php – Landing page for the Youth Profiling System
    • Shows two role‑specific entry points (Youth & Provider)
    • Uses the same Tailwind theme you already configured
    • All CTA buttons link to the correct login / signup pages
*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Reset / base styles -->
    <style>
        @layer base {

            html,
            body {
                margin: 0;
                padding: 0;
            }

            body {
                overscroll-behavior: none;
            }

            main>:first-child {
                margin-top: 0 !important;
            }

            main>:last-child {
                margin-bottom: 0 !important;
            }
        }

        ::-webkit-scrollbar {
            display: none;
        }
    </style>
    <!-- Tailwind (using the same config you already have) -->
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
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a"
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

    <!-- Material icons & Inter font (same as original) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet" />

    <title>Youth & Provider Hub – Youth Profiling System</title>
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
                <a href="index.php" class="transition-colors text-primary font-semibold">Home</a>
                <a href="pages/about.php" class="text-sm text-on-surface-variant hover:text-on-surface transition-colors">About</a>
            </nav>
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
            </div>
        </div>
    </header>

    <main class="w-full pt-16">
        <!-- HERO SECTION -->
        <section class="relative w-full h-[600px] lg:h-[700px] flex items-center justify-center -mt-16 pt-16 overflow-hidden">
            <div class="absolute inset-0 w-full h-full bg-cover bg-center"
                style="background-image: url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1600&q=80');">
            </div>
            <div class="absolute inset-0 bg-on-background/60 backdrop-blur-[2px]"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12 w-full flex flex-col items-center text-center">
                <h1 class="font-display font-black text-5xl md:text-6xl lg:text-7xl text-on-primary tracking-tight mb-6 max-w-4xl drop-shadow-lg">
                    Bridge the Gap to Your Future
                </h1>
                <p class="font-body text-lg md:text-xl text-surface-container-highest max-w-2xl mb-12 font-medium">
                    Connect with verified local opportunities. Whether you're a youth looking for internships or a provider seeking talent,
                    our platform empowers both sides to grow together.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-6 w-full max-w-md mx-auto sm:max-w-none sm:justify-center">
                    <a href="pages/youth-login.php"
                        class="w-full sm:w-auto px-8 py-4 bg-gradient-to-br from-primary to-primary-container text-on-primary rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 font-label font-bold tracking-wide flex items-center justify-center gap-2 group">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">school</span>
                        I am a Youth
                    </a>

                    <a href="pages/provider-login.php"
                        class="w-full sm:w-auto px-8 py-4 bg-surface-container-lowest text-primary hover:bg-surface-container-low rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 font-label font-bold tracking-wide flex items-center justify-center gap-2 group border border-outline-variant/20">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">domain</span>
                        I am a Provider
                    </a>
                </div>
            </div>
        </section>

        <!-- TRUSTED BY SECTION -->
        <section class="w-full py-12 bg-surface border-b border-outline-variant/15">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 text-center">
                <p class="font-label font-bold uppercase tracking-widest text-on-surface-variant text-sm mb-8">
                    Trusted by Community Partners
                </p>
                <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
                    <div class="flex items-center gap-2 text-on-surface font-headline font-bold text-xl">
                        <span class="material-symbols-outlined text-3xl">account_balance</span> Barangay Council
                    </div>
                    <div class="flex items-center gap-2 text-on-surface font-headline font-bold text-xl">
                        <span class="material-symbols-outlined text-3xl">school</span> TESDA
                    </div>
                    <div class="flex items-center gap-2 text-on-surface font-headline font-bold text-xl">
                        <span class="material-symbols-outlined text-3xl">storefront</span> Local Businesses
                    </div>
                    <div class="flex items-center gap-2 text-on-surface font-headline font-bold text-xl">
                        <span class="material-symbols-outlined text-3xl">groups</span> Youth Council
                    </div>
                </div>
            </div>
        </section>

        <!-- WHY JOIN SECTION -->
        <section class="w-full py-24 bg-surface-container-low relative">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-16">
                    <span class="text-primary font-label font-bold uppercase tracking-[0.2em] text-sm">Value Propositions</span>
                    <h2 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mt-3">
                        Why Join the Youth Profiling System?
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Youth Benefits -->
                    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/15">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined">emoji_events</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface">For Youth</h3>
                        </div>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Access Verified Internships</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Connect with legitimate local businesses offering real‑world experience.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Build a Professional Profile</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Create a digital portfolio that showcases your growing skill set.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Get Matched with Free Training</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Discover skill‑building programs tailored to your interests.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Provider Benefits -->
                    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/15">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-full bg-tertiary/10 flex items-center justify-center text-tertiary">
                                <span class="material-symbols-outlined">trending_up</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface">For Providers</h3>
                        </div>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Direct Access to Local Talent</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Reach motivated youth eager to contribute to your mission.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Verified Skills Inventory</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Find candidates with the exact competencies you need.
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Streamlined Recruitment</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">
                                        Manage postings, applications, and communications from a single dashboard.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ROLE‑SPECIFIC CARD SECTION -->
        <section class="w-full py-24 bg-surface relative">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-16">
                    <span class="text-primary font-label font-bold uppercase tracking-[0.2em] text-sm">Join the Network</span>
                    <h2 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mt-3">
                        Paths to Engagement
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Youth Card -->
                    <div class="group flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-500 border border-outline-variant/15">
                        <div class="w-full h-64 overflow-hidden relative">
                            <div class="absolute inset-0 bg-primary/10 group-hover:bg-transparent transition-colors duration-500 z-10"></div>
                            <img alt="Youth collaborating"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out"
                                src="https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?auto=format&fit=crop&w=800&q=80" />
                        </div>
                        <div class="p-8 lg:p-10 flex flex-col flex-grow">
                            <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center mb-6 text-primary">
                                <span class="material-symbols-outlined">rocket_launch</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface mb-3">Join as a Youth</h3>
                            <p class="font-body text-on-surface-variant leading-relaxed mb-8 flex-grow">
                                Discover internships, training programs, and community projects tailored for young talent.
                                Take the first step in building your civic profile.
                            </p>
                            <a href="pages/youth-signup.php"
                                class="inline-flex items-center gap-2 text-primary font-label font-bold hover:text-primary-container transition-colors group/link w-max">
                                Start Your Journey
                                <span class="material-symbols-outlined text-sm group-hover/link:translate-x-1 transition-transform">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Provider Card -->
                    <div class="group flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-500 border border-outline-variant/15">
                        <div class="w-full h-64 overflow-hidden relative">
                            <div class="absolute inset-0 bg-tertiary/10 group-hover:bg-transparent transition-colors duration-500 z-10"></div>
                            <img alt="Provider networking"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out"
                                src="assets/provider-networking.svg" />
                        </div>
                        <div class="p-8 lg:p-10 flex flex-col flex-grow">
                            <div class="w-12 h-12 rounded-full bg-tertiary-fixed flex items-center justify-center mb-6 text-tertiary">
                                <span class="material-symbols-outlined">handshake</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface mb-3">Register as a Provider</h3>
                            <p class="font-body text-on-surface-variant leading-relaxed mb-8 flex-grow">
                                Post opportunities, connect with enthusiastic youth, and grow your impact. Build a stronger
                                community by mentoring the next generation.
                            </p>
                            <a href="pages/provider-registration.php"
                                class="inline-flex items-center gap-2 text-tertiary font-label font-bold hover:text-tertiary-container transition-colors group/link w-max">
                                Find Local Talent
                                <span class="material-symbols-outlined text-sm group-hover/link:translate-x-1 transition-transform">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- COMMUNITY PULSE / STATISTICS -->
        <section class="w-full pb-24 bg-surface">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="bg-surface-container-lowest rounded-xl p-8 lg:p-12 shadow-sm border border-outline-variant/15 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>

                    <div class="max-w-xl relative z-10">
                        <h3 class="font-headline font-bold text-2xl md:text-3xl text-on-surface mb-4">
                            Community Pulse
                        </h3>
                        <p class="text-on-surface-variant font-body leading-relaxed text-lg">
                            Join a thriving network of ambitious youth and dedicated organisations working together
                            to build a stronger, more transparent local economy.
                        </p>
                    </div>

                    <div class="flex gap-8 relative z-10">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-outlined text-primary text-3xl">groups</span>
                                <span class="font-display font-black text-4xl text-primary tracking-tighter">2,400+</span>
                            </div>
                            <span class="font-label text-xs uppercase tracking-widest text-on-surface-variant">
                                Active Youth
                            </span>
                        </div>
                        <div class="w-px bg-outline-variant/30 hidden md:block"></div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="material-symbols-outlined text-tertiary text-3xl">domain</span>
                                <span class="font-display font-black text-4xl text-tertiary tracking-tighter">150+</span>
                            </div>
                            <span class="font-label text-xs uppercase tracking-widest text-on-surface-variant">
                                Providers
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="w-full bg-surface-container-low pt-16 pb-12 mt-20">
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
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="index.php">Home</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="pages/about.php">About</a>
                    </nav>
                </div>

                <!-- Support -->
                <div class="space-y-4">
                    <h4 class="text-xs font-label font-bold uppercase tracking-widest text-on-surface-variant">Support</h4>
                    <nav class="flex flex-col gap-2">
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="#">Contact Support</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="pages/privacy-policy.php">Privacy Policy</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="pages/terms-of-service.php">Terms of Service</a>
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

</body>

</html>