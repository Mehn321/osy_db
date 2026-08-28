<?php

/**
 * Enhanced Public Youth Sign Up
 * 
 * Allows new youth to self-register with full profile information and document upload.
 * Creates both a user account (role=youth, status=Pending) and an osy_profile 
 * (verification_status=Pending) that requires SK Chairman approval.
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/OSYProfile.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Reference.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . (rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') === '/' ? '' : rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')) . '/dashboard.php');
    exit;
}

$user = new User($database);
$osyProfile = new OSYProfile($database);
$auditLog = new AuditLog($database);

$message = '';
$messageType = 'success';
$errors = [];
$currentStep = 1;

// Initialize all form variables to prevent undefined variable warnings
$username = '';
$email = '';
$password = '';
$confirmPassword = '';
$firstName = '';
$middleName = '';
$lastName = '';
$gender = '';
$dateOfBirth = '';
$address = '';
$barangay = '';
$phone = '';
$age = '';
$educationLevel = '';
$civilStatus = '';
$primarySkill = '';
$certifications = '';
$interests = '';
$reasonNotInSchool = '';
$engagementStatus = '';
$govtIdType = '';
$govtIdNumber = '';
$govtIdImage = '';
$profileImage = '';
$consentAccepted = 0;
$dataPrivacyAccepted = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $errors[] = 'This form has already been submitted or the session expired. Please refresh the page and try again.';
    } else {
        // Validate required fields
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $dateOfBirth = $_POST['date_of_birth'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $age = trim($_POST['age'] ?? '');
        $educationLevel = trim($_POST['education_level'] ?? '');
        $civilStatus = $_POST['civil_status'] ?? '';
        $primarySkill = trim($_POST['primary_skill'] ?? '');
        $certifications = trim($_POST['certifications'] ?? '');
        $interests = trim($_POST['interests'] ?? '');
        $reasonNotInSchool = trim($_POST['reason_not_in_school'] ?? '');
        $engagementStatus = trim($_POST['engagement_status'] ?? '');
        $govtIdType = $_POST['govt_id_type'] ?? '';
        $govtIdNumber = trim($_POST['govt_id_number'] ?? '');
        $consentAccepted = isset($_POST['consent_accepted']) ? 1 : 0;
        $dataPrivacyAccepted = isset($_POST['data_privacy_accepted']) ? 1 : 0;

        // Step 1: Account & Identity Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 4) {
            $errors[] = 'Username must be at least 4 characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters and include letters, numbers, and symbols.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }

        if (empty($gender)) {
            $errors[] = 'Gender is required.';
        }

        if (empty($dateOfBirth)) {
            $errors[] = 'Date of birth is required.';
        } else {
            $dobObject = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
            if ($dobObject) {
                $age = $dobObject->diff(new DateTime('now'))->y;
            }
        }

        if (empty($address)) {
            $errors[] = 'Address is required.';
        }

        if (empty($barangay)) {
            $errors[] = 'Barangay is required. Please select your barangay.';
        }

        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }

        // Step 2: Profile Information Validation
        if (empty($educationLevel)) {
            $errors[] = 'Education level is required.';
        }

        if (empty($civilStatus)) {
            $errors[] = 'Civil status is required.';
        }

        // Step 3: Document Upload Validation
        $idUploadFile = null;
        $profileImageUploadFile = null;
        $certificationUploadFile = null;

        if (!empty($_FILES['govt_id_image']['name']) || !empty($_FILES['profile_image']['name']) || !empty($_FILES['certification_file']['name'])) {
            // At least one document must be uploaded
            if (!empty($_FILES['govt_id_image']['name'])) {
                if ($_FILES['govt_id_image']['error'] === UPLOAD_ERR_OK) {
                    $idUploadFile = $_FILES['govt_id_image'];
                    if (empty($govtIdType)) {
                        $errors[] = 'Government ID type is required when uploading ID.';
                    }
                    if (empty($govtIdNumber)) {
                        $errors[] = 'Government ID number is required when uploading ID.';
                    }
                } elseif ($_FILES['govt_id_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Error uploading government ID image.';
                }
            }

            if (!empty($_FILES['profile_image']['name'])) {
                if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $profileImageUploadFile = $_FILES['profile_image'];
                } elseif ($_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Error uploading profile image.';
                }
            }

            if (!empty($_FILES['certification_file']['name'])) {
                if ($_FILES['certification_file']['error'] === UPLOAD_ERR_OK) {
                    $certificationUploadFile = $_FILES['certification_file'];
                } elseif ($_FILES['certification_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Error uploading certification document.';
                }
            }
        } else {
            $errors[] = 'At least one document (Government ID, Certificate of Residency/Profile Image, or Certification Document) must be uploaded for verification.';
        }

        // Consent Validation
        if (!$consentAccepted) {
            $errors[] = 'You must accept the terms and conditions.';
        }

        if (!$dataPrivacyAccepted) {
            $errors[] = 'You must accept the data privacy notice.';
        }

        // If no validation errors, proceed with registration
        if (empty($errors)) {
            try {
                if (!$database->beginTransaction()) {
                    throw new Exception('Unable to start registration transaction.');
                }

                // 1. Create user account with role=youth, status=Pending
                $userResult = $user->register($username, $email, $password, $firstName . ' ' . $lastName, 'youth');

                if (!$userResult['success']) {
                    throw new Exception($userResult['message']);
                }

                $userId = $userResult['user_id'];

                // 1b. Also save the barangay on the users table for SK lookup
                $database->execute(
                    "UPDATE users SET barangay = ? WHERE id = ?",
                    [$barangay, $userId],
                    "si"
                );

                // 2. Create osy_profile with verification_status=Pending
                $profileData = [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'age' => !empty($age) ? (int) $age : null,
                    'gender' => $gender,
                    'date_of_birth' => $dateOfBirth,
                    'education_level' => $educationLevel,
                    'civil_status' => $civilStatus,
                    'barangay' => $barangay,
                    'primary_skill' => $primarySkill,
                    'skills' => $certifications,
                    'interests' => $interests,
                    'reason_for_not_in_school' => $reasonNotInSchool,
                    'engagement_status' => $engagementStatus,
                    'govt_id_type' => $govtIdType,
                    'govt_id_number' => $govtIdNumber,
                    'profile_type' => 'OSY',
                    'status' => 'Active', // Profile itself is active
                    'registration_status' => 'Submitted',
                    'verification_status' => 'Pending', // Awaits SK Chairman approval
                    'consent_accepted' => $consentAccepted,
                    'created_by' => $userId
                ];

                $profileResult = $osyProfile->create($profileData, $profileImageUploadFile, $idUploadFile, $certificationUploadFile);

                if (!$profileResult['success']) {
                    throw new Exception($profileResult['message']);
                }

                $profileId = $profileResult['id'];

                // 3. Log the signup action
                $auditLog->logAction(
                    $userId,
                    'youth',
                    'Youth self-registered with full profile for approval',
                    'OSYProfile',
                    $profileId,
                    json_encode(['barangay' => $barangay, 'id_type' => $govtIdType])
                );

                $database->commit();

                // 4. Send notification to the active SK Chairman of this barangay
                $skChairman = $database->fetchOne(
                    "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status = 'Active' LIMIT 1",
                    [$barangay],
                    "s"
                );
                if ($skChairman) {
                    $notifObj = new Notification($database);
                    $notifObj->sendToUser(
                        $skChairman['id'],
                        'New Youth Registration awaiting review',
                        "A new youth member ($firstName $lastName) has self-registered in barangay $address and is awaiting verification.",
                        'System',
                        $userId
                    );
                }

                // Success message with next steps
                $message = 'Sign up successful! Your registration has been submitted for approval. Your SK Chairman will review your information and documents. Please check back for updates.';
                $messageType = 'success';

                // Clear form
                $username = $email = $password = $confirmPassword = $firstName = $middleName = $lastName = '';
                $gender = $dateOfBirth = $address = $phone = $educationLevel = $civilStatus = '';
                $primarySkill = $certifications = $interests = $reasonNotInSchool = $engagementStatus = $age = '';
                $govtIdType = $govtIdNumber = '';
                $consentAccepted = $dataPrivacyAccepted = 0;
            } catch (Exception $e) {
                $database->rollback();
                $errors[] = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youth Sign Up - Youth Profiling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-slate-50 to-purple-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-blue-900">Youth Profiling System</h1>
                        <p class="text-sm text-slate-600">Community Youth Registration & Matching</p>
                    </div>
                    <a href="login.php" class="text-blue-700 hover:text-blue-900 font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined">login</span>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex items-center justify-center px-4 py-12">
            <div class="w-full max-w-4xl">
                <!-- Card -->
                <div class="bg-white rounded-3xl shadow-lg border border-slate-200 overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-8 py-8 text-white">
                        <h2 class="text-3xl font-bold">Youth Sign Up</h2>
                        <p class="text-blue-100 mt-2">Create your account and complete your profile to access
                            opportunities and training programs</p>
                    </div>

                    <!-- Card Body -->
                    <div class="px-8 py-8">
                        <!-- Messages -->
                        <?php if ($message): ?>
                            <div
                                class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
                                <p
                                    class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
                                    <span
                                        class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
                                    <?php echo htmlspecialchars($message); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                                <ul class="space-y-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li class="text-red-800 flex items-start gap-2">
                                            <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">error</span>
                                            <span><?php echo htmlspecialchars($error); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="signup" value="1">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

                            <!-- STEP 1: Account Credentials -->
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">account_circle</span>
                                    Account Credentials
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                                        <input type="text" name="username"
                                            value="<?php echo htmlspecialchars($username); ?>" required
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Enter username (4+ characters)">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email
                                            Address</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                                            required
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Enter email address">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                                            <input type="password" name="password" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="6+ characters">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm
                                                Password</label>
                                            <input type="password" name="confirm_password" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Confirm password">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: Personal Information -->
                            <div class="border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">person</span>
                                    Personal Information
                                </h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">First Name
                                                *</label>
                                            <input type="text" name="first_name"
                                                value="<?php echo htmlspecialchars($firstName); ?>" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Middle
                                                Name</label>
                                            <input type="text" name="middle_name"
                                                value="<?php echo htmlspecialchars($middleName); ?>"
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Last Name
                                                *</label>
                                            <input type="text" name="last_name"
                                                value="<?php echo htmlspecialchars($lastName); ?>" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gender
                                                *</label>
                                            <select name="gender" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select gender</option>
                                                <option value="Male"
                                                    <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female"
                                                    <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female
                                                </option>
                                                <option value="Other"
                                                    <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Date of Birth
                                                *</label>
                                            <input type="date" name="date_of_birth"
                                                value="<?php echo htmlspecialchars($dateOfBirth); ?>" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                onchange="updateAge()">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Age *</label>
                                            <input type="number" name="age"
                                                value="<?php echo htmlspecialchars($age); ?>" readonly
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Civil Status
                                                *</label>
                                            <select name="civil_status" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select status</option>
                                                <option value="Single"
                                                    <?php echo $civilStatus === 'Single' ? 'selected' : ''; ?>>Single
                                                </option>
                                                <option value="Married"
                                                    <?php echo $civilStatus === 'Married' ? 'selected' : ''; ?>>Married
                                                </option>
                                                <option value="Widowed"
                                                    <?php echo $civilStatus === 'Widowed' ? 'selected' : ''; ?>>Widowed
                                                </option>
                                                <option value="Solo Parent"
                                                    <?php echo $civilStatus === 'Solo Parent' ? 'selected' : ''; ?>>Solo
                                                    Parent</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Highest
                                                Educational Attainment *</label>
                                            <select name="education_level" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select attainment</option>
                                                <option value="Elementary"
                                                    <?php echo $educationLevel === 'Elementary' ? 'selected' : ''; ?>>
                                                    Elementary</option>
                                                <option value="High School"
                                                    <?php echo $educationLevel === 'High School' ? 'selected' : ''; ?>>
                                                    High School</option>
                                                <option value="Vocational"
                                                    <?php echo $educationLevel === 'Vocational' ? 'selected' : ''; ?>>
                                                    Vocational</option>
                                                <option value="College"
                                                    <?php echo $educationLevel === 'College' ? 'selected' : ''; ?>>
                                                    College</option>
                                                <option value="Other"
                                                    <?php echo $educationLevel === 'Other' ? 'selected' : ''; ?>>Other
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Phone Number
                                                *</label>
                                            <input type="tel" name="phone"
                                                value="<?php echo htmlspecialchars($phone); ?>" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="09XXXXXXXXX">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">Address
                                                *</label>
                                            <input type="text" name="address" required
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Enter your street/purok (detailed address)" value="<?php echo htmlspecialchars($address); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Barangay Selection -->
                            <div class="border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">location_on</span>
                                    Barangay Selection
                                </h3>
                                <div>
                                    <?php
                                    $ref = new Reference($database);
                                    $barangays = $ref->getByCategory('barangay');
                                    ?>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Barangay *</label>
                                    <select name="barangay" required
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Barangay</option>
                                        <?php foreach ($barangays as $b): ?>
                                            <option value="<?php echo htmlspecialchars($b); ?>" <?php echo ($barangay === $b) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- STEP 3: Skills & Interests -->
                            <div class="border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">star</span>
                                    Certifications & Interests (Optional)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Primary
                                            Skill</label>
                                        <input type="text" name="primary_skill"
                                            value="<?php echo htmlspecialchars($primarySkill); ?>"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="e.g., Electrical, Carpentry, Welding">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-slate-700 mb-2">Certifications
                                                (if any)</label>
                                            <textarea name="certifications" rows="2"
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="e.g., TESDA NC II, Food Safety, Computer Programming"><?php echo htmlspecialchars($certifications); ?></textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-slate-700 mb-2">Interests</label>
                                            <textarea name="interests" rows="2"
                                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="What are you interested in?"><?php echo htmlspecialchars($interests); ?></textarea>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Certification
                                            Document</label>
                                        <input type="file" name="certification_file"
                                            accept="image/jpeg,image/png,image/gif,application/pdf"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="text-xs text-slate-500 mt-1">Upload your certification file if
                                            available. Accepted: JPG, PNG, GIF, PDF (Max 5MB).</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Reason Not in
                                            School (if applicable)</label>
                                        <textarea name="reason_not_in_school" rows="2"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Tell us why you're not currently in school"><?php echo htmlspecialchars($reasonNotInSchool); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Current
                                            Status/Engagement</label>
                                        <select name="engagement_status"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select status</option>
                                            <option value="Unemployed"
                                                <?php echo $engagementStatus === 'Unemployed' ? 'selected' : ''; ?>>
                                                Unemployed</option>
                                            <option value="Self-Employed"
                                                <?php echo $engagementStatus === 'Self-Employed' ? 'selected' : ''; ?>>
                                                Self-Employed</option>
                                            <option value="Part-Time Work"
                                                <?php echo $engagementStatus === 'Part-Time Work' ? 'selected' : ''; ?>>
                                                Part-Time Work</option>
                                            <option value="Full-Time Work"
                                                <?php echo $engagementStatus === 'Full-Time Work' ? 'selected' : ''; ?>>
                                                Full-Time Work</option>
                                            <option value="In Training"
                                                <?php echo $engagementStatus === 'In Training' ? 'selected' : ''; ?>>In
                                                Training</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 4: Document Verification -->
                            <div class="border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">id_card</span>
                                    Document Verification (Required)
                                </h3>
                                <p class="text-sm text-slate-600 mb-4">Upload at least one document for identity
                                    verification. You can upload a government ID or certificate of residency.</p>

                                <div class="space-y-4">
                                    <!-- Government ID Section -->
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                                        <h4 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                                            <input type="radio" name="id_choice" value="govt_id" id="id_choice_govt"
                                                checked>
                                            <label for="id_choice_govt" class="cursor-pointer">Government ID</label>
                                        </h4>

                                        <div class="space-y-3 ml-6">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-2">ID
                                                    Type</label>
                                                <select name="govt_id_type"
                                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="">Select ID type</option>
                                                    <option value="National ID"
                                                        <?php echo $govtIdType === 'National ID' ? 'selected' : ''; ?>>
                                                        National ID (PhilID)</option>
                                                    <option value="Passport"
                                                        <?php echo $govtIdType === 'Passport' ? 'selected' : ''; ?>>
                                                        Passport</option>
                                                    <option value="Driver's License"
                                                        <?php echo $govtIdType === 'Driver\'s License' ? 'selected' : ''; ?>>
                                                        Driver's License</option>
                                                    <option value="Voter ID"
                                                        <?php echo $govtIdType === 'Voter ID' ? 'selected' : ''; ?>>
                                                        Voter ID</option>
                                                    <option value="PRC License"
                                                        <?php echo $govtIdType === 'PRC License' ? 'selected' : ''; ?>>
                                                        PRC License</option>
                                                    <option value="Senior Citizen ID"
                                                        <?php echo $govtIdType === 'Senior Citizen ID' ? 'selected' : ''; ?>>
                                                        Senior Citizen ID</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-2">ID
                                                    Number</label>
                                                <input type="text" name="govt_id_number"
                                                    value="<?php echo htmlspecialchars($govtIdNumber); ?>"
                                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    placeholder="Enter your government ID number">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-2">ID
                                                    Image/Photo</label>
                                                <input type="file" name="govt_id_image"
                                                    accept="image/jpeg,image/png,image/gif"
                                                    class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <p class="text-xs text-slate-500 mt-1">Accepted: JPEG, PNG, GIF (Max
                                                    5MB)</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- OR Divider -->
                                    <div class="flex items-center gap-2 my-4">
                                        <div class="flex-1 border-t border-slate-300"></div>
                                        <span class="text-xs font-semibold text-slate-500">OR</span>
                                        <div class="flex-1 border-t border-slate-300"></div>
                                    </div>

                                    <!-- Profile Image (Certificate of Residency) Section -->
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                                        <h4 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                                            <input type="radio" name="id_choice" value="cert_residency"
                                                id="id_choice_cert">
                                            <label for="id_choice_cert" class="cursor-pointer">Certificate of Residency
                                                / Profile Photo</label>
                                        </h4>

                                        <div class="space-y-3 ml-6">
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-2">Upload
                                                    Document or Photo</label>
                                                <input type="file" name="profile_image"
                                                    accept="image/jpeg,image/png,image/gif"
                                                    class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <p class="text-xs text-slate-500 mt-1">Clear photo of Certificate of
                                                    Residency or recent portrait photo</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 5: Data Privacy & Terms -->
                            <div class="border-t border-slate-200 pt-6">
                                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700">shield</span>
                                    Data Privacy & Terms
                                </h3>

                                <!-- Data Privacy Notice -->
                                <div
                                    class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-2xl max-h-48 overflow-y-auto">
                                    <h4 class="font-bold text-blue-900 mb-2">📋 Data Privacy Notice (RA 10173 - Data
                                        Privacy Act of 2012)</h4>
                                    <div class="text-xs text-slate-700 space-y-2">
                                        <p><strong>Collection and Use:</strong> Your personal information including
                                            name, contact details, educational background, skills, and identifying
                                            documents are collected to register you in the Youth Profiling System for
                                            employment and training opportunity matching.</p>

                                        <p><strong>Legal Basis:</strong> This collection is authorized under the Data
                                            Privacy Act of 2012 (RA 10173) and processed for legitimate purposes of
                                            youth development and employment facilitation.</p>

                                        <p><strong>Data Protection:</strong> All personal data is stored securely on
                                            protected servers with restricted access. We implement industry-standard
                                            encryption and access controls to prevent unauthorized access, alteration,
                                            or disclosure.</p>

                                        <p><strong>Data Sharing:</strong> Your information will be shared with:
                                        <ul class="list-disc ml-4 mt-1">
                                            <li>SK Chairman/Barangay Officials for verification and approval</li>
                                            <li>Employers, Training Providers, and LYDO Staff for opportunity matching
                                                (with your consent)</li>
                                            <li>Government agencies as required by law</li>
                                        </ul>
                                        </p>

                                        <p><strong>Data Retention:</strong> Your data will be retained for as long as
                                            you remain an active user of the system, plus 3 years after account closure,
                                            unless legally required to retain longer.</p>

                                        <p><strong>Your Rights:</strong> You have the right to:
                                        <ul class="list-disc ml-4 mt-1">
                                            <li>Access your personal data</li>
                                            <li>Correct inaccurate information</li>
                                            <li>Request deletion (subject to legal requirements)</li>
                                            <li>Object to processing</li>
                                            <li>Lodge complaints with the National Privacy Commission (NPC)</li>
                                        </ul>
                                        </p>

                                        <p><strong>Contact:</strong> For privacy concerns, contact your SK Chairman,
                                            LYDO, or submit complaints to the National Privacy Commission
                                            (www.privacy.gov.ph).</p>
                                    </div>
                                </div>

                                <!-- Checkboxes -->
                                <div class="space-y-3 mb-4">
                                    <label
                                        class="flex items-start gap-3 cursor-pointer p-3 border border-slate-200 rounded-2xl hover:bg-slate-50 transition">
                                        <input type="checkbox" name="data_privacy_accepted"
                                            <?php echo $dataPrivacyAccepted ? 'checked' : ''; ?>
                                            class="mt-1 w-5 h-5 rounded border-slate-300 focus:ring-2 focus:ring-blue-500 flex-shrink-0">
                                        <span class="text-sm text-slate-700">
                                            <strong>I acknowledge and consent</strong> to the collection, processing,
                                            and sharing of my personal data as described in the Data Privacy Notice
                                            above. I understand that my data will be used for youth profiling,
                                            opportunity matching, and employment services in accordance with RA 10173.
                                        </span>
                                    </label>

                                    <label
                                        class="flex items-start gap-3 cursor-pointer p-3 border border-slate-200 rounded-2xl hover:bg-slate-50 transition">
                                        <input type="checkbox" name="consent_accepted"
                                            <?php echo $consentAccepted ? 'checked' : ''; ?>
                                            class="mt-1 w-5 h-5 rounded border-slate-300 focus:ring-2 focus:ring-blue-500 flex-shrink-0">
                                        <span class="text-sm text-slate-700">
                                            <strong>I understand and accept</strong> the terms and conditions of the
                                            Youth Profiling System. I confirm that the information I provided is
                                            accurate and complete. I will not be able to access the system until my SK
                                            Chairman verifies and approves my registration.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t border-slate-200 pt-6 flex gap-4">
                                <button type="submit"
                                    class="flex-1 rounded-2xl bg-blue-700 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-600 transition flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    Submit Registration
                                </button>
                                <a href="login.php"
                                    class="flex-1 rounded-2xl border border-slate-200 text-slate-700 px-6 py-3 text-sm font-semibold hover:bg-slate-50 transition flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">arrow_back</span>
                                    Back to Login
                                </a>
                            </div>
                        </form>

                        <!-- Info Box -->
                        <div class="mt-8 space-y-3">
                            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-200">
                                <p class="text-sm text-blue-900 flex items-start gap-2">
                                    <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">info</span>
                                    <span><strong>Verification Process:</strong> Your SK Chairman will review your
                                        profile and documents. Once approved, you'll receive a notification and can
                                        start accessing job opportunities and training programs.</span>
                                </p>
                            </div>
                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200">
                                <p class="text-sm text-amber-900 flex items-start gap-2">
                                    <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">shield</span>
                                    <span><strong>Your Privacy Matters:</strong> We are committed to protecting your
                                        personal information in compliance with Philippine Data Privacy Law (RA 10173).
                                        Your data will only be used for youth development and employment
                                        services.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-slate-600 mt-6">
                    Already have an account? <a href="login.php"
                        class="text-blue-700 font-semibold hover:text-blue-900">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        function updateAge() {
            const dobInput = document.querySelector('[name="date_of_birth"]');
            const ageInput = document.querySelector('[name="age"]');
            if (!dobInput || !ageInput) return;

            const dobValue = dobInput.value;
            if (!dobValue) {
                ageInput.value = '';
                return;
            }

            const dob = new Date(dobValue);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            ageInput.value = age >= 0 ? age : '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateAge();
            const dobInput = document.querySelector('[name="date_of_birth"]');
            if (dobInput) {
                dobInput.addEventListener('change', updateAge);
            }
        });
    </script>
</body>

</html>