<?php

/**
 * Simple SMS sender for Traccar cloud gateway.
 * Update $phone and $authToken before running.
 */

$gatewayUrl = 'https://www.traccar.org/sms/';
$phone = '+639631371969';
$message = 'Profiling System: Simple SMS test.';

// Traccar cloud token from the Android app.
$authToken = 'fntX9p3fR5qOiRRacRnUD4:APA91bFMrtwBMxffrUEFzKVMLZiUzXb5jMx3hiYndHEnQY2-gUj1qjT5uPtG30z7PgZYzh5LpNw_esrr7wE1RybeidkqAWXTxhtDyMZdNxdbg_NuTUOiyz8';

$payload = json_encode([
    'to' => $phone,
    'message' => $message,
]);

$headers = [
    'Content-Type: application/json',
    'Authorization: ' . $authToken,
];

$ch = curl_init($gatewayUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Gateway URL: $gatewayUrl\n";
echo "Phone: $phone\n";
echo "HTTP code: $httpCode\n";
echo "Response: " . ($response === false ? '<no response>' : $response) . "\n";
echo "Curl error: " . ($error ?: '<none>') . "\n";
