<?php
require_once 'config/database.php';

function testGeminiAPI() {
    $apiKey = 'AIzaSyC5Kr3Mx_drSaLqB1R1swtwmI1FSnuQYQk';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;
    
    $requestData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Hello, how are you?']
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "Gemini API Test:\n";
    echo "HTTP Code: " . $httpCode . "\n";
    if ($curlError) {
        echo "Error: " . $curlError . "\n";
    } else {
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
    echo "\n";
}

function testOpenAIAPI() {
    $apiKey = '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';
    $url = 'https://api.openai.com/v1/chat/completions';

    $requestData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, how are you?']
        ],
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "OpenAI API Test:\n";
    echo "HTTP Code: " . $httpCode . "\n";
    if ($curlError) {
        echo "Error: " . $curlError . "\n";
    } else {
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
    echo "\n";
}

function testWeatherAPI() {
    $apiKey = 'b60015e9046ef4afb65133b77adfe1d1';
    $url = "https://api.openweathermap.org/data/2.5/weather?q=London&appid=" . $apiKey . "&units=metric";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "Weather API Test:\n";
    echo "HTTP Code: " . $httpCode . "\n";
    if ($curlError) {
        echo "Error: " . $curlError . "\n";
    } else {
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
    echo "\n";
}

// Run tests
echo "Starting API Tests...\n\n";
testGeminiAPI();
testOpenAIAPI();
testWeatherAPI();
?> 