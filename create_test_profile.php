<?php
require_once __DIR__ . '/init.php';

if (!$user->isLoggedIn() || $_SESSION['role'] !== 'youth') {
    echo "Not logged in as youth\n";
    exit;
}

$userId = $_SESSION['user_id'];
$osyProfile = new OSYProfile($database);

// Check if profile exists
$existing = $osyProfile->getByUserId($userId);
if ($existing) {
    echo "Profile already exists for user ID: " . $userId . "\n";
    exit;
}

// Create test profile
$data = [
    'first_name' => 'Test',
    'middle_name' => 'Demo',
    'last_name' => 'Youth',
    'email' => $_SESSION['email'] ?? 'test@example.com',
    'phone' => '09123456789',
    'age' => 22,
    'date_of_birth' => '2004-01-15',
    'gender' => 'Male',
    'civil_status' => 'Single',
    'barangay' => 'Barangay Sample',
    'education_level' => 'High School Graduate',
    'reason_for_not_in_school' => 'Seeking Employment',
    'engagement_status' => 'Seeking Employment',
    'govt_id_type' => 'National ID',
    'govt_id_number' => '12-3456789-0',
    'primary_skill' => 'Communication',
    'skills' => 'Problem Solving, Leadership',
    'interests' => 'Technology, Business',
    'profile_type' => 'OSY',
    'status' => 'Active',
    'registration_status' => 'Submitted',
    'verification_status' => 'Pending',
    'consent_accepted' => 1,
    'created_by' => $userId
];

$result = $osyProfile->create($data, null, null, null);

if ($result['success']) {
    echo "Profile created successfully!\n";
    $profile = $osyProfile->getByUserId($userId);
    if ($profile) {
        // Set verification status to Verified so they can see full profile
        $osyProfile->setVerificationStatus($profile['id'], 'Verified', 'Test profile created');
        echo "Profile verified and ready for testing.\n";
        echo "Redirecting to profile page...\n";
        header("Location: pages/my-profile.php");
        exit;
    }
} else {
    echo "Error creating profile: " . ($result['message'] ?? 'Unknown error') . "\n";
}
