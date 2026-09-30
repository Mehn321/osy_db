<?php
$pageTitle = 'Help Center';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = (string) ($_SESSION['role'] ?? '');
$helpByRole = [
    'youth' => [
        'intro' => 'Manage your youth profile, track verification, and find opportunities that fit your skills.',
        'guides' => [
            ['icon' => 'person', 'title' => 'Keep your profile complete', 'steps' => ['Open My Profile from the side menu.', 'Select Edit all information.', 'Update your details, address, skills, or documents, then select Save all changes. A barangay change remains pending until reviewed.']],
            ['icon' => 'verified_user', 'title' => 'Check verification', 'steps' => ['Review the verification status shown on your profile.', 'If action is required, read the reviewer remark.', 'Update the requested details and save to resubmit.']],
            ['icon' => 'work', 'title' => 'Apply to opportunities', 'steps' => ['Open Opportunities from the side menu.', 'Select View Details on an opportunity and review its requirements.', 'Select Apply when eligible, then check My Notifications for updates.']],
            ['icon' => 'notifications', 'title' => 'Read notifications', 'steps' => ['Open My Notifications from the side menu or the notification bell.', 'Unread notifications are highlighted and marked New.', 'Select Mark as Read after reviewing a notification.']],
        ],
        'faqs' => [
            ['q' => 'Why can’t I use all youth features yet?', 'a' => 'Some opportunities and matching features require your profile to be verified and your account to be active. Check the status shown on My Profile.'],
            ['q' => 'How do I change my barangay?', 'a' => 'Edit your profile and submit a barangay transfer request. Your current barangay remains active until the receiving SK Chairman reviews the request.'],
            ['q' => 'Where can I see application updates?', 'a' => 'Open My Notifications to see application decisions, verification updates, and announcements.'],
        ],
    ],
    'sk_chairman' => [
        'intro' => 'Review youth registrations assigned to your barangay, process verification, and follow up with youth members.',
        'guides' => [
            ['icon' => 'groups', 'title' => 'Review barangay youth', 'steps' => ['Open My Barangay Youth from the side menu.', 'Review the Pending Approval section for self-registered youth.', 'Select Full Profile to inspect a submission, or use the review buttons shown on the youth record.']],
            ['icon' => 'verified_user', 'title' => 'Verify registrations', 'steps' => ['Open Verify Youth from the side menu.', 'Review the submitted information and uploaded files.', 'Choose Approve, Return for Correction, or Reject. Add a reviewer note when returning or rejecting a submission.']],
            ['icon' => 'swap_horiz', 'title' => 'Review incoming transfers', 'steps' => ['Open My Barangay Youth.', 'Find Incoming Barangay Transfers near the top of the page.', 'Review the request and select Verify & Transfer or Decline.']],
            ['icon' => 'notifications', 'title' => 'Track system updates', 'steps' => ['Open My Notifications from the side menu or notification bell.', 'Unread items are highlighted and marked New.', 'Use Help Center or Contact Support if an issue cannot be resolved.']],
        ],
        'faqs' => [
            ['q' => 'What should I do when a profile needs corrections?', 'a' => 'Decline or request action using the available review control and write a specific remark so the youth member knows what to correct.'],
            ['q' => 'What happens after approval?', 'a' => 'The youth profile status is updated and the member can access features available to verified users.'],
            ['q' => 'Can I review a transfer request?', 'a' => 'Transfer requests are routed to the receiving SK Chairman. Review the pending request and verify it before the youth member is moved.'],
        ],
    ],
    'employer' => [
        'intro' => 'Create job openings, review applicants, and connect with youth whose skills match your needs.',
        'guides' => [
            ['icon' => 'work', 'title' => 'Manage job openings', 'steps' => ['Open My Job Openings from the side menu.', 'Select Post New Job and complete the job details, then submit the form.', 'Use the Edit Job action to update a posting or View Applicants to open its candidate pipeline.']],
            ['icon' => 'psychology', 'title' => 'Review job matches', 'steps' => ['Open Skills Matching from the side menu or select View Applicants for a job.', 'Choose the job under Target Opportunity and review candidates in the Employment Pipeline.', 'Use Details to inspect a candidate, Accept or Reject pending candidates, or Disapprove an accepted candidate.']],
            ['icon' => 'groups', 'title' => 'Track applicants', 'steps' => ['Open My Job Openings and select View Applicants beside a job.', 'Review candidates in the Shortlisted, Rejected, and Accepted sections.', 'Open candidate details before making a decision.']],
            ['icon' => 'notifications', 'title' => 'Follow updates', 'steps' => ['Open My Notifications from the side menu or notification bell.', 'Use the top-right profile icon labeled View profile to open My Profile.', 'Select Edit Profile to update your name or email; change your password from the same page.']],
        ],
        'faqs' => [
            ['q' => 'How do I attract suitable applicants?', 'a' => 'Provide specific duties, required skills, location, compensation information, and a realistic deadline in each opening.'],
            ['q' => 'Where do I decide on an application?', 'a' => 'Open the applications for the relevant opportunity and use the Accept or Reject action for each pending applicant.'],
            ['q' => 'Why is a candidate match score only a guide?', 'a' => 'A match score compares profile and opportunity information. Review the full applicant details before making a decision.'],
        ],
    ],
    'training_provider' => [
        'intro' => 'Publish training programs, review applicants, and keep program information current.',
        'guides' => [
            ['icon' => 'school', 'title' => 'Manage training programs', 'steps' => ['Open My Programs from the side menu.', 'Select Add New Program and complete the program details, then submit the form.', 'Use Edit Program beside a listing to update it.']],
            ['icon' => 'groups', 'title' => 'Review applicants', 'steps' => ['Open Program Applicants from the side menu, or select View Applicants beside a program in My Programs.', 'Choose a training program from the selector if needed.', 'Review each application and use its Accept or Reject action.']],
            ['icon' => 'notifications', 'title' => 'Check application updates', 'steps' => ['Open My Notifications from the side menu or notification bell.', 'Open Program Applicants to see applicants for each program.', 'Use Edit Program from My Programs when program information needs updating.']],
            ['icon' => 'manage_accounts', 'title' => 'Manage your account', 'steps' => ['Select the top-right profile icon labeled View profile.', 'Choose Edit Profile to update your name or email, then select Save personal details.', 'In the Change Password section, enter your current and new passwords, then select Change password.']],
        ],
        'faqs' => [
            ['q' => 'Why is my program missing from the applicant selector?', 'a' => 'The selector lists programs owned by your account. Confirm that you are signed in with the account that created the program and that the program is not a job opening.'],
            ['q' => 'How do I decide on applicants?', 'a' => 'Review the profile and application information for the selected program, then use the status action for each pending application.'],
            ['q' => 'How do I notify applicants about changes?', 'a' => 'Update the program information and use My Notifications to review system messages. Contact Support if a program update is not appearing.'],
        ],
    ],
    'lydo' => [
        'intro' => 'Administer youth records, opportunities, approvals, reports, notifications, and system configuration.',
        'guides' => [
            ['icon' => 'people', 'title' => 'Manage youth records', 'steps' => ['Open Youth Profiles to use KK Profile Management, or Member Registry to manage member accounts.', 'In Youth Profiles, search by name or email and use the filters for type, barangay, gender, education, or status.', 'Open a profile from its row actions to review details and use the available profile controls.']],
            ['icon' => 'work', 'title' => 'Monitor opportunities', 'steps' => ['Open Opportunities in the side menu and select All Opportunities, Job Openings, or Training Programs.', 'Use the Search, Type, and Status controls to find a listing.', 'Review its status and applicant activity from the relevant opportunity or applicant page.']],
            ['icon' => 'how_to_reg', 'title' => 'Review provider approvals', 'steps' => ['Open Provider Approvals from the side menu.', 'Review the employer or training provider account details.', 'Use Approve or Decline and provide a clear remark when the form requests one.']],
            ['icon' => 'assessment', 'title' => 'Use reports and settings', 'steps' => ['Open Reports from the side menu to review available summaries and exports.', 'Open Settings to manage your profile, notification services, AI, and matching options.', 'Under Settings > Notifications, set the editable general Contact Support email. LYDO support requests go to the fixed address displayed on Contact Support.']],
        ],
        'faqs' => [
            ['q' => 'How do I send a system announcement?', 'a' => 'Open Notifications, compose the announcement, choose the intended recipients and delivery channels, then send it. Review the result summary for delivery failures.'],
            ['q' => 'Where do I configure the support inbox?', 'a' => 'Open Settings and select Notifications. Update the Contact Support email there; LYDO Contact Support requests themselves always go to the locked address shown on the form.'],
            ['q' => 'How should I handle a provider approval?', 'a' => 'Review the submitted account details before approving. If information is incomplete, decline with a clear explanation so the provider can follow up.'],
        ],
    ],
];
$helpContent = $helpByRole[$role] ?? $helpByRole['youth'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-8">
    <p class="mb-2 text-xs font-bold uppercase tracking-widest text-blue-700 dark:text-blue-400">Help Center</p>
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Guidance for your workspace</h1>
    <p class="mt-2 max-w-3xl text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($helpContent['intro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
</div>

<section aria-labelledby="guides-heading" class="mb-10">
    <h2 id="guides-heading" class="mb-4 text-xl font-bold text-slate-900 dark:text-white">Your common tasks</h2>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <?php foreach ($helpContent['guides'] as $guide): ?>
            <article class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                <h3 class="mb-3 flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                    <span class="material-symbols-outlined text-blue-700 dark:text-blue-400" aria-hidden="true"><?php echo htmlspecialchars($guide['icon']); ?></span>
                    <?php echo htmlspecialchars($guide['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                </h3>
                <ol class="list-decimal space-y-2 pl-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    <?php foreach ($guide['steps'] as $step): ?>
                        <li><?php echo htmlspecialchars($step, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ol>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section aria-labelledby="faq-heading" class="max-w-4xl">
    <h2 id="faq-heading" class="mb-4 text-xl font-bold text-slate-900 dark:text-white">Common questions</h2>
    <div class="divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-800">
        <?php foreach ($helpContent['faqs'] as $faq): ?>
            <details class="group p-5">
                <summary class="cursor-pointer list-none pr-8 font-semibold text-slate-900 marker:hidden dark:text-white">
                    <span class="flex items-start justify-between gap-4">
                        <span><?php echo htmlspecialchars($faq['q'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                        <span class="material-symbols-outlined shrink-0 transition-transform group-open:rotate-180" aria-hidden="true">expand_more</span>
                    </span>
                </summary>
                <p class="mt-3 border-t border-slate-100 pt-3 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                    <?php echo htmlspecialchars($faq['a'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                </p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="mt-10 flex flex-col gap-4 rounded-lg bg-slate-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-8">
    <div>
        <h2 class="text-xl font-bold">Need help with something else?</h2>
        <p class="mt-1 text-sm text-slate-300">Send a support request directly from your account.</p>
    </div>
    <a href="contact-support.php" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 font-bold text-slate-900 hover:bg-slate-100">
        <span class="material-symbols-outlined" aria-hidden="true">mail</span>Contact Support
    </a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>