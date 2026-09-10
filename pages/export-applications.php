<?php
require_once __DIR__ . '/../init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
requireRole(['training_provider', 'employer', 'lydo']);

$opportunityId = isset($_GET['opportunity_id']) && ctype_digit($_GET['opportunity_id']) ? (int)$_GET['opportunity_id'] : 0;
$format = $_GET['format'] ?? 'csv';
$statusFilter = !empty($_GET['status']) && $_GET['status'] !== 'All' ? $_GET['status'] : null;
$search = !empty($_GET['search']) ? trim($_GET['search']) : null;

if ($opportunityId <= 0) {
    die('Invalid opportunity ID');
}

// Ensure user has access to this opportunity
$opportunity = $database->fetchOne('SELECT * FROM opportunities WHERE id = ?', [$opportunityId], 'i');
if (!$opportunity) {
    die('Opportunity not found');
}
if ($_SESSION['role'] !== 'lydo' && (int) $opportunity['provider_id'] !== (int) $_SESSION['user_id']) {
    http_response_code(403);
    exit('Access denied. You can only export applications for your own opportunities.');
}

$matching = new Matching($database);
// We reuse getMatchesForOpportunityPaginated but without limit
$applications = $matching->getMatchesForOpportunity($opportunityId, 0, null, null);

// In-memory filtering since the backend isn't filtering by status/search directly in getMatchesForOpportunity currently
if ($statusFilter) {
    $applications = array_filter($applications, fn($app) => $app['status'] === $statusFilter);
}
if ($search) {
    $searchLower = strtolower($search);
    $applications = array_filter($applications, function($app) use ($searchLower) {
        $text = strtolower($app['first_name'] . ' ' . $app['last_name'] . ' ' . $app['email'] . ' ' . $app['phone'] . ' ' . $app['primary_skill']);
        return strpos($text, $searchLower) !== false;
    });
}
$applications = array_values($applications); // reindex

$filename = "applications_opp_{$opportunityId}_" . date('Ymd_His');

if ($format === 'json') {
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=\"{$filename}.json\"");
    echo json_encode($applications, JSON_PRETTY_PRINT);
    exit;
}

// For CSV and Excel (which is basically CSV with xlsx extension or actual xlsx if PhpSpreadsheet is used)
// We'll use CSV for both here for simplicity, Excel can open CSV easily, or output a simple HTML table for Excel.
if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
    echo "<table border='1'>";
    echo "<tr><th>First Name</th><th>Last Name</th><th>Email</th><th>Phone</th><th>Age</th><th>Primary Skill</th><th>Status</th></tr>";
    foreach ($applications as $app) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($app['first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($app['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($app['email']) . "</td>";
        echo "<td>" . htmlspecialchars($app['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($app['age'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($app['primary_skill'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($app['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

// Default to CSV
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
$out = fopen('php://output', 'w');
fputcsv($out, ['First Name', 'Last Name', 'Email', 'Phone', 'Age', 'Primary Skill', 'Status']);
foreach ($applications as $app) {
    fputcsv($out, [
        $app['first_name'],
        $app['last_name'],
        $app['email'],
        $app['phone'],
        $app['age'] ?? '-',
        $app['primary_skill'] ?? '-',
        $app['status']
    ]);
}
fclose($out);
exit;
