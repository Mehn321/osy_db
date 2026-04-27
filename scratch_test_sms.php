<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/Classes/SmsService.php';

$sms = new SmsService($database);
// Replace with the user's phone for testing IF I knew it, but I'll use a dummy one from their table
$testPhone = "09171234567"; 
$message = "Test SMS from CLI at " . date('H:i:s');

echo "Sending SMS to $testPhone...\n";
$result = $sms->send($testPhone, $message);

print_r($result);
