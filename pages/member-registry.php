<?php
$pageTitle = 'Member Registration';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/OSYProfile.php';
require_once __DIR__ . '/../Classes/Reference.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$reference = new Reference($database);
$govtIdTypes = $reference->getByCategory('govt_id_type');
$barangays = $reference->getByCategory('barangay');
$eduLevels = $reference->getByCategory('education_level');
$reasons = $reference->getByCategory('reason');

$osyProfile = new OSYProfile($database);
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_member'])) {
    try {
        // Get uploaded images if exist
        $profileImageFile = $_FILES['profile_image'] ?? null;
        $govtIdImageFile = $_FILES['govt_id_image'] ?? null;

        // Create member profile
        $result = $osyProfile->create([
            'profile_type' => $_POST['profile_type'] ?? 'Regular',
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'],
            'date_of_birth' => $_POST['birthdate'],
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civil_status'],
            'phone' => $_POST['contact_number'],
            'email' => $_POST['email'],
            'barangay' => $_POST['barangay'],
            'govt_id_type' => $_POST['govt_id_type'],
            'govt_id_number' => $_POST['govt_id_number'] ?? null,
            'education_level' => $_POST['education_level'],
            'reason_for_not_in_school' => $_POST['reason_for_school'] ?? null,
            'engagement_status' => $_POST['engagement_status'] ?? null,
            'primary_skill' => $_POST['skills'] ?? 'Not Specified',
            'skills' => $_POST['skills'],
            'interests' => $_POST['interests'],
            'status' => 'Active',
            'registration_status' => 'Submitted'
        ], $profileImageFile, $govtIdImageFile);

        if ($result['success']) {
            header('Location: profiles.php?success=created');
            exit;
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Registry</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">Youth Registration</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Register New Youth Member</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Complete the three-step profiling system to register a new youth member (15-30 years old). Identify if they are OSY or Regular to unlock relevant programs.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Progress Stepper -->
<div class="relative flex items-center justify-between mb-12 px-2">
    <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-200 dark:bg-slate-700 -z-10"></div>
    <?php
    $steps = [
        ['id' => 1, 'label' => 'Personal Info', 'icon' => 'person'],
        ['id' => 2, 'label' => 'Education & Skills', 'icon' => 'school']
    ];
    foreach ($steps as $s):
    ?>
        <div class="flex flex-col items-center gap-3">
            <div id="step-icon-<?php echo $s['id']; ?>" class="<?php echo ($s['id'] == 1 ? 'bg-blue-900 text-white shadow-lg' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'); ?> w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all">
                <span class="material-symbols-outlined"><?php echo $s['icon']; ?></span>
            </div>
            <span id="step-label-<?php echo $s['id']; ?>" class="text-xs font-bold uppercase tracking-wider <?php echo ($s['id'] == 1 ? 'text-blue-900' : 'text-slate-500'); ?>">
                <?php echo $s['label']; ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Registration Form (Single page, JS-based steps) -->
<form method="POST" enctype="multipart/form-data" id="registrationForm" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm overflow-hidden border border-slate-200 dark:border-slate-700">
    <input type="hidden" name="register_member" value="1">

    <div class="p-8">
        <!-- STEP 1: Personal Information -->
        <div id="step-1" class="step-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Photo Upload -->
                <div class="flex flex-col items-center">
                    <div class="relative group mb-6">
                        <div id="photoPreviewContainer" class="w-48 h-48 rounded-xl bg-slate-100 dark:bg-slate-700 overflow-hidden flex items-center justify-center border-2 border-dashed border-slate-300 hover:border-blue-900 transition-colors cursor-pointer">
                            <input type="file" name="profile_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" id="imageUpload" onchange="previewImage(this, 'photoPreview')">
                            <div id="photoPlaceholder" class="text-center p-4">
                                <span class="material-symbols-outlined text-4xl text-slate-400">add_a_photo</span>
                                <p class="text-xs mt-2 font-medium text-slate-500">Upload Photo</p>
                            </div>
                            <img id="photoPreview" src="" alt="" class="hidden w-full h-full object-cover">
                        </div>
                        <button type="button" class="absolute -bottom-2 -right-2 bg-blue-900 text-white p-2 rounded-full shadow-lg" onclick="document.getElementById('imageUpload').click()">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                    </div>
                    <div class="w-full p-4 bg-slate-50 dark:bg-slate-700 rounded-xl">
                        <p class="text-xs uppercase font-bold text-slate-600 dark:text-slate-400 mb-2">Registration Status</p>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                            <span class="text-xs font-semibold text-yellow-700 dark:text-yellow-400">In Progress</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Form Fields -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Member Type Selection -->
                    <div class="space-y-2 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <label class="block text-sm font-bold text-blue-900 dark:text-blue-300 uppercase">Member Classification</label>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mb-3">This determines which features are available</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="profile_type" value="Regular" checked class="w-4 h-4 text-blue-900">
                                <span class="text-sm font-semibold text-blue-900 dark:text-blue-300">Regular Youth Member</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="profile_type" value="OSY" class="w-4 h-4 text-orange-600">
                                <span class="text-sm font-semibold text-blue-900 dark:text-blue-300">Out-of-School Youth (OSY)</span>
                            </label>
                        </div>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 italic">OSY members access exclusive employment matching and training programs</p>
                    </div>

                    <!-- Full Name Section -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest">Personal Information</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="first_name" placeholder="First Name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <input type="text" name="last_name" placeholder="Last Name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <input type="text" name="middle_name" placeholder="Middle Name (Optional)" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white md:col-span-2">
                        </div>
                    </div>

                    <!-- Birth & Age -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Birthdate</label>
                            <input type="date" name="birthdate" id="birthdate" required onchange="calculateAge()" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Age</label>
                            <input type="number" name="age" id="age" readonly class="w-full bg-slate-200 dark:bg-slate-600 border-none rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white mt-2 font-bold">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Gender</label>
                            <select name="gender" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Civil Status & Location -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Civil Status</label>
                            <select name="civil_status" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Solo Parent">Solo Parent</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Barangay</label>
                            <select name="barangay" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                                <option value="">Select Address</option>
                                <?php foreach ($barangays as $b): ?>
                                    <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Contact Number</label>
                        <div class="flex mt-2">
                            <span class="flex items-center px-4 bg-slate-200 dark:bg-slate-600 rounded-l-xl text-sm text-slate-600 dark:text-slate-400 font-bold">+63</span>
                            <input type="tel" name="contact_number" placeholder="917 123 4567" required class="flex-1 bg-slate-100 dark:bg-slate-700 border-none rounded-r-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Email & ID Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Email</label>
                            <input type="email" name="email" placeholder="email@example.com" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Government ID Type</label>
                            <select name="govt_id_type" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white mt-2">
                                <option value="">Select ID Type</option>
                                <?php foreach ($govtIdTypes as $idType): ?>
                                    <option value="<?php echo htmlspecialchars($idType); ?>"><?php echo htmlspecialchars($idType); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Government ID Details -->
                  
                </div>
            </div>
        </div>

        <!-- STEP 2: Education, Skills & Interests -->
        <div id="step-2" class="step-content hidden">
            <div class="space-y-6 max-w-3xl">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Education, Skills & Engagement</h2>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 block">Highest Educational Attainment</label>
                    <select name="education_level" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select Level</option>
                        <?php foreach ($eduLevels as $lvl): ?>
                            <option value="<?php echo htmlspecialchars($lvl); ?>"><?php echo htmlspecialchars($lvl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="reasonSection" class="hidden">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 block">Reason for Current Situation (Optional)</label>
                    <select name="reason_for_school" id="reason_for_school" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select Reason (Optional)</option>
                        <?php foreach ($reasons as $reason): ?>
                            <option value="<?php echo htmlspecialchars($reason); ?>"><?php echo htmlspecialchars($reason); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Non-OSY Engagement Status -->
                <div id="engagementSection" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 hidden">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 block">Current Engagement Status (Non-OSY Members)</label>
                    <select name="engagement_status" class="w-full bg-white dark:bg-slate-700 border border-green-200 dark:border-green-800 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-green-500 text-slate-900 dark:text-white">
                        <option value="">Select Status (Optional)</option>
                        <option value="Studying">Studying (Still in School/College)</option>
                        <option value="Working">Working (Employed)</option>
                        <option value="Self-Employed">Self-Employed</option>
                        <option value="Seeking Employment">Seeking Employment</option>
                        <option value="Unemployed">Unemployed</option>
                        <option value="Homemaker">Homemaker</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 block">Current Skills (e.g., Welding, Driving, Cooking)</label>
                    <textarea name="skills" rows="4" placeholder="List your existing skills separated by commas..." required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 block">Areas of Interest for Training</label>
                    <textarea name="interests" rows="4" placeholder="What skills would you like to learn? List them separated by commas..." required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="p-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                    <h3 class="font-bold text-blue-900 dark:text-blue-300 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined">info</span>
                        Program Benefits
                    </h3>
                    <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">Registered members automatically qualify for:</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
                            <span class="material-symbols-outlined text-base">school</span>
                            Skills Training Programs
                        </div>
                        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
                            <span class="material-symbols-outlined text-base">work</span>
                            Job Placement Services
                        </div>
                        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
                            <span class="material-symbols-outlined text-base">assessment</span>
                            Opportunity Matching
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Footer -->
    <div class="bg-slate-100 dark:bg-slate-700 p-6 flex justify-between items-center border-t border-slate-200 dark:border-slate-600">
        <div>
            <button type="button" id="prevBtn" onclick="changeStep(-1)" class="text-blue-900 hover:underline font-semibold hidden">
                ← Previous Step
            </button>
            <span id="startLabel" class="text-slate-400 font-semibold">← Start</span>
        </div>

        <div class="flex gap-3">
            <button type="button" id="nextBtn" onclick="changeStep(1)" class="inline-flex items-center gap-2 bg-blue-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-800 transition-all">
                Next: Education
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
            <button type="submit" id="submitBtn" class="hidden inline-flex items-center gap-2 bg-green-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-700 transition-all">
                Register Profile
                <span class="material-symbols-outlined text-base">check_circle</span>
            </button>
        </div>
    </div>
</form>

<script>
    let currentStep = 1;
    const totalSteps = 2;
    const stepNames = ['', 'Education & Skills'];

    function changeStep(direction) {
        const newStep = currentStep + direction;
        if (newStep < 1 || newStep > totalSteps) return;

        // Validate current step before moving forward
        if (direction > 0) {
            const currentFields = document.getElementById('step-' + currentStep).querySelectorAll('[required]');
            for (const field of currentFields) {
                if (!field.value) {
                    field.focus();
                    field.classList.add('ring-2', 'ring-red-500');
                    setTimeout(() => field.classList.remove('ring-2', 'ring-red-500'), 2000);
                    return;
                }
            }
        }

        // Update display
        document.getElementById('step-' + currentStep).classList.add('hidden');
        document.getElementById('step-' + newStep).classList.remove('hidden');

        // Update stepper icons
        for (let i = 1; i <= totalSteps; i++) {
            const icon = document.getElementById('step-icon-' + i);
            const label = document.getElementById('step-label-' + i);
            if (i <= newStep) {
                icon.className = 'bg-blue-900 text-white shadow-lg w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all';
                label.className = 'text-xs font-bold uppercase tracking-wider text-blue-900';
            } else {
                icon.className = 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all';
                label.className = 'text-xs font-bold uppercase tracking-wider text-slate-500';
            }
        }

        currentStep = newStep;

        // Update buttons
        document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
        document.getElementById('startLabel').classList.toggle('hidden', currentStep !== 1);
        document.getElementById('nextBtn').classList.toggle('hidden', currentStep === totalSteps);
        document.getElementById('submitBtn').classList.toggle('hidden', currentStep !== totalSteps);

        if (currentStep < totalSteps) {
            document.getElementById('nextBtn').innerHTML = 'Next: ' + stepNames[currentStep] + ' <span class="material-symbols-outlined text-base">arrow_forward</span>';
        }

        // Show engagement section on step 2
        if (currentStep === 2) {
            updateEngagementSection();
        }
    }

    function calculateAge() {
        const birthdate = document.getElementById('birthdate').value;
        if (!birthdate) return;

        const today = new Date();
        const birth = new Date(birthdate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }

        if (age < 15 || age > 30) {
            alert('Member must be between 15-30 years old');
            document.getElementById('birthdate').value = '';
            document.getElementById('age').value = '';
        } else {
            document.getElementById('age').value = age;
        }
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                document.getElementById('photoPlaceholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Monitor profile type changes to show/hide engagement and reason sections
    document.querySelectorAll('input[name="profile_type"]').forEach(radio => {
        radio.addEventListener('change', updateEngagementSection);
    });

    function updateEngagementSection() {
        const profileType = document.querySelector('input[name="profile_type"]:checked')?.value;
        const engagementSection = document.getElementById('engagementSection');
        const reasonSection = document.getElementById('reasonSection');

        if (profileType === 'Regular') {
            if (engagementSection) engagementSection.classList.remove('hidden');
            if (reasonSection) {
                reasonSection.classList.add('hidden');
                document.getElementById('reason_for_school').value = '';
            }
        } else {
            if (engagementSection) engagementSection.classList.add('hidden');
            if (reasonSection) reasonSection.classList.remove('hidden');
        }
    }
    
    // Call once on page load to set correct initial state based on default selection
    updateEngagementSection();

    // Show filename when govt ID uploaded
    document.getElementById('govtIdUpload')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            document.getElementById('govtIdLabel').textContent = this.files[0].name;
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>