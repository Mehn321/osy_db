<?php
$pageTitle = 'Profile Details';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole(['lydo', 'sk_chairman']);

require_once __DIR__ . '/../includes/header.php';

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$osyProfile = new OSYProfile($database);
$message = '';
$messageType = '';

// Fake data to prevent PHP warnings and bypass checks during initial load
$profile = [
    'id' => $profile_id,
    'first_name' => '', 'last_name' => '', 'middle_name' => '',
    'profile_type' => 'OSY', 'status' => '', 'age' => 0, 'gender' => '',
    'email' => '', 'phone' => '', 'suffix' => '', 'civil_status' => '',
    'date_of_birth' => '', 'govt_id_type' => '', 'education_level' => '',
    'occupation' => '', 'primary_skill' => '', 'skills' => '', 'interests' => '',
    'purok' => '', 'address' => '', 'barangay' => $_SESSION['role'] === 'sk_chairman' ? $_SESSION['barangay'] : '',
    'reason_for_not_in_school' => '', 'image_path' => '', 'created_at' => date('Y-m-d')
];

// Handle delete (Restricted to LYDO only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        requireRole('lydo'); // Double check role for destructive action
        $result = $osyProfile->delete(intval($_POST['profile_id']));
        if ($result['success']) {
            header('Location: profiles.php?success=deleted');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

$matches = [];
?>

<div id="skeleton-container" class="animate-pulse">
    <!-- Breadcrumb Skeleton -->
    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3 mb-6"></div>
    
    <!-- Profile Header Skeleton -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 mb-8">
        <div class="flex flex-col md:flex-row items-start gap-8">
            <div class="w-24 h-24 rounded-full bg-slate-200 dark:bg-slate-700 flex-shrink-0"></div>
            <div class="flex-1 w-full space-y-4">
                <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
                <div class="flex gap-4">
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-24"></div>
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-24"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="main-content" style="display: none;">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-600 dark:text-slate-400 mb-6">
        <a href="dashboard.php" class="hover:text-blue-900 dark:hover:text-blue-400">Dashboard</a>
        <span class="mx-2">›</span>
        <?php if ($_SESSION['role'] === 'sk_chairman'): ?>
            <a href="sk-barangay-youth.php" class="hover:text-blue-900 dark:hover:text-blue-400">My Barangay</a>
        <?php else: ?>
            <a href="profiles.php" class="hover:text-blue-900 dark:hover:text-blue-400">Profiles</a>
        <?php endif; ?>
        <span class="mx-2">›</span>
        <span id="breadcrumb-name" class="text-blue-900 dark:text-blue-400 font-semibold"></span>
    </nav>

    <?php if ($message): ?>
        <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
            <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 mb-8">
        <div class="flex flex-col md:flex-row items-start gap-8">
            <!-- Profile Image -->
            <div class="flex-shrink-0" id="profile-image-container">
            </div>

            <!-- Profile Info -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h1 id="profile-full-name" class="text-4xl font-bold text-slate-900 dark:text-white"></h1>
                    <span id="profile-type" class="text-xs font-semibold px-2 py-1 rounded-md"></span>
                </div>
                <div class="flex items-center gap-4 mb-6">
                    <span id="profile-status" class="inline-flex px-4 py-2 rounded-lg font-semibold text-sm"></span>
                    <span id="profile-age-text" class="text-slate-600 dark:text-slate-400"></span>
                    <span id="profile-gender-text" class="text-slate-600 dark:text-slate-400"></span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Email:</span>
                        <p id="detail-email" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Phone:</span>
                        <p id="detail-phone" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Suffix:</span>
                        <p id="detail-suffix" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Civil Status:</span>
                        <p id="detail-civil-status" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div id="container-dob" style="display: none;">
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Date of Birth:</span>
                        <p id="detail-dob" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div id="container-govt-id" style="display: none;">
                        <span class="text-slate-600 dark:text-slate-400 font-medium">ID Type:</span>
                        <p id="detail-govt-id" class="text-slate-900 dark:text-white"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Education & Skills -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Education & Skills</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Educational Attainment</span>
                        <p id="detail-education" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Occupation</span>
                        <p id="detail-occupation" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Primary Skill</span>
                        <p id="detail-primary-skill" class="text-slate-900 dark:text-white font-semibold text-blue-900 dark:text-blue-400"></p>
                    </div>
                    <div id="container-skills" style="display: none;">
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">All Skills</span>
                        <p id="detail-skills" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div id="container-interests" style="display: none;">
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Interests</span>
                        <p id="detail-interests" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Purok</span>
                        <p id="detail-purok" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Barangay</span>
                        <p id="detail-barangay" class="text-slate-900 dark:text-white"></p>
                    </div>
                    <div id="container-reason" style="display: none;">
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Reason for Not in School</span>
                        <p id="detail-reason" class="text-slate-900 dark:text-white"></p>
                    </div>
                </div>
            </div>

            <!-- Matched Opportunities -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Matched Opportunities</h3>
                <div id="matches-container">
                    <!-- Matches will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Age</span>
                        <p id="stat-age" class="text-2xl font-bold text-blue-900 dark:text-blue-400"></p>
                    </div>
                    <div>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Total Matches</span>
                        <p id="stat-matches" class="text-2xl font-bold text-blue-900 dark:text-blue-400"></p>
                    </div>
                    <div>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Status</span>
                        <p id="stat-status" class="text-lg font-semibold text-slate-900 dark:text-white"></p>
                    </div>
                    <div>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Registered</span>
                        <p id="stat-registered" class="text-sm font-semibold text-slate-900 dark:text-white"></p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Actions</h3>
                <div class="space-y-3">
                    <a id="btn-edit" href="#" class="block w-full px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg font-semibold text-sm transition-colors text-center">
                        <span class="flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">edit</span>
                            Edit Profile
                        </span>
                    </a>
                    <?php if ($_SESSION['role'] === 'lydo'): ?>
                        <a href="matching.php" class="block w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm transition-colors text-center">
                            <span class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-base">psychology</span>
                                Find Matches
                            </span>
                        </a>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this profile? This cannot be undone.')">
                        <input type="hidden" name="profile_id" id="form-profile-id" value="<?php echo $profile_id; ?>">
                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                        <button type="submit" name="delete_profile" value="1" class="w-full px-4 py-2 bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg font-semibold text-sm transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-base">delete</span>
                                Delete Profile
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var profileId = <?php echo $profile_id; ?>;
    
    function escapeHtml(unsafe) {
        if (unsafe == null) return '';
        return (unsafe + '')
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    fetch('../api/get_profile_detail.php?id=' + profileId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.error) {
                window.location.href = 'profiles.php?error=' + encodeURIComponent(data.error);
                return;
            }
            
            var profile = data.profile;
            var matches = data.matches;
            
            var fullName = profile.first_name + ' ' + (profile.middle_name ? profile.middle_name + ' ' : '') + profile.last_name;
            
            document.getElementById('breadcrumb-name').innerText = fullName;
            document.getElementById('profile-full-name').innerText = fullName;
            
            var profileTypeEl = document.getElementById('profile-type');
            profileTypeEl.innerText = profile.profile_type;
            profileTypeEl.className = 'text-xs font-semibold px-2 py-1 rounded-md ' + (profile.profile_type === 'OSY' ? 'bg-orange-100 text-orange-900' : 'bg-green-100 text-green-900');
            
            var statusEl = document.getElementById('profile-status');
            statusEl.innerText = profile.status;
            var statusClass = 'inline-flex px-4 py-2 rounded-lg font-semibold text-sm ';
            if (profile.status == 'Active') statusClass += 'bg-green-100 text-green-900';
            else if (profile.status == 'Employed') statusClass += 'bg-blue-100 text-blue-900';
            else if (profile.status == 'In Training') statusClass += 'bg-orange-100 text-orange-900';
            else statusClass += 'bg-slate-100 text-slate-900';
            statusEl.className = statusClass;
            
            document.getElementById('profile-age-text').innerText = profile.age + ' years old';
            if (profile.gender) {
                document.getElementById('profile-gender-text').innerText = '• ' + profile.gender;
            }
            
            var imgContainer = document.getElementById('profile-image-container');
            if (profile.image_path) {
                imgContainer.innerHTML = '<img src="' + escapeHtml(profile.image_path) + '" alt="Profile" class="w-24 h-24 rounded-full object-cover">';
            } else {
                var initials = (profile.first_name.charAt(0) + profile.last_name.charAt(0)).toUpperCase();
                imgContainer.innerHTML = '<div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-4xl font-bold">' + escapeHtml(initials) + '</div>';
            }
            
            document.getElementById('detail-email').innerText = profile.email || 'N/A';
            document.getElementById('detail-phone').innerText = profile.phone || 'N/A';
            document.getElementById('detail-suffix').innerText = profile.suffix ? profile.suffix : 'None';
            document.getElementById('detail-civil-status').innerText = profile.civil_status || 'N/A';
            
            if (profile.date_of_birth) {
                document.getElementById('container-dob').style.display = 'block';
                document.getElementById('detail-dob').innerText = new Date(profile.date_of_birth).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }
            
            if (profile.govt_id_type) {
                document.getElementById('container-govt-id').style.display = 'block';
                document.getElementById('detail-govt-id').innerText = profile.govt_id_type;
            }
            
            document.getElementById('detail-education').innerText = profile.education_level || 'N/A';
            document.getElementById('detail-occupation').innerText = profile.occupation || 'N/A';
            document.getElementById('detail-primary-skill').innerText = profile.primary_skill || 'N/A';
            
            if (profile.skills) {
                document.getElementById('container-skills').style.display = 'block';
                document.getElementById('detail-skills').innerText = profile.skills;
            }
            
            if (profile.interests) {
                document.getElementById('container-interests').style.display = 'block';
                document.getElementById('detail-interests').innerText = profile.interests;
            }
            
            document.getElementById('detail-purok').innerText = profile.purok || profile.address || 'N/A';
            document.getElementById('detail-barangay').innerText = profile.barangay || 'N/A';
            
            if (profile.reason_for_not_in_school) {
                document.getElementById('container-reason').style.display = 'block';
                document.getElementById('detail-reason').innerText = profile.reason_for_not_in_school;
            }
            
            document.getElementById('stat-age').innerText = profile.age;
            document.getElementById('stat-matches').innerText = matches.length;
            document.getElementById('stat-status').innerText = profile.status;
            document.getElementById('stat-registered').innerText = new Date(profile.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            
            document.getElementById('btn-edit').href = 'edit-profile.php?id=' + profile.id;
            document.getElementById('form-profile-id').value = profile.id;
            
            var matchesContainer = document.getElementById('matches-container');
            if (matches && matches.length > 0) {
                var html = '<div class="space-y-3">';
                for (var i = 0; i < matches.length; i++) {
                    var match = matches[i];
                    var statusClassMatch = '';
                    if (match.status == 'Accepted') statusClassMatch = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                    else if (match.status == 'Pending') statusClassMatch = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                    else if (match.status == 'Rejected') statusClassMatch = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                    
                    html += '<div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">' +
                                '<div class="flex items-start justify-between mb-2">' +
                                    '<h4 class="font-semibold text-slate-900 dark:text-white">' + escapeHtml(match.title || 'Unknown Opportunity') + '</h4>' +
                                    '<span class="px-2 py-1 text-xs font-bold rounded ' + statusClassMatch + '">' + escapeHtml(match.status) + '</span>' +
                                '</div>' +
                                '<div class="flex items-center justify-between">' +
                                    '<span class="text-sm text-slate-600 dark:text-slate-400">' + escapeHtml(match.type || 'N/A') + '</span>' +
                                    '<span class="text-sm font-bold text-blue-900 dark:text-blue-400">Match Score: ' + match.match_score + '%</span>' +
                                '</div>' +
                            '</div>';
                }
                html += '</div>';
                matchesContainer.innerHTML = html;
            } else {
                matchesContainer.innerHTML = '<p class="text-slate-600 dark:text-slate-400 text-center py-8">No matched opportunities yet</p>';
            }
            
            document.getElementById('skeleton-container').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
        })
        .catch(function(err) {
            console.error('Failed to load profile:', err);
        });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>