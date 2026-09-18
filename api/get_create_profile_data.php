<?php
require_once __DIR__ . '/../init.php';
requireLogin();

require_once __DIR__ . '/../Classes/Reference.php';
$ref = new Reference($database);
$barangays = $ref->getByCategory('barangay');
$eduLevels = $ref->getByCategory('education_level');
$govtIdTypes = $ref->getByCategory('govt_id_type');

header('Content-Type: application/json');
echo json_encode([
    'barangays' => $barangays,
    'eduLevels' => $eduLevels,
    'govtIdTypes' => $govtIdTypes
]);
