<?php require_once __DIR__ . '/../init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Integrated Web Based Information System for Youth Profiling and Skills Matching' : 'Integrated Web Based Information System for Youth Profiling and Skills Matching'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .sidebar-active {
            background-color: #ffffff;
            color: #1d4ed8;
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.1);
        }

        .sidebar-inactive {
            color: #64748b;
        }

        .sidebar-inactive:hover {
            background-color: #f8fafc;
        }

        @media (prefers-color-scheme: dark) {
            .sidebar-inactive:hover {
                background-color: #1f2937;
            }
        }

        /* Toast notification styles */
        .toast {
            animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 2.7s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-50">
    <?php if ($user->isLoggedIn()): ?>
        <!-- SPA Progress Bar -->
        <div id="spa-progress" class="fixed top-0 left-0 h-1 bg-blue-600 z-[100] transition-all duration-300 shadow-[0_0_10px_rgba(37,99,235,0.5)]" style="width: 0; display: none;"></div>

        <?php
        // Get dynamic user notification count
        $notifCount = 0;
        $messageCount = 0;
        try {
            if (isset($_SESSION['user_id'])) {
                $notification = new Notification($database);
                $notifCount = $notification->getUnreadCount($_SESSION['user_id']);
            }

            $msgResult = $database->fetchOne("SELECT COUNT(*) as cnt FROM messages WHERE recipient_type = 'admin' AND is_read = 0");
            $messageCount = $msgResult['cnt'] ?? 0;
        } catch (Exception $e) {
            $notifCount = 0;
            $messageCount = 0;
        }
        ?>
        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="md:hidden fixed top-4 left-4 z-[60] p-2 bg-white rounded-lg shadow-lg border border-slate-200">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed left-0 top-0 h-full flex flex-col p-4 gap-2 bg-white dark:bg-slate-900 w-64 border-r border-slate-200/50 dark:border-slate-700/50 z-50 font-inter transform -translate-x-full md:translate-x-0 transition-transform duration-300">
            <div class="flex items-center gap-3 px-2 py-4 mb-6">
                <div class="w-10 h-10 rounded-lg bg-blue-900 flex items-center justify-center text-white shadow-lg">
                    <span class="material-symbols-outlined">account_balance</span>
                </div>
                <div>
                    <h1 class="font-bold text-blue-900 dark:text-blue-200 leading-tight text-sm">Integrated Web Based Information System for Youth Profiling and Skills Matching</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-widest mt-1">Youth Registry</p>
                </div>
            </div>

            <nav class="flex-1 flex flex-col gap-1">
                <?php
                $userRole = $_SESSION['role'] ?? 'staff';
                $navItems = [
                    ['name' => 'Dashboard', 'icon' => 'dashboard', 'path' => 'dashboard.php'],
                    ['name' => 'Profiles', 'icon' => 'people', 'path' => 'profiles.php'],
                    [
                        'name' => 'Opportunities',
                        'icon' => 'work',
                        'path' => '#',
                        'osy_only' => true,
                        'sub_items' => [
                            ['name' => 'All Opportunities', 'path' => 'opportunities.php'],
                            ['name' => 'Job Openings', 'path' => 'job-openings.php'],
                            ['name' => 'Training Programs', 'path' => 'training-programs.php']
                        ]
                    ],
                    ['name' => 'Matching', 'icon' => 'psychology', 'path' => 'matching.php', 'osy_only' => true],
                    ['name' => 'Notifications', 'icon' => 'notifications', 'path' => 'notifications.php'],
                    ['name' => 'My Notifications', 'icon' => 'notifications_active', 'path' => 'my-notifications.php'],
                    ['name' => 'Templates', 'icon' => 'description', 'path' => 'notification-templates.php'],
                    ['name' => 'Reports', 'icon' => 'assessment', 'path' => 'reports.php'],
                    ['name' => 'SK Chairmen', 'icon' => 'supervisor_account', 'path' => 'manage-sk-chairmen.php', 'roles' => ['lydo']],
                    ['name' => 'Provider Approvals', 'icon' => 'how_to_reg', 'path' => 'provider-approvals.php', 'roles' => ['lydo']],
                    ['name' => 'Youth Verification', 'icon' => 'verified_user', 'path' => 'verify-youth.php', 'roles' => ['sk_chairman']],
                    ['name' => 'Audit Logs', 'icon' => 'history_edu', 'path' => 'audit-logs.php', 'roles' => ['lydo']],
                ];

                $current_page = basename($_SERVER['PHP_SELF']);
                $basePath = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
                $basePath = $basePath === '/' ? '' : $basePath;

                foreach ($navItems as $index => $item):
                    if (isset($item['roles']) && !in_array($userRole, $item['roles'], true)) {
                        continue;
                    }
                    $hasSubItems = isset($item['sub_items']);
                    $isActive = $current_page == $item['path'];
                    $isSubActive = false;

                    if ($hasSubItems) {
                        foreach ($item['sub_items'] as $sub) {
                            if ($current_page == $sub['path']) {
                                $isSubActive = true;
                                $isActive = true;
                                break;
                            }
                        }
                    }
                ?>
                    <?php if ($hasSubItems): ?>
                        <div class="flex flex-col">
                            <button onclick="toggleSubmenu('submenu-<?php echo $index; ?>')" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-transform duration-200 hover:translate-x-1 <?php echo ($isActive ? 'sidebar-active' : 'sidebar-inactive'); ?> w-full text-left">
                                <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
                                <span class="text-sm flex-1"><?php echo $item['name']; ?></span>
                                <?php if (isset($item['osy_only']) && $item['osy_only']): ?>
                                    <span class="text-[10px] ml-auto bg-orange-200 text-orange-800 px-2 py-0.5 rounded-full font-bold">OSY</span>
                                <?php endif; ?>
                                <span class="material-symbols-outlined text-[16px] transition-transform duration-200" id="icon-submenu-<?php echo $index; ?>"><?php echo $isSubActive ? 'expand_less' : 'expand_more'; ?></span>
                            </button>
                            <div id="submenu-<?php echo $index; ?>" class="<?php echo $isSubActive ? '' : 'hidden'; ?> pl-10 pr-2 py-1 flex flex-col gap-1 mt-1">
                                <?php foreach ($item['sub_items'] as $sub): ?>
                                    <a href="<?php echo $basePath; ?>/pages/<?php echo $sub['path']; ?>" class="text-xs px-3 py-2 rounded-lg transition-colors <?php echo ($current_page == $sub['path'] ? 'text-blue-900 bg-blue-50 font-semibold dark:bg-blue-900/20 dark:text-blue-400' : 'text-slate-500 hover:text-blue-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'); ?>">
                                        <?php echo $sub['name']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>/pages/<?php echo $item['path']; ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-transform duration-200 hover:translate-x-1 <?php echo ($isActive ? 'sidebar-active' : 'sidebar-inactive'); ?>">
                            <span class="material-symbols-outlined"><?php echo $item['icon']; ?></span>
                            <span class="text-sm"><?php echo $item['name']; ?></span>
                            <?php if (isset($item['osy_only']) && $item['osy_only']): ?>
                                <span class="text-[10px] ml-auto bg-orange-200 text-orange-800 px-2 py-0.5 rounded-full font-bold">OSY</span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- Help Link -->
                <a href="<?php echo $basePath; ?>/pages/help.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-transform duration-200 hover:translate-x-1 mt-2 pt-2 border-t border-slate-200/50 dark:border-slate-700/50 <?php echo ($current_page == 'help.php' ? 'sidebar-active' : 'sidebar-inactive'); ?>">
                    <span class="material-symbols-outlined">help</span>
                    <span class="text-sm">Help Center</span>
                </a>
            </nav>

            <div class="mt-auto pt-4 border-t border-slate-200/50 dark:border-slate-700/50 flex flex-col gap-1">
                <a href="<?php echo $basePath; ?>/pages/matching.php" class="mb-4 w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-900 to-blue-800 text-white py-2.5 rounded-lg font-semibold text-sm shadow-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-sm">auto_awesome</span>
                    Match Skills
                </a>
                <a href="<?php echo $basePath; ?>/pages/settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-transform duration-200 hover:translate-x-1 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined text-[20px]">settings</span>
                    <span class="text-sm">Settings</span>
                </a>
                <a href="<?php echo $basePath; ?>/pages/logout.php" class="flex items-center gap-3 px-3 py-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg hover:translate-x-1 transition-transform duration-200">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="text-sm">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Top Bar -->
        <div class="fixed top-0 left-0 md:left-64 right-0 bg-white dark:bg-slate-900 border-b border-slate-200/50 dark:border-slate-700/50 h-16 flex items-center justify-between px-8 z-40">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white ml-10 md:ml-0"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h2>
            <div class="flex items-center gap-4">

                <a href="<?php echo $basePath; ?>/pages/my-notifications.php" class="relative inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-blue-900 transition-colors">
                    <span class="material-symbols-outlined">notifications</span>
                    <?php if (isset($notifCount) && $notifCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-sm border-2 border-white dark:border-slate-900"><?php echo $notifCount > 99 ? '99+' : $notifCount; ?></span>
                    <?php endif; ?>
                </a>
                <div class="flex items-center gap-3 border-l border-slate-200 dark:border-slate-700 pl-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-900 dark:text-blue-200">account_circle</span>
                    </div>
                    <div class="text-sm hidden sm:block">
                        <p class="font-semibold text-slate-900 dark:text-white"><?php echo $_SESSION['fullname'] ?? 'User'; ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo ucfirst($_SESSION['role'] ?? 'staff'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="md:ml-64 pt-24 pb-8 px-4 md:px-8 min-h-screen">
            <div class="max-w-7xl mx-auto">
            <?php endif; ?>

            <script>
                function toggleSidebar() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                }
                document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);

                function toggleSubmenu(id) {
                    const el = document.getElementById(id);
                    const icon = document.getElementById('icon-' + id);
                    if (el.classList.contains('hidden')) {
                        el.classList.remove('hidden');
                        icon.textContent = 'expand_less';
                    } else {
                        el.classList.add('hidden');
                        icon.textContent = 'expand_more';
                    }
                }

                // --- Single Page Application (SPA) Logic ---
                let currentSpaController = null;

                async function navigateTo(url, pushState = true) {
                    // Cancel any ongoing navigation
                    if (currentSpaController) {
                        currentSpaController.abort();
                    }
                    currentSpaController = new AbortController();
                    const signal = currentSpaController.signal;

                    const progressBar = document.getElementById('spa-progress');
                    const mainContent = document.querySelector('main');

                    if (progressBar) {
                        progressBar.style.display = 'block';
                        progressBar.style.width = '30%';
                    }

                    try {
                        const response = await fetch(url, {
                            signal
                        });
                        if (!response.ok) throw new Error('Navigation failed');

                        if (progressBar) progressBar.style.width = '70%';

                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newMain = doc.querySelector('main');
                        const currentMain = document.querySelector('main');

                        if (newMain && currentMain) {
                            // Update content
                            currentMain.innerHTML = newMain.innerHTML;

                            // Update Page Title in Top Bar
                            const newTitle = doc.querySelector('header h2') || doc.querySelector('h2');
                            const currentTitleEl = document.querySelector('div.fixed.top-0 h2');
                            if (newTitle && currentTitleEl) {
                                currentTitleEl.textContent = newTitle.textContent;
                            }

                            // Update Document Title
                            document.title = doc.title;

                            if (pushState) {
                                history.pushState({
                                    url
                                }, doc.title, url);
                            }

                            // Highlight Active Sidebar Link
                            updateSidebarHighlight(url);

                            // CRITICAL: Execute all scripts found in the new content
                            executeScripts(currentMain);

                            // Re-initialize dynamic elements
                            reinitializeState();

                            // Scroll to top
                            window.scrollTo(0, 0);
                        }
                    } catch (error) {
                        if (error.name === 'AbortError') {
                            console.log('Navigation aborted:', url);
                            return;
                        }
                        console.error('SPA Error:', error);
                        window.location.href = url;
                    } finally {
                        // Only hide progress bar if this is still the active navigation
                        if (currentSpaController && currentSpaController.signal === signal) {
                            if (progressBar) {
                                progressBar.style.width = '100%';
                                setTimeout(() => {
                                    // Re-check after timeout in case a new nav started
                                    if (currentSpaController && currentSpaController.signal === signal) {
                                        progressBar.style.display = 'none';
                                        progressBar.style.width = '0';
                                    }
                                }, 500);
                            }
                            currentSpaController = null;
                        }
                    }
                }

                // Script Execution Engine: Manually runs scripts in AJAX-loaded content
                function executeScripts(container) {
                    const scripts = container.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });

                        if (oldScript.src) {
                            // For external scripts
                            newScript.src = oldScript.src;
                        } else {
                            // For inline scripts
                            newScript.textContent = oldScript.textContent;
                        }

                        // Append directly to body to execute, then remove to keep DOM clean
                        document.body.appendChild(newScript);
                        // We keep it in the DOM if it might be needed for debugging, but typically removing is fine
                        // document.body.removeChild(newScript); 
                    });
                }

                function reinitializeState() {
                    console.log('SPA: Re-initializing page state...');
                    // Re-bind global UI events that might be lost
                    document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);

                    // Check for page-specific inits
                    if (typeof filterProfiles === 'function') filterProfiles();
                }

                function updateSidebarHighlight(url) {
                    const links = document.querySelectorAll('aside nav a');
                    const urlObj = new URL(url, window.location.origin);
                    const path = urlObj.pathname + urlObj.search;

                    links.forEach(link => {
                        const linkUrl = new URL(link.href, window.location.origin);
                        const linkPath = linkUrl.pathname + linkUrl.search;

                        // Check if it's the exact path or if this is a sub-item
                        if (path.includes(linkPath) && linkPath.length > 1) {
                            link.classList.add('sidebar-active');
                            link.classList.remove('sidebar-inactive');
                        } else {
                            link.classList.remove('sidebar-active');
                            link.classList.add('sidebar-inactive');
                        }
                    });
                }

                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (!link || e.ctrlKey || e.shiftKey || e.metaKey || e.button !== 0) return;

                    const url = new URL(link.href, window.location.origin);
                    if (url.origin === window.location.origin && !link.hasAttribute('download') && link.target !== '_blank') {
                        const path = url.pathname;
                        if (path.endsWith('.php') && !path.includes('logout.php')) {
                            e.preventDefault();
                            navigateTo(link.href);
                            if (!document.getElementById('sidebar').classList.contains('-translate-x-full')) {
                                toggleSidebar();
                            }
                        }
                    }
                });

                window.addEventListener('popstate', (e) => {
                    if (e.state && e.state.url) {
                        navigateTo(e.state.url, false);
                    } else {
                        // Fallback for initial page load or non-SPA states
                        location.reload();
                    }
                });

                // Intercept Form Submissions
                document.addEventListener('submit', async (e) => {
                    const form = e.target;
                    const url = new URL(form.action || window.location.href, window.location.origin);

                    if (url.origin === window.location.origin && url.pathname.includes('.php') && !url.pathname.includes('logout.php')) {
                        e.preventDefault();

                        const progressBar = document.getElementById('spa-progress');
                        if (progressBar) {
                            progressBar.style.display = 'block';
                            progressBar.style.width = '30%';
                        }

                        // Cancel any ongoing navigation
                        if (currentSpaController) {
                            currentSpaController.abort();
                        }
                        currentSpaController = new AbortController();
                        const signal = currentSpaController.signal;

                        try {
                            const formData = new FormData(form);
                            if (e.submitter && e.submitter.name) {
                                formData.append(e.submitter.name, e.submitter.value);
                            }

                            const response = await fetch(url.href, {
                                method: form.method || 'POST',
                                body: formData,
                                signal
                            });

                            if (!response.ok) throw new Error('Submission failed');

                            if (progressBar) progressBar.style.width = '70%';
                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            const newMain = doc.querySelector('main');
                            const currentMain = document.querySelector('main');

                            if (newMain && currentMain) {
                                currentMain.innerHTML = newMain.innerHTML;
                                document.title = doc.title;

                                // Handle potential redirect after POST
                                const finalUrl = response.url;
                                if (finalUrl !== window.location.href) {
                                    history.pushState({
                                        url: finalUrl
                                    }, doc.title, finalUrl);
                                }

                                updateSidebarHighlight(finalUrl);
                                executeScripts(currentMain);
                                reinitializeState();
                                window.scrollTo(0, 0);
                            }
                        } catch (error) {
                            if (error.name === 'AbortError') {
                                console.log('Form submission aborted');
                                return;
                            }
                            console.error('SPA Form Error:', error);
                            form.submit();
                        } finally {
                            if (currentSpaController && currentSpaController.signal === signal) {
                                if (progressBar) {
                                    progressBar.style.width = '100%';
                                    setTimeout(() => {
                                        if (currentSpaController && currentSpaController.signal === signal) {
                                            progressBar.style.display = 'none';
                                            progressBar.style.width = '0';
                                        }
                                    }, 500);
                                }
                                currentSpaController = null;
                            }
                        }
                    }
                });
            </script>