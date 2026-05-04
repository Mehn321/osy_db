<?php
$apiKey = 'AIzaSyBkyEBVsZV2RF7pkKYah9tmrV4i4tOi218';
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
$response = file_get_contents($url);
echo $response;
