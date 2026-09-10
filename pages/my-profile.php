<?php

/**
 * Youth Personal Profile Management
 *
 * Allows youth to view and edit their own profile information.
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'youth') {
    header('Location: dashboard.php?error=Unauthorized access');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../Classes/Reference.php';

if (!function_exists('osy_ref_values')) {
    function osy_ref_values($items, $current = '')
    {
        $out = [];
        foreach ((array) $items as $item) {
            if (is_array($item)) {
                $val = trim((string) ($item['value'] ?? $item['label'] ?? ''));
            } else {
                $val = trim((string) $item);
            }
            if ($val !== '' && !in_array($val, $out, true)) {
                $out[] = $val;
            }
        }
        $current = trim((string) $current);
        if ($current !== '' && !in_array($current, $out, true)) {
            array_unshift($out, $current);
        }
        return $out;
    }
}

if (!function_exists('osy_display')) {
    function osy_display($value, $fallback = 'Not provided')
    {
        $value = trim((string) ($value ?? ''));
        return $value !== '' ? $value : $fallback;
    }
}

$osyProfile = new OSYProfile($database);
$notification = new Notification($database);
$message = '';
$messageType = '';
$isEditing = isset($_GET['edit']) && $_GET['edit'] === '1';

$profile = $osyProfile->getByUserId($_SESSION['user_id']);

if (!$profile) {
    echo '<div class="max-w-xl mx-auto mt-16 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-10">';
    echo '<span class="material-symbols-outlined text-5xl text-slate-400">person_off</span>';
    echo '<p class="mt-4 text-slate-600 dark:text-slate-300">No profile found. Please complete your registration first.</p>';
    echo '<a href="youth-signup.php" class="mt-6 inline-block px-6 py-2.5 bg-blue-900 text-white rounded-xl font-semibold hover:bg-blue-800">Complete Registration</a>';
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This form has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
        $isEditing = true;
    } else {
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $submittedDateOfBirth = trim($_POST['date_of_birth'] ?? $profile['date_of_birth'] ?? '');
        $calculatedAge = $profile['age'];

        if ($firstName === '' || $lastName === '') {
            $message = 'First name and last name are required.';
            $messageType = 'error';
            $isEditing = true;
        } else {
            if ($submittedDateOfBirth !== '') {
                $birthDate = DateTime::createFromFormat('Y-m-d', $submittedDateOfBirth);
                $today = new DateTime('today');
                if ($birthDate && $birthDate->format('Y-m-d') === $submittedDateOfBirth && $birthDate <= $today) {
                    $calculatedAge = $birthDate->diff($today)->y;
                } else {
                    $message = 'Please enter a valid date of birth.';
                    $messageType = 'error';
                    $isEditing = true;
                }
            }

            if ($messageType !== 'error') {
                $data = [
                    'first_name' => $firstName,
                    'middle_name' => trim((string) ($_POST['middle_name'] ?? '')),
                    'last_name' => $lastName,
                    'suffix' => trim((string) ($_POST['suffix'] ?? '')),
                    'email' => $email !== '' ? $email : ($profile['email'] ?? ''),
                    'phone' => $phone,
                    'age' => $calculatedAge,
                    'date_of_birth' => $submittedDateOfBirth,
                    'gender' => $_POST['gender'] ?? $profile['gender'],
                    'civil_status' => $_POST['civil_status'] ?? $profile['civil_status'],
                    'purok' => trim($_POST['purok'] ?? $profile['purok'] ?? ''),
                    'address' => trim($_POST['purok'] ?? $profile['purok'] ?? ''),
                    'province' => Location::isValidProvince(trim($_POST['province'] ?? '')) ? trim($_POST['province']) : ($profile['province'] ?? Location::DEFAULT_PROVINCE),
                    'municipality' => Location::isValidMunicipality(trim($_POST['province'] ?? Location::DEFAULT_PROVINCE), trim($_POST['municipality'] ?? '')) ? trim($_POST['municipality']) : ($profile['municipality'] ?? Location::DEFAULT_MUNICIPALITY),
                    'barangay' => $profile['barangay'],
                    'education_level' => $_POST['education_level'] ?? $profile['education_level'],
                    'occupation' => trim((string) ($_POST['occupation'] ?? '')),
                    'reason_for_not_in_school' => trim((string) ($_POST['reason_for_not_in_school'] ?? '')),
                    'engagement_status' => $_POST['engagement_status'] ?? $profile['engagement_status'],
                    'govt_id_type' => $_POST['govt_id_type'] ?? $profile['govt_id_type'],
                    'govt_id_number' => trim((string) ($_POST['govt_id_number'] ?? '')),
                    'primary_skill' => trim((string) ($_POST['primary_skill'] ?? '')),
                    'skills' => trim((string) ($_POST['skills'] ?? '')),
                    'interests' => trim((string) ($_POST['interests'] ?? '')),
                ];

                $profileImageFile = !empty($_FILES['profile_image']['name']) ? $_FILES['profile_image'] : null;
                $govtIdImageFile = !empty($_FILES['govt_id_image']['name']) ? $_FILES['govt_id_image'] : null;
                $certificationFile = !empty($_FILES['certification_file']['name']) ? $_FILES['certification_file'] : null;

                $result = $osyProfile->update($profile['id'], $data, $profileImageFile, $govtIdImageFile, $certificationFile);
                if ($result['success']) {
                    $requestedBarangay = trim($_POST['barangay'] ?? '');
                    $transferRequested = false;
                    if ($requestedBarangay !== '' && $requestedBarangay !== $profile['barangay']) {
                        $transfer = $osyProfile->requestBarangayTransfer(
                            $profile['id'],
                            $requestedBarangay,
                            $_SESSION['user_id'],
                            trim($_POST['transfer_remark'] ?? '')
                        );
                        if (!$transfer['success']) {
                            $message = $transfer['message'];
                            $messageType = 'error';
                            $isEditing = true;
                        } else {
                            $transferRequested = true;
                            $receivingSk = $database->fetchOne(
                                "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status = 'Active' LIMIT 1",
                                [$requestedBarangay],
                                's'
                            );
                            if ($receivingSk) {
                                $notification->sendToUser(
                                    $receivingSk['id'],
                                    'Youth Barangay Transfer Awaiting Review',
                                    "{$firstName} {$lastName} is requesting a transfer from {$profile['barangay']} to {$requestedBarangay}.",
                                    'System',
                                    $profile['created_by']
                                );
                            }
                        }
                    }

                    $resubmitted = false;
                    if (in_array($profile['verification_status'], ['Rejected', 'Declined', 'Action Required'], true)) {
                        $osyProfile->setVerificationStatus($profile['id'], 'Pending', 'Resubmitted by user');
                        $database->execute("UPDATE users SET status = 'Pending' WHERE id = ?", [$profile['created_by']], 'i');
                        $_SESSION['status'] = 'Pending';
                        $resubmitted = true;
                    }

                    if ($resubmitted) {
                        $skChairman = $database->fetchOne(
                            "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status = 'Active' LIMIT 1",
                            [$profile['barangay']],
                            's'
                        );
                        if ($skChairman) {
                            $notification->sendToUser(
                                $skChairman['id'],
                                'Resubmitted Youth Profile',
                                "Youth member {$firstName} {$lastName} has updated and resubmitted their profile for verification.",
                                'System',
                                $profile['created_by']
                            );
                        }
                    }

                    $_SESSION['fullname'] = trim($firstName . ' ' . $lastName);
                    if ($email !== '') {
                        $_SESSION['email'] = $email;
                    }

                    if ($messageType !== 'error') {
                        $message = 'Profile updated successfully.'
                            . ($transferRequested ? ' Your barangay transfer is awaiting verification by the receiving SK Chairman.' : '')
                            . ($resubmitted ? ' Your profile has been resubmitted for verification.' : '');
                        $messageType = 'success';
                        $isEditing = false;
                    }
                    $profile = $osyProfile->getById($profile['id']);
                } else {
                    $message = $result['message'] ?? 'Error updating profile';
                    $messageType = 'error';
                    $isEditing = true;
                }
            }
        }
    }
}

$ref = new Reference($database);
$barangays = osy_ref_values($ref->getByCategory('barangay'), $profile['barangay'] ?? '');
$eduLevels = osy_ref_values($ref->getByCategory('education_level'), $profile['education_level'] ?? '');
if (empty($eduLevels)) {
    $eduLevels = osy_ref_values([
        'Elementary Undergraduate',
        'Elementary Graduate',
        'High School Undergraduate',
        'High School Graduate',
        'College Undergraduate',
        'College Graduate',
        'Vocational',
        'No Formal Education',
    ], $profile['education_level'] ?? '');
}
$govtIdTypes = osy_ref_values($ref->getByCategory('govt_id_type'), $profile['govt_id_type'] ?? '');
if (empty($govtIdTypes)) {
    $govtIdTypes = osy_ref_values([
        'National ID',
        'Passport',
        "Driver's License",
        'Voter ID',
        'PRC License',
        'Postal ID',
        'SSS',
        'PhilHealth',
        'Pag-IBIG',
    ], $profile['govt_id_type'] ?? '');
}
$skillSuggestions = osy_ref_values($ref->getByCategory('skills'), $profile['primary_skill'] ?? '');
$reasonSuggestions = osy_ref_values($ref->getByCategory('reason'), '');

$engagementOptions = osy_ref_values([
    'Unemployed',
    'Self-Employed',
    'Part-Time Work',
    'Full-Time Work',
    'In Training',
    'Studying',
    'Working',
    'Seeking Employment',
], $profile['engagement_status'] ?? '');

$pendingTransfer = null;
try {
    $pendingTransfer = $database->fetchOne(
        "SELECT * FROM youth_barangay_transfers WHERE profile_id = ? AND status = 'Pending' LIMIT 1",
        [$profile['id']],
        'i'
    );
} catch (Exception $e) {
    $pendingTransfer = null;
}

$verificationStatus = $profile['verification_status'] ?? 'Pending';
$needsAction = in_array($verificationStatus, ['Declined', 'Rejected', 'Action Required'], true);
$isVerified = $verificationStatus === 'Verified';
$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['middle_name'] ?? '') . ' ' . ($profile['last_name'] ?? '') . ' ' . ($profile['suffix'] ?? ''));
$photoSrc = !empty($profile['image_path']) ? '../' . ltrim($profile['image_path'], '/') : '';
$inputClass = 'w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 py-3 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-700';
$labelClass = 'block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5';

$matching = new Matching($database);
$applications = $isVerified ? $matching->getMatchesForOSY($profile['id']) : [];
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 tracking-wider uppercase mb-2">
                <a href="dashboard.php" class="hover:text-blue-800">Account</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-blue-900">My Profile</span>
            </nav>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-blue-900 tracking-tight"><?php echo $isEditing ? 'Edit Profile' : 'My Profile'; ?></h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1"><?php echo $isEditing ? 'Update every detail of your youth record, including documents.' : 'Review your personal record and keep it complete for matching and verification.'; ?></p>
        </div>
        <?php if (!$isEditing): ?>
            <a href="my-profile.php?edit=1" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-900/20">
                <span class="material-symbols-outlined text-base">edit</span>
                Edit all information
            </a>
        <?php else: ?>
            <a href="my-profile.php" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white rounded-xl font-bold text-sm">
                Cancel
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl border <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
            <p class="flex items-start gap-2 font-medium">
                <span class="material-symbols-outlined text-base mt-0.5"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($needsAction && !empty($profile['verification_remark'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-8">
            <div class="flex gap-4">
                <span class="material-symbols-outlined text-red-600 text-2xl">warning</span>
                <div>
                    <h4 class="font-bold text-red-900 dark:text-red-300 mb-1">Action required</h4>
                    <p class="text-red-800 dark:text-red-200 text-sm mb-3">Your profile was declined. Update the information below and save to resubmit for verification.</p>
                    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border-l-4 border-red-500 text-sm italic text-slate-700 dark:text-slate-300">
                        <?php echo htmlspecialchars($profile['verification_remark']); ?>
                    </div>
                    <?php if (!$isEditing): ?>
                        <a href="my-profile.php?edit=1" class="inline-flex mt-4 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">Edit and resubmit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($pendingTransfer): ?>
        <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 p-5">
            <p class="font-semibold text-amber-900 dark:text-amber-200">Barangay transfer pending</p>
            <p class="text-sm text-amber-800 dark:text-amber-300 mt-1">
                Requested move from <?php echo htmlspecialchars($pendingTransfer['from_barangay']); ?>
                to <?php echo htmlspecialchars($pendingTransfer['to_barangay']); ?>.
                Your current barangay stays the same until the receiving SK Chairman verifies it.
            </p>
        </div>
    <?php endif; ?>

    <?php if ($isEditing): ?>
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="update_profile" value="1">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Photo and personal details</h2>
                    <p class="text-sm text-slate-500">This is the identity information used for verification.</p>
                </div>
                <div class="p-6 grid grid-cols-1 lg:grid-cols-[200px_1fr] gap-8">
                    <div class="text-center">
                        <div class="w-36 h-36 mx-auto rounded-full overflow-hidden border-4 border-white shadow-md bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                            <?php if ($photoSrc): ?>
                                <img id="profilePhotoPreview" src="<?php echo htmlspecialchars($photoSrc); ?>" alt="Profile photo" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img id="profilePhotoPreview" src="" alt="" class="w-full h-full object-cover hidden">
                                <span id="profilePhotoPlaceholder" class="material-symbols-outlined text-5xl text-slate-400">person</span>
                            <?php endif; ?>
                        </div>
                        <label class="<?php echo $labelClass; ?> mt-4">Profile photo</label>
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/jpeg,image/png,image/gif" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-blue-100 file:text-blue-800 file:font-semibold">
                        <p class="mt-1 text-[11px] text-slate-500">JPG, PNG, or GIF. Max 5MB.</p>
                    </div>
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="<?php echo $labelClass; ?>">First name *</label>
                                <input type="text" name="first_name" required maxlength="50" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Middle name</label>
                                <input type="text" name="middle_name" maxlength="50" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Last name *</label>
                                <input type="text" name="last_name" required maxlength="50" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Suffix</label>
                                <select name="suffix" class="<?php echo $inputClass; ?>">
                                    <option value="">None</option>
                                    <?php foreach (Location::suffixes() as $suffixOption): ?>
                                        <option value="<?php echo htmlspecialchars($suffixOption); ?>" <?php echo (($profile['suffix'] ?? '') === $suffixOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($suffixOption); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="<?php echo $labelClass; ?>">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Phone</label>
                                <input type="tel" name="phone" maxlength="20" placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="<?php echo $labelClass; ?>">Date of birth</label>
                                <input type="date" name="date_of_birth" id="profileDob" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Age</label>
                                <input type="number" name="age" id="profileAge" readonly value="<?php echo htmlspecialchars((string) ($profile['age'] ?? '')); ?>" class="<?php echo $inputClass; ?> bg-slate-100 dark:bg-slate-700 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Gender</label>
                                <select name="gender" class="<?php echo $inputClass; ?>">
                                    <option value="">Select</option>
                                    <?php foreach (['Male', 'Female', 'Other'] as $genderOption): ?>
                                        <option value="<?php echo $genderOption; ?>" <?php echo (($profile['gender'] ?? '') === $genderOption) ? 'selected' : ''; ?>><?php echo $genderOption; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="<?php echo $labelClass; ?>">Civil status</label>
                                <select name="civil_status" class="<?php echo $inputClass; ?>">
                                    <?php foreach (['Single', 'Married', 'Widowed', 'Solo Parent'] as $civilOption): ?>
                                        <option value="<?php echo $civilOption; ?>" <?php echo (($profile['civil_status'] ?? '') === $civilOption) ? 'selected' : ''; ?>><?php echo $civilOption; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Address</h2>
                    <p class="text-sm text-slate-500">Select province, municipality, and barangay. Enter purok manually. Changing barangay starts a transfer request.</p>
                </div>
                <div class="p-6 space-y-4">
                    <?php
                    $selectedProvince = $profile['province'] ?? Location::DEFAULT_PROVINCE;
                    $selectedMunicipality = $profile['municipality'] ?? Location::DEFAULT_MUNICIPALITY;
                    $selectedBarangay = $profile['barangay'] ?? '';
                    $selectedPurok = $profile['purok'] ?? ($profile['address'] ?? '');
                    $purokRequired = true;
                    $lockBarangay = $pendingTransfer ? ($profile['barangay'] ?? '') : null;
                    require __DIR__ . '/../includes/location-fields.php';
                    ?>
                    <?php if ($pendingTransfer): ?>
                        <p class="text-xs text-amber-700">You already have a pending transfer request.</p>
                    <?php else: ?>
                        <p class="text-xs text-slate-500">Changing barangay does not move you immediately. The receiving SK Chairman must verify the transfer.</p>
                    <?php endif; ?>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Reason for transfer (optional)</label>
                        <textarea name="transfer_remark" rows="2" <?php echo $pendingTransfer ? 'disabled' : ''; ?> placeholder="Why are you requesting a barangay transfer?" class="<?php echo $inputClass; ?>"></textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Education and current status</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="<?php echo $labelClass; ?>">Educational attainment</label>
                        <select name="education_level" class="<?php echo $inputClass; ?>">
                            <option value="">Select educational attainment</option>
                            <?php foreach ($eduLevels as $eduOption): ?>
                                <option value="<?php echo htmlspecialchars($eduOption); ?>" <?php echo (($profile['education_level'] ?? '') === $eduOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($eduOption); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Occupation</label>
                        <input type="text" name="occupation" maxlength="150" value="<?php echo htmlspecialchars($profile['occupation'] ?? ''); ?>" placeholder="e.g., Farmer, Student, Vendor" class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Engagement / work status</label>
                        <select name="engagement_status" class="<?php echo $inputClass; ?>">
                            <option value="">Select status</option>
                            <?php foreach ($engagementOptions as $statusOption): ?>
                                <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo (($profile['engagement_status'] ?? '') === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="<?php echo $labelClass; ?>">Reason not in school (if applicable)</label>
                        <input list="reasonSuggestions" type="text" name="reason_for_not_in_school" value="<?php echo htmlspecialchars($profile['reason_for_not_in_school'] ?? ''); ?>" placeholder="Financial problem, employment, family, etc." class="<?php echo $inputClass; ?>">
                        <datalist id="reasonSuggestions">
                            <?php foreach ($reasonSuggestions as $reasonOption): ?>
                                <option value="<?php echo htmlspecialchars($reasonOption); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Skills, certifications, and goals</h2>
                    <p class="text-sm text-slate-500">These fields are used to match you with jobs and training.</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="<?php echo $labelClass; ?>">Primary skill</label>
                        <input list="skillSuggestions" type="text" name="primary_skill" maxlength="100" value="<?php echo htmlspecialchars($profile['primary_skill'] ?? ''); ?>" placeholder="e.g. Welding, Culinary, Computer Literacy" class="<?php echo $inputClass; ?>">
                        <datalist id="skillSuggestions">
                            <?php foreach ($skillSuggestions as $skillOption): ?>
                                <option value="<?php echo htmlspecialchars($skillOption); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Skills / certifications</label>
                        <textarea name="skills" rows="4" placeholder="TESDA NC II, Food Safety, Computer Programming" class="<?php echo $inputClass; ?>"><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Interests / career goals</label>
                        <textarea name="interests" rows="4" placeholder="What work or training are you looking for?" class="<?php echo $inputClass; ?>"><?php echo htmlspecialchars($profile['interests'] ?? ''); ?></textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Government ID and supporting documents</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="<?php echo $labelClass; ?>">Government ID type</label>
                        <select name="govt_id_type" class="<?php echo $inputClass; ?>">
                            <option value="">Select ID type</option>
                            <?php foreach ($govtIdTypes as $idOption): ?>
                                <option value="<?php echo htmlspecialchars($idOption); ?>" <?php echo (($profile['govt_id_type'] ?? '') === $idOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($idOption); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Government ID number</label>
                        <input type="text" name="govt_id_number" maxlength="100" value="<?php echo htmlspecialchars($profile['govt_id_number'] ?? ''); ?>" class="<?php echo $inputClass; ?>">
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Update government ID file</label>
                        <input type="file" name="govt_id_image" accept="image/jpeg,image/png,image/gif,application/pdf" class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-blue-100 file:text-blue-800 file:font-semibold">
                        <?php if (!empty($profile['govt_id_image'])): ?>
                            <a href="<?php echo htmlspecialchars('../' . ltrim($profile['govt_id_image'], '/')); ?>" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm font-semibold text-blue-700 hover:text-blue-800">
                                <span class="material-symbols-outlined text-base">open_in_new</span> View current ID
                            </a>
                        <?php else: ?>
                            <p class="mt-2 text-xs text-slate-500">No ID file uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="<?php echo $labelClass; ?>">Update certification / supporting document</label>
                        <input type="file" name="certification_file" accept="image/jpeg,image/png,image/gif,application/pdf" class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:bg-blue-100 file:text-blue-800 file:font-semibold">
                        <?php if (!empty($profile['identity_document_path'])): ?>
                            <a href="<?php echo htmlspecialchars('../' . ltrim($profile['identity_document_path'], '/')); ?>" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm font-semibold text-blue-700 hover:text-blue-800">
                                <span class="material-symbols-outlined text-base">open_in_new</span> View current document
                            </a>
                        <?php else: ?>
                            <p class="mt-2 text-xs text-slate-500">No certification file uploaded yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <div class="sticky bottom-4 z-20 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-lg">
                <a href="my-profile.php" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white rounded-xl font-bold text-sm text-center">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-blue-900 hover:bg-blue-800 text-white rounded-xl font-bold text-sm inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">save</span>
                    Save all changes
                </button>
            </div>
        </form>
    <?php else: ?>
        <div class="bg-gradient-to-br from-blue-900 to-blue-700 rounded-2xl p-6 sm:p-8 text-white mb-6 shadow-lg">
            <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white/20 bg-white/10 flex items-center justify-center shrink-0">
                    <?php if ($photoSrc): ?>
                        <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="Profile photo" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="material-symbols-outlined text-5xl text-white/70">person</span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl sm:text-3xl font-extrabold truncate"><?php echo htmlspecialchars(osy_display($fullName, 'Youth member')); ?></h2>
                    <p class="text-blue-100 mt-1"><?php echo htmlspecialchars(osy_display($profile['email'] ?? '')); ?> · <?php echo htmlspecialchars(osy_display($profile['phone'] ?? '')); ?></p>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold <?php echo $isVerified ? 'bg-emerald-400 text-emerald-950' : ($needsAction ? 'bg-red-200 text-red-900' : 'bg-amber-200 text-amber-950'); ?>">
                            <?php echo htmlspecialchars($verificationStatus); ?>
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15"><?php echo htmlspecialchars($_SESSION['status'] ?? 'Pending'); ?> account</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/15"><?php echo htmlspecialchars(osy_display($profile['barangay'] ?? '', 'No barangay')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Registration</p>
                <p class="mt-2 text-xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($verificationStatus); ?></p>
                <p class="text-xs text-slate-500 mt-1"><?php echo $isVerified ? 'Approved by SK Chairman' : ($needsAction ? 'Please correct and resubmit' : 'Awaiting SK Chairman review'); ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Account access</p>
                <p class="mt-2 text-xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['status'] ?? 'Pending'); ?></p>
                <p class="text-xs text-slate-500 mt-1"><?php echo (($_SESSION['status'] ?? '') === 'Active') ? 'You can use all youth features' : 'Limited access until approved'; ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Last updated</p>
                <p class="mt-2 text-xl font-bold text-slate-900 dark:text-white"><?php echo !empty($profile['updated_at']) ? date('M d, Y', strtotime($profile['updated_at'])) : '—'; ?></p>
                <p class="text-xs text-slate-500 mt-1">Keep this current for better matching</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-blue-800">badge</span> Personal information</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Full name</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($fullName)); ?></dd></div>
                    <div><dt class="text-slate-500">Suffix</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['suffix'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Date of birth</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo !empty($profile['date_of_birth']) ? htmlspecialchars(date('F d, Y', strtotime($profile['date_of_birth']))) : 'Not provided'; ?></dd></div>
                    <div><dt class="text-slate-500">Age</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['age'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Gender</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['gender'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Civil status</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['civil_status'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Phone</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['phone'] ?? '')); ?></dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Email</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1 break-all"><?php echo htmlspecialchars(osy_display($profile['email'] ?? '')); ?></dd></div>
                </dl>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-blue-800">home</span> Address</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm">
                    <div><dt class="text-slate-500">Purok</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['purok'] ?? ($profile['address'] ?? ''))); ?></dd></div>
                    <div><dt class="text-slate-500">Barangay</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['barangay'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Municipality</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['municipality'] ?? Location::DEFAULT_MUNICIPALITY)); ?></dd></div>
                    <div><dt class="text-slate-500">Province</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['province'] ?? Location::DEFAULT_PROVINCE)); ?></dd></div>
                </dl>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-blue-800">school</span> Education and status</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-500">Educational attainment</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['education_level'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Occupation</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['occupation'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Engagement</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['engagement_status'] ?? '')); ?></dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Reason not in school</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['reason_for_not_in_school'] ?? '')); ?></dd></div>
                </dl>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-blue-800">workspace_premium</span> Skills and goals</h3>
                <dl class="grid grid-cols-1 gap-4 text-sm">
                    <div><dt class="text-slate-500">Primary skill</dt><dd class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['primary_skill'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Skills / certifications</dt><dd class="font-medium text-slate-900 dark:text-white mt-1 whitespace-pre-line"><?php echo htmlspecialchars(osy_display($profile['skills'] ?? '')); ?></dd></div>
                    <div><dt class="text-slate-500">Interests / career goals</dt><dd class="font-medium text-slate-900 dark:text-white mt-1 whitespace-pre-line"><?php echo htmlspecialchars(osy_display($profile['interests'] ?? '')); ?></dd></div>
                </dl>
            </section>
        </div>

        <section class="mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-blue-800">id_card</span> Documents</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/40 p-4">
                    <p class="text-slate-500">Government ID</p>
                    <p class="font-semibold text-slate-900 dark:text-white mt-1"><?php echo htmlspecialchars(osy_display($profile['govt_id_type'] ?? '')); ?></p>
                    <p class="text-slate-600 dark:text-slate-300 mt-1">No. <?php echo htmlspecialchars(osy_display($profile['govt_id_number'] ?? '')); ?></p>
                    <?php if (!empty($profile['govt_id_image'])): ?>
                        <a href="<?php echo htmlspecialchars('../' . ltrim($profile['govt_id_image'], '/')); ?>" target="_blank" class="inline-flex items-center gap-1 mt-3 text-blue-700 font-semibold">View ID file</a>
                    <?php endif; ?>
                </div>
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/40 p-4">
                    <p class="text-slate-500">Certification / supporting document</p>
                    <?php if (!empty($profile['identity_document_path'])): ?>
                        <a href="<?php echo htmlspecialchars('../' . ltrim($profile['identity_document_path'], '/')); ?>" target="_blank" class="inline-flex items-center gap-1 mt-3 text-blue-700 font-semibold">View file</a>
                    <?php else: ?>
                        <p class="font-semibold text-slate-900 dark:text-white mt-1">Not provided</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ($isVerified): ?>
            <section class="mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">My applications</h3>
                    <p class="text-sm text-slate-500">Opportunity matches linked to this profile.</p>
                </div>
                <div class="p-6">
                    <?php if (!empty($applications)): ?>
                        <div class="space-y-3">
                            <?php foreach ($applications as $app): ?>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-600">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($app['title'] ?? $app['opp_title'] ?? 'Opportunity'); ?></p>
                                        <p class="text-sm text-slate-500">Match score: <?php echo isset($app['match_score']) ? round((float) $app['match_score']) : 0; ?>%</p>
                                    </div>
                                    <span class="self-start px-3 py-1 rounded-full text-xs font-bold <?php echo ($app['status'] ?? '') === 'Accepted' ? 'bg-green-100 text-green-700' : (($app['status'] ?? '') === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); ?>">
                                        <?php echo htmlspecialchars($app['status'] ?? 'Pending'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-slate-500">You have not applied to any opportunities yet. <a href="opportunities.php" class="text-blue-700 font-semibold hover:underline">Browse opportunities</a></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($isEditing): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var dob = document.getElementById('profileDob');
    var age = document.getElementById('profileAge');
    if (dob && age) {
        dob.addEventListener('change', function () {
            if (!dob.value) { age.value = ''; return; }
            var birth = new Date(dob.value + 'T00:00:00');
            var today = new Date();
            var years = today.getFullYear() - birth.getFullYear();
            var monthDifference = today.getMonth() - birth.getMonth();
            if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birth.getDate())) years--;
            age.value = years >= 0 ? years : '';
        });
    }
    var photoInput = document.getElementById('profileImageInput');
    var preview = document.getElementById('profilePhotoPreview');
    var placeholder = document.getElementById('profilePhotoPlaceholder');
    if (photoInput && preview) {
        photoInput.addEventListener('change', function () {
            var file = photoInput.files && photoInput.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        });
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
