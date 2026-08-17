<?php
$pageTitle = 'Edit Member';
require_once __DIR__ . '/../init.php';
requireLogin();
requireRole('lydo');

// Get member ID from query string
if (!isset($_GET['user_id'])) {
    die('User ID not specified');
}
$userId = (int)$_GET['user_id'];

// Fetch member data
$member = $database->fetchOne('SELECT * FROM users WHERE id = ?', [$userId], 'i');
if (!$member) {
    die('Member not found');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF nonce
    if (!validateFormNonce($_POST['form_nonce'] ?? '')) {
        die('Invalid CSRF token');
    }
    // Collect and sanitize inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? $member['role']);
    $area = trim($_POST['area'] ?? ($member['barangay'] ?? $member['provider_type'] ?? ''));
    $status = trim($_POST['status'] ?? ($member['status'] ?? 'Active'));

    // Build update query
    $query = "UPDATE users SET fullname = ?, email = ?, role = ?, status = ?";
    $params = [$fullname, $email, $role, $status];
    $types = "ssss";
    if ($role === 'training_provider') {
        $query .= ", provider_type = ?";
        $params[] = $area;
        $types .= "s";
    } else {
        $query .= ", barangay = ?";
        $params[] = $area;
        $types .= "s";
    }
    $query .= " WHERE id = ?";
    $params[] = $userId;
    $types .= "i";

    $database->query($query, $params, $types);
    header('Location: member-registry.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-2xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-4">Edit Member</h2>
    <form method="POST" class="space-y-4">
        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
        <div>
            <label class="block font-medium mb-1" for="fullname">Full Name</label>
            <input id="fullname" name="fullname" type="text" required class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($member['fullname']); ?>">
        </div>
        <div>
            <label class="block font-medium mb-1" for="email">Email</label>
            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($member['email']); ?>">
        </div>
        <div>
            <label class="block font-medium mb-1" for="role">Role</label>
            <select id="role" name="role" class="w-full border rounded px-3 py-2" onchange="toggleAreaField(this.value)">
                <?php
                $roles = ['sk_chairman','employer','training_provider','youth'];
                foreach ($roles as $r) {
                    $selected = $member['role'] === $r ? 'selected' : '';
                    echo "<option value=\"$r\" $selected>" . ucfirst(str_replace('_',' ', $r)) . "</option>";
                }
                ?>
            </select>
        </div>
        <div id="areaField">
            <label class="block font-medium mb-1" for="area">
                <?php echo $member['role'] === 'training_provider' ? 'Provider Type' : 'Barangay'; ?>
            </label>
            <input id="area" name="area" type="text" class="w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($member['role'] === 'training_provider' ? $member['provider_type'] : $member['barangay']); ?>">
        </div>
        <div>
            <label class="block font-medium mb-1" for="status">Status</label>
            <select id="status" name="status" class="w-full border rounded px-3 py-2">
                <?php
                $statuses = ['Active','Inactive'];
                foreach ($statuses as $s) {
                    $sel = ($member['status'] ?? 'Active') === $s ? 'selected' : '';
                    echo "<option value=\"$s\" $sel>$s</option>";
                }
                ?>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
            <a href="member-registry.php" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">Cancel</a>
        </div>
    </form>
</div>
<script>
function toggleAreaField(role) {
    const label = document.querySelector('#areaField label');
    label.textContent = role === 'training_provider' ? 'Provider Type' : 'Barangay';
}
</script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
