<?php
$pageTitle = 'Help Center';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hidden meta for SPA navigation highlighting -->
<meta name="page" content="help">

<!-- Skeleton loader for help page -->
<div id="skeleton-loader-help" style="padding:2rem">
    <div class="skeleton-pulse" style="height:2rem;width:40%;margin-bottom:1rem;border-radius:6px"></div>
    <div class="skeleton-pulse" style="height:1rem;width:70%;margin-bottom:.5rem;border-radius:6px"></div>
    <div class="skeleton-pulse" style="height:1rem;width:55%;border-radius:6px"></div>
</div>
<div id="real-content-help" style="display:none">

<!-- Help Center Header -->
<div class="mb-12">
    <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-4">Help Center</h1>
    <p class="text-lg text-slate-600 dark:text-slate-400">Find answers to common questions and learn how to use the Youth Profiling System.</p>
</div>

<!-- Search Bar -->
<div class="mb-12">
    <div class="relative max-w-2xl mx-auto">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input type="text" placeholder="Search help articles..." class="client-filter w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-4 pl-12 pr-4 text-base focus:ring-2 focus:ring-blue-900 transition-all text-slate-900 dark:text-white" data-target=".faq-item" data-filter-type="search" />
    </div>
</div>

<!-- FAQ Categories -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
    <!-- Getting Started -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">🚀</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Getting Started</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Learn the basics and set up your account.</p>
    </div>

    <!-- Profiles -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">👥</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Managing Profiles</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Create, edit, and manage OSY profiles.</p>
    </div>

    <!-- Opportunities -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">💼</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Opportunities</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Post and manage job and training opportunities.</p>
    </div>

    <!-- Matching -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">🎯</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Skills Matching</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Understand how our matching algorithm works.</p>
    </div>

    <!-- Reports -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">📊</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Reports & Analytics</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Generate and analyze reports.</p>
    </div>

    <!-- Account & Settings -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 hover:shadow-lg transition-shadow cursor-pointer">
        <div class="text-3xl mb-4">⚙️</div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Account & Settings</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400">Manage your account and preferences.</p>
    </div>
</div>

<!-- Detailed FAQs -->
<div class="space-y-6 max-w-4xl">
    <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-8">Frequently Asked Questions</h2>

    <!-- FAQ Item 1 -->
    <div class="faq-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <button class="w-full text-left font-bold text-lg text-slate-900 dark:text-white flex justify-between items-center faq-toggle focus:outline-none">
            <span>How do I create a new youth profile?</span>
            <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
        </button>
        <div class="faq-content hidden mt-4 text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-4">
            <p class="mb-3">
                To create a new youth profile:
            </p>
            <ol class="list-decimal list-inside space-y-2">
                <li>Navigate to the KK Profiles page from the sidebar</li>
                <li>Click the "Add New Profile" button</li>
                <li>Fill in the required information (name, age, skills, etc.)</li>
                <li>Click "Save Profile"</li>
            </ol>
        </div>
    </div>

    <!-- FAQ Item 2 -->
    <div class="faq-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <button class="w-full text-left font-bold text-lg text-slate-900 dark:text-white flex justify-between items-center faq-toggle focus:outline-none">
            <span>How does the skills matching work?</span>
            <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
        </button>
        <div class="faq-content hidden mt-4 text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-4">
            <p>
                Our AI-powered matching algorithm analyzes the skills of each OSY profile and compares them with available opportunities.
                It calculates a match score (0-100%) based on skill alignment, educational attainment, and other relevant factors.
                A higher score indicates a better fit for the opportunity.
            </p>
        </div>
    </div>

    <!-- FAQ Item 3 -->
    <div class="faq-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <button class="w-full text-left font-bold text-lg text-slate-900 dark:text-white flex justify-between items-center faq-toggle focus:outline-none">
            <span>Can I adjust the minimum match score threshold?</span>
            <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
        </button>
        <div class="faq-content hidden mt-4 text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-4">
            <p class="mb-3">
                Yes! On the Skills Matching page, you can use the slider to set your preferred minimum match score.
                This allows you to filter candidates by your desired match quality:
            </p>
            <ul class="list-disc list-inside space-y-2">
                <li>60-70%: Potential candidates with room for training</li>
                <li>70-85%: Good matches ready for opportunities</li>
                <li>85-100%: Excellent matches with strong skill alignment</li>
            </ul>
        </div>
    </div>

    <!-- FAQ Item 4 -->
    <div class="faq-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <button class="w-full text-left font-bold text-lg text-slate-900 dark:text-white flex justify-between items-center faq-toggle focus:outline-none">
            <span>How do I send notifications to multiple users?</span>
            <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
        </button>
        <div class="faq-content hidden mt-4 text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-4">
            <p>
                From the Notifications page, click "Send New Notification". You can select the recipient type
                (All OSY, All Staff, or Specific users), compose your message, and broadcast it instantly.
                The system logs all notifications for audit purposes.
            </p>
        </div>
    </div>

    <!-- FAQ Item 5 -->
    <div class="faq-item bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <button class="w-full text-left font-bold text-lg text-slate-900 dark:text-white flex justify-between items-center faq-toggle focus:outline-none">
            <span>What reports are available?</span>
            <span class="material-symbols-outlined transition-transform duration-200">expand_more</span>
        </button>
        <div class="faq-content hidden mt-4 text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-4">
            <p class="mb-3">
                The system provides several useful reports:
            </p>
            <ul class="list-disc list-inside space-y-2">
                <li><strong>Youth Demographics Report:</strong> Age, gender, education distribution</li>
                <li><strong>Skills Analysis Report:</strong> Most in-demand skills and gaps</li>
                <li><strong>Matching Report:</strong> Success rates and match quality metrics</li>
                <li><strong>Employment Status Report:</strong> Tracking youth employment outcomes</li>
                <li><strong>Opportunity Performance Report:</strong> Usage and success of each opportunity posted</li>
            </ul>
        </div>
    </div>
</div>

<!-- Contact Support -->
<div class="mt-12 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl p-8">
    <h3 class="text-2xl font-bold mb-4">Still need help?</h3>
    <p class="mb-6">Can't find what you're looking for? Our support team is here to help.</p>
    <a href="mailto:support@civichorizon.ph" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-900 rounded-lg font-bold hover:bg-blue-50 transition-colors">
        <span class="material-symbols-outlined">mail</span>
        Contact Support
    </a>
</div>

<!-- Back to top button -->
<button id="back-to-top" class="fixed bottom-6 right-6 p-3 bg-blue-900 text-white rounded-full shadow-lg hover:bg-blue-800 hidden transition-all duration-300 focus:outline-none z-50">
    <span class="material-symbols-outlined">arrow_upward</span>
</button>

<script>
(function() {
    // Accordion Toggle logic
    document.querySelectorAll('.faq-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            const icon = btn.querySelector('.material-symbols-outlined');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });
    });

    // Back to top logic
    const backToTopBtn = document.getElementById('back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.remove('hidden');
            } else {
                backToTopBtn.classList.add('hidden');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();
</script>


</div><!-- /#real-content-help -->

<script>
(function(){
    var sk = document.getElementById('skeleton-loader-help');
    var rc = document.getElementById('real-content-help');
    if(sk) sk.style.display = 'none';
    if(rc) rc.style.display = '';
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
