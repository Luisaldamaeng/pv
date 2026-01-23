<?php
require_once 'config.php';

$models = [
    'gemini-1.5-flash',
    'gemini-flash-latest',
    'gemini-1.5-flash-002',
    'gemini-pro',
    'gemini-pro-latest',
    'gemini-2.0-flash-lite'
];

$results = [];

foreach ($models as $model) {
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . GEMINI_API_KEY;
    $data = ["contents" => [["parts" => [["text" => "hi"]]]]];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[$model] = [
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
?>