<?php
require 'c:/xampp/htdocs/osy_db/init.php';
require 'c:/xampp/htdocs/osy_db/Classes/SmsService.php';

$sms = new SmsService($database);
$result = $sms->send('09123456789', 'This is a test message from the new multi-endpoint local gateway logic.');
print_r($result);
