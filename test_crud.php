<?php
session_start();
require_once 'init.php';

// Test login
$user = new User($database);
$login_result = $user->login('admin1', 'Admin@123');

if ($login_result['success']) {
    echo "Login successful\n";

    // Now test creating a profile
    $osyProfile = new OSYProfile($database);
    $_SESSION['user_id'] = 1;

    $result = $osyProfile->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone' => '09123456789',
        'age' => 20,
        'gender' => 'Male',
        'education_level' => 'High School Graduate',
        'barangay' => 'Barangay 1',
        'primary_skill' => 'Welding',
        'status' => 'Active'
    ]);

    echo "Create result: " . json_encode($result) . "\n";

    if ($result['success']) {
        $profile_id = $result['id'];
        echo "Profile created with ID: $profile_id\n";

        // Test update
        $update_result = $osyProfile->update($profile_id, [
            'first_name' => 'Updated',
            'age' => 21,
            'status' => 'Employed'
        ]);
        echo "Update result: " . json_encode($update_result) . "\n";

        // Test delete
        $delete_result = $osyProfile->delete($profile_id);
        echo "Delete result: " . json_encode($delete_result) . "\n";
    }
} else {
    echo "Login failed: " . $login_result['message'] . "\n";
}
