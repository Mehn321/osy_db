<?php
require_once 'init.php';
$settings = $database->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('traccar_token','semaphore_api_key') ORDER BY setting_key");
foreach ($settings as $row) {
    echo $row['setting_key'] . ': ' . $row['setting_value'] . PHP_EOL;
}
