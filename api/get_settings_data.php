<?php
/**
 * API: Settings Data
 * Serves system settings and sync stats for settings.php (LYDO only).
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn() || ($_SESSION['role'] ?? '') !== 'lydo') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cache = new Cache(30);

try {
    require_once __DIR__ . '/../Classes/Matching.php';
    $matching = new Matching($database);

    $cacheKey = 'settings_data';
    $data = $cache->remember($cacheKey, function () use ($database, $matching) {
        // System settings key-value store
        $raw = $database->fetchAll("SELECT * FROM system_settings");
        $settings = [];
        foreach ($raw as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        return [
            'system_settings' => $settings,
            'sync_stats'      => $matching->getGlobalSyncStats(),
        ];
    }, 30);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching settings data']);
}
