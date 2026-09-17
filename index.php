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
    <script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
    <script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "surface-variant": "#e1e2e4",
                    "primary": "#00288e",
                    "primary-container": "#1e40af",
                    "primary-fixed": "#dce1ff",
                    "on-primary": "#ffffff",
                    "secondary": "#525c87",
                    "on-secondary": "#ffffff",
                    "tertiary": "#6b538c",
                    "on-tertiary": "#ffffff",
                    "background": "#f8f9fb",
                    "on-background": "#191c1e",
                    "surface": "#f8f9fb",
                    "on-surface": "#191c1e",
                    "on-surface-variant": "#44474f",
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <style>
    /* ── Highlight flash ── */
    .highlight {
        animation: highlightFade 2s forwards;
    }

    @keyframes highlightFade {
        from {
            box-shadow: 0 0 0 4px rgba(0, 120, 255, 0.5);
        }

        to {
            box-shadow: none;
        }
    }

    /* ── Hero text entrance ── */
    @keyframes heroSlideUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-title {
        animation: heroSlideUp .9s ease both;
    }

    .hero-sub {
        animation: heroSlideUp .9s .2s ease both;
    }

    .hero-cta {
        animation: heroSlideUp .9s .4s ease both;
    }

    /* ── Floating blobs in hero ── */
    @keyframes floatBlob {

        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-24px) scale(1.04);
        }
    }

    .blob {
        animation: floatBlob 7s ease-in-out infinite;
    }

    .blob-2 {
        animation: floatBlob 9s 2s ease-in-out infinite;
    }

    .blob-3 {
        animation: floatBlob 11s 4s ease-in-out infinite;
    }

    /* ── Pulse ring on profile icon ── */
    @keyframes pulseRing {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 40, 142, .45);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(0, 40, 142, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(0, 40, 142, 0);
        }
    }

    #profile-icon {
        animation: pulseRing 2.4s ease-out infinite;
        cursor: pointer;
    }

    /* ── Shimmer on partner logos ── */
    @keyframes shimmer {
        0% {
            background-position: -400px 0;
        }

        100% {
            background-position: 400px 0;
        }
    }

    /* ── Gradient text ── */
    .grad-text {
        background: linear-gradient(135deg, #00288e 0%, #6b538c 60%, #525c87 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* ── Card tilt on hover ── */
    .tilt-card {
        transition: transform .35s cubic-bezier(.25, .46, .45, .94), box-shadow .35s ease;
    }

    .tilt-card:hover {
        transform: translateY(-6px) rotate(-0.5deg);
        box-shadow: 0 20px 60px rgba(0, 40, 142, .12);
    }

    /* ── Step icon bounce ── */
    @keyframes iconBounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-6px);
        }
    }

    .step-card:hover .step-icon {
        animation: iconBounce .6s ease;
    }

    /* ── Wave SVG divider ── */
    .wave-divider svg {
        display: block;
    }

    /* ── Scroll indicator bounce ── */
    @keyframes scrollBounce {

        0%,
        100% {
            transform: translateY(0);
            opacity: .8;
        }

        50% {
            transform: translateY(8px);
            opacity: 1;
        }
    }

    .scroll-indicator {
        animation: scrollBounce 1.8s ease-in-out infinite;
    }

    /* ── Gradient section backgrounds ── */
    .grad-section-blue {
        background: linear-gradient(160deg, #eef1ff 0%, #f8f9fb 60%);
    }

    .grad-section-purple {
        background: linear-gradient(160deg, #f3eeff 0%, #f8f9fb 70%);
    }

    .grad-section-dark {
        background: linear-gradient(135deg, #00288e 0%, #1e40af 50%, #6b538c 100%);
    }

    /* ── Glow badge ── */
    .glow-badge {
        box-shadow: 0 0 0 4px rgba(0, 40, 142, .08), 0 2px 12px rgba(0, 40, 142, .15);
    }

    /* ── Footer gradient ── */
    footer {
        background: linear-gradient(160deg, #1a1e2e 0%, #0d1422 100%) !important;
    }

    footer,
    footer a,
    footer p,
    footer h4,
    footer span {
        color: #c5c6d0 !important;
    }

    footer a:hover {
        color: #a8b4ff !important;
    }

    footer .text-primary {
        color: #a8b4ff !important;
    }

    footer .border-outline-variant\/15 {
        border-color: rgba(255, 255, 255, .08) !important;
    }
    </style>
</head>

<body class="bg-surface font-body text-on-surface">

    <header class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,14,83,0.04)]">
        <div class="h-16 max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[20px]">account_balance</span>
                </div>
                <span class="text-lg font-headline font-bold tracking-tight text-primary">Youth Profiling System</span>
            </div>
            <nav class="hidden md:flex items-center gap-10">
                <a href="index.php" class="transition-colors text-primary font-semibold">Home</a>
                <a href="pages/about.php"
                    class="text-sm text-on-surface-variant hover:text-on-surface transition-colors">About</a>
            </nav>
            <div class="flex items-center gap-4">
                <div id="profile-icon" class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
                </div>
            </div>
        </div>
    </header>

    <main class="w-full pt-16">
        <!-- HERO SECTION -->
        <section
            class="relative w-full h-[600px] lg:h-[700px] flex items-center justify-center -mt-16 pt-16 overflow-hidden">
            <!-- Background image -->
            <img src="assets/images/hero_background.jpg" alt="Filipino youth skills training - TESDA"
                class="absolute inset-0 w-full h-full object-cover z-0" />
            <!-- Gradient overlay: dark bottom + blue tint -->
            <div class="absolute inset-0 z-10"
                style="background:linear-gradient(to bottom, rgba(0,20,70,.45) 0%, rgba(0,10,40,.65) 100%);"></div>

            <!-- Floating decorative blobs -->
            <div class="blob absolute top-12 left-10 w-56 h-56 rounded-full z-10 pointer-events-none"
                style="background:radial-gradient(circle,rgba(107,83,140,.35) 0%,transparent 70%);"></div>
            <div class="blob-2 absolute bottom-16 right-12 w-72 h-72 rounded-full z-10 pointer-events-none"
                style="background:radial-gradient(circle,rgba(0,40,142,.3) 0%,transparent 70%);"></div>
            <div class="blob-3 absolute top-1/3 right-1/4 w-40 h-40 rounded-full z-10 pointer-events-none"
                style="background:radial-gradient(circle,rgba(82,92,135,.25) 0%,transparent 70%);"></div>

            <!-- Hero content -->
            <div class="relative z-20 max-w-7xl mx-auto px-6 lg:px-12 w-full flex flex-col items-center text-center">
                <span
                    class="hero-title inline-block mb-4 px-4 py-1.5 rounded-full text-xs font-label font-bold uppercase tracking-widest glow-badge"
                    style="background:rgba(255,255,255,.12);color:#dce1ff;border:1px solid rgba(255,255,255,.2);">🇵🇭
                    Youth &amp; Skills — Philippines</span>
                <h1
                    class="hero-title font-display font-black text-5xl md:text-6xl lg:text-7xl text-white tracking-tight mb-6 max-w-4xl drop-shadow-lg leading-tight">
                    Bridge the Gap<br><span
                        style="background:linear-gradient(90deg,#a8b4ff,#d4aaff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">to
                        Your Future</span>
                </h1>
                <p class="hero-sub font-body text-lg md:text-xl max-w-2xl mb-12 font-medium drop-shadow"
                    style="color:rgba(220,225,255,.9);">
                    Connect Filipino youth with verified training, internships, and local job opportunities. Build
                    skills. Find work. Grow together.
                </p>

                <div
                    class="hero-cta flex flex-col sm:flex-row items-center gap-4 w-full max-w-md mx-auto sm:max-w-none sm:justify-center">
                    <a href="pages/youth-login.php"
                        class="w-full sm:w-auto px-8 py-4 rounded-lg shadow-lg font-label font-bold tracking-wide flex items-center justify-center gap-2 group transition-all duration-300 hover:scale-105"
                        style="background:linear-gradient(135deg,#00288e,#3b5bdb);color:#fff;box-shadow:0 8px 32px rgba(0,40,142,.4);">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">school</span>
                        I am a Youth
                    </a>
                    <a href="pages/provider-login.php"
                        class="w-full sm:w-auto px-8 py-4 rounded-lg font-label font-bold tracking-wide flex items-center justify-center gap-2 group transition-all duration-300 hover:scale-105"
                        style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.35);backdrop-filter:blur(8px);">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">domain</span>
                        I am a Provider
                    </a>
                </div>
            </div>

            <!-- Scroll down indicator -->
            <div class="scroll-indicator absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center gap-1"
                style="color:rgba(255,255,255,.6);">
                <span class="text-xs font-label uppercase tracking-widest">Scroll</span>
                <span class="material-symbols-outlined text-2xl">expand_more</span>
            </div>

            <!-- Wave bottom divider -->
            <div class="wave-divider absolute bottom-0 left-0 w-full z-20 pointer-events-none">
                <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
                    style="height:60px;width:100%;">
                    <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#f8f9fb" />
                </svg>
            </div>
        </section>

        <!-- TRUSTED BY SECTION -->
        <section class="w-full py-12 bg-surface border-b border-outline-variant/15">
            <div class="max-w-7xl mx-auto px-6 lg:px-12 text-center">
                <p class="font-label font-bold uppercase tracking-widest text-on-surface-variant text-sm mb-8">
                    Trusted by Community Partners
                </p>
                <div
                    class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
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
        <section class="w-full py-24 grad-section-blue relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full pointer-events-none"
                style="background:radial-gradient(circle,rgba(0,40,142,.06) 0%,transparent 70%);"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full pointer-events-none"
                style="background:radial-gradient(circle,rgba(107,83,140,.06) 0%,transparent 70%);"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="text-primary font-label font-bold uppercase tracking-[0.2em] text-sm">Value
                        Propositions</span>
                    <h2 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mt-3">
                        Why Join the <span class="grad-text">Youth Profiling System?</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Youth Benefits -->
                    <div class="tilt-card bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/15"
                        data-aos="fade-right">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-primary"
                                style="background:linear-gradient(135deg,#eef1ff,#dce1ff);">
                                <span class="material-symbols-outlined">emoji_events</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface">For Youth</h3>
                        </div>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Access Verified
                                        Internships</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Connect with legitimate local
                                        businesses offering real‑world experience.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Build a Professional
                                        Profile</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Create a digital portfolio that
                                        showcases your growing skill set.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Get Matched with Free
                                        Training</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Discover skill‑building programs
                                        tailored to your interests.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Provider Benefits -->
                    <div class="tilt-card bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/15"
                        data-aos="fade-left">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-tertiary"
                                style="background:linear-gradient(135deg,#f3eeff,#e9d8ff);">
                                <span class="material-symbols-outlined">trending_up</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface">For Providers</h3>
                        </div>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Direct Access to Local
                                        Talent</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Reach motivated youth eager to
                                        contribute to your mission.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Verified Skills
                                        Inventory</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Find candidates with the exact
                                        competencies you need.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-tertiary mt-0.5">check_circle</span>
                                <div>
                                    <strong class="text-on-surface block font-headline">Streamlined Recruitment</strong>
                                    <p class="text-on-surface-variant text-sm mt-1">Manage postings, applications, and
                                        communications from a single dashboard.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ROLE‑SPECIFIC CARD SECTION -->
        <section id="paths-section" class="w-full py-24 grad-section-purple relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-full h-full pointer-events-none opacity-30"
                style="background: radial-gradient(circle at 80% 20%, rgba(107,83,140,0.15) 0%, transparent 50%);">
            </div>

            <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10">
                <div class="text-center mb-16" data-aos="fade-up">
                    <span class="text-tertiary font-label font-bold uppercase tracking-[0.2em] text-sm">Join the
                        Network</span>
                    <h2 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mt-3">
                        Paths to <span class="grad-text">Engagement</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Youth Card -->
                    <div class="tilt-card group flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-500 border border-outline-variant/15"
                        data-aos="fade-up" data-aos-delay="100">
                        <div class="w-full h-64 overflow-hidden relative">
                            <div
                                class="absolute inset-0 bg-primary/20 group-hover:bg-primary/0 transition-colors duration-500 z-10">
                            </div>
                            <img alt="Youth collaborating in a barangay community"
                                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"
                                src="assets/images/youth_card.jpg" />
                        </div>
                        <div class="p-8 lg:p-10 flex flex-col flex-grow">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center mb-6 text-primary shadow-sm"
                                style="background:linear-gradient(135deg,#eef1ff,#dce1ff);">
                                <span class="material-symbols-outlined text-2xl">rocket_launch</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface mb-3">Join as a Youth</h3>
                            <p class="font-body text-on-surface-variant leading-relaxed mb-8 flex-grow">
                                Discover internships, training programs, and community projects tailored for young
                                talent.
                                Take the first step in building your civic profile.
                            </p>
                            <a href="pages/youth-signup.php"
                                class="inline-flex items-center gap-2 text-primary font-label font-bold hover:text-primary-container transition-colors group/link w-max">
                                Start Your Journey
                                <span
                                    class="material-symbols-outlined text-sm group-hover/link:translate-x-2 transition-transform">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Provider Card -->
                    <div class="tilt-card group flex flex-col bg-surface-container-lowest rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-500 border border-outline-variant/15"
                        data-aos="fade-up" data-aos-delay="200">
                        <div class="w-full h-64 overflow-hidden relative">
                            <div
                                class="absolute inset-0 bg-tertiary/20 group-hover:bg-tertiary/0 transition-colors duration-500 z-10">
                            </div>
                            <img alt="Provider networking event"
                                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"
                                src="assets/images/provider_card.jpg" />
                        </div>
                        <div class="p-8 lg:p-10 flex flex-col flex-grow">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center mb-6 text-tertiary shadow-sm"
                                style="background:linear-gradient(135deg,#f3eeff,#e9d8ff);">
                                <span class="material-symbols-outlined text-2xl">handshake</span>
                            </div>
                            <h3 class="font-headline font-bold text-2xl text-on-surface mb-3">Register as a Provider
                            </h3>
                            <p class="font-body text-on-surface-variant leading-relaxed mb-8 flex-grow">
                                Post opportunities, connect with enthusiastic youth, and grow your impact. Build a
                                stronger
                                community by mentoring the next generation.
                            </p>
                            <a href="pages/provider-registration.php"
                                class="inline-flex items-center gap-2 text-tertiary font-label font-bold hover:text-tertiary-container transition-colors group/link w-max">
                                Find Local Talent
                                <span
                                    class="material-symbols-outlined text-sm group-hover/link:translate-x-2 transition-transform">
                                    arrow_forward
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS -->
        <section class="w-full pb-32 pt-24 bg-surface relative">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="text-center mb-20" data-aos="fade-up">
                    <span class="text-primary font-label font-bold uppercase tracking-[0.2em] text-sm">Simple
                        Process</span>
                    <h2 class="font-headline font-bold text-3xl md:text-4xl text-on-surface mt-3">How It Works</h2>
                    <p class="text-on-surface-variant font-body leading-relaxed text-lg mt-4 max-w-2xl mx-auto">
                        Getting started is easy. Follow these steps to connect with opportunities across the
                        Philippines.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                    <!-- Connector line (desktop only) -->
                    <div
                        class="hidden md:block absolute top-12 left-[16.66%] right-[16.66%] h-1 bg-gradient-to-r from-primary via-tertiary to-secondary opacity-30 rounded-full">
                    </div>

                    <!-- Step 1 -->
                    <div class="step-card flex flex-col items-center text-center p-8 bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-outline-variant/15 relative z-10"
                        data-aos="fade-up" data-aos-delay="100">
                        <div
                            class="step-icon w-20 h-20 rounded-2xl bg-primary flex items-center justify-center mb-6 shadow-lg transform rotate-3 transition-transform">
                            <span class="material-symbols-outlined text-on-primary text-4xl">person_add</span>
                        </div>
                        <span
                            class="absolute top-6 right-8 font-display font-black text-6xl text-primary/5 leading-none select-none pointer-events-none">01</span>
                        <h3 class="font-headline font-bold text-xl text-on-surface mb-3">Create Your Profile</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">
                            Register as a youth or provider. Fill in your skills, interests, and goals to get matched
                            with the right opportunities.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-card flex flex-col items-center text-center p-8 bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-outline-variant/15 relative z-10"
                        data-aos="fade-up" data-aos-delay="200">
                        <div
                            class="step-icon w-20 h-20 rounded-2xl bg-tertiary flex items-center justify-center mb-6 shadow-lg transform -rotate-3 transition-transform">
                            <span class="material-symbols-outlined text-on-primary text-4xl">search</span>
                        </div>
                        <span
                            class="absolute top-6 right-8 font-display font-black text-6xl text-tertiary/5 leading-none select-none pointer-events-none">02</span>
                        <h3 class="font-headline font-bold text-xl text-on-surface mb-3">Discover Opportunities</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">
                            Browse verified training programs, internships, and livelihood projects available in your
                            local community.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="step-card flex flex-col items-center text-center p-8 bg-surface-container-lowest rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-outline-variant/15 relative z-10"
                        data-aos="fade-up" data-aos-delay="300">
                        <div
                            class="step-icon w-20 h-20 rounded-2xl bg-secondary flex items-center justify-center mb-6 shadow-lg transform rotate-3 transition-transform">
                            <span class="material-symbols-outlined text-on-primary text-4xl">handshake</span>
                        </div>
                        <span
                            class="absolute top-6 right-8 font-display font-black text-6xl text-secondary/5 leading-none select-none pointer-events-none">03</span>
                        <h3 class="font-headline font-bold text-xl text-on-surface mb-3">Connect &amp; Grow</h3>
                        <p class="text-on-surface-variant text-sm leading-relaxed">
                            Apply directly, communicate with providers, and build your future — all through one
                            transparent platform.
                        </p>
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
                        <span class="text-lg font-headline font-bold tracking-tight text-primary">Youth Profiling
                            System</span>
                    </div>
                    <p class="text-sm text-on-surface-variant leading-relaxed max-w-xs">
                        Empowering the next generation through digital transformation and community engagement.
                    </p>
                </div>

                <!-- Navigation -->
                <div class="space-y-4">
                    <h4 class="text-xs font-label font-bold uppercase tracking-widest text-on-surface-variant">
                        Navigation</h4>
                    <nav class="flex flex-col gap-2">
                        <a class="text-sm text-on-surface hover:text-primary transition-colors"
                            href="index.php">Home</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors"
                            href="pages/about.php">About</a>
                    </nav>
                </div>

                <!-- Support -->
                <div class="space-y-4">
                    <h4 class="text-xs font-label font-bold uppercase tracking-widest text-on-surface-variant">Support
                    </h4>
                    <nav class="flex flex-col gap-2">
                        <a class="text-sm text-on-surface hover:text-primary transition-colors" href="#">Contact
                            Support</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors"
                            href="pages/privacy-policy.php">Privacy Policy</a>
                        <a class="text-sm text-on-surface hover:text-primary transition-colors"
                            href="pages/terms-of-service.php">Terms of Service</a>
                    </nav>
                </div>
            </div>

            <div
                class="pt-8 border-t border-outline-variant/15 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-label text-on-surface-variant">
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
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true
        });
    }
    // Smooth scroll to Paths to Engagement section when profile icon is clicked
    const profileIcon = document.getElementById('profile-icon');
    const pathsSection = document.getElementById('paths-section');
    if (profileIcon && pathsSection) {
        profileIcon.addEventListener('click', function(e) {
            e.preventDefault();
            pathsSection.scrollIntoView({
                behavior: 'smooth'
            });
            pathsSection.classList.add('highlight');
            setTimeout(() => pathsSection.classList.remove('highlight'), 2000);
        });
    }
    </script>
</body>

</html>