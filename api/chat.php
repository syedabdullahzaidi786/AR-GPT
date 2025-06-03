<?php
session_start();
require_once '../config/database.php';
require '../vendor/autoload.php';

use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Language\LanguageClient;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Get database connection using singleton pattern
    $db = Database::getInstance()->getConnection();
    
    // Get the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $message = $data['message'] ?? '';
    $model = $data['model'] ?? 'gemini'; // Default to Gemini if not specified
    $unsplashModel = $data['unsplashModel'] ?? 'default'; // Default to default if not specified

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    $aiResponse = '';
    $images = [];
    
    if ($model === 'weather') {
        // OpenWeatherMap API configuration
        $apiKey = 'b60015e9046ef4afb65133b77adfe1d1';
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($message) . "&appid=" . $apiKey . "&units=metric";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            throw new Exception('Network Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorDetails = json_decode($response, true);
            $errorMessage = $errorDetails['message'] ?? 'Unknown API error';
            throw new Exception('Weather API Error: ' . $errorMessage);
        }

        $weatherData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }

        // Format weather response with emojis
        $aiResponse = sprintf(
            "🌍 Weather in %s:\n\n" .
            "🌡️ Temperature: %.1f°C\n" .
            "☁️ Condition: %s\n" .
            "💧 Humidity: %d%%\n" .
            "💨 Wind Speed: %.1f m/s\n\n" .
            "Last updated: %s",
            $weatherData['name'],
            $weatherData['main']['temp'],
            $weatherData['weather'][0]['description'],
            $weatherData['main']['humidity'],
            $weatherData['wind']['speed'],
            date('Y-m-d H:i:s')
        );

        // Save weather data to database
        $stmt = $db->prepare("INSERT INTO weather_history (user_id, city, temperature, `condition`, humidity, wind_speed) 
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $weatherData['name'],
            $weatherData['main']['temp'],
            $weatherData['weather'][0]['description'],
            $weatherData['main']['humidity'],
            $weatherData['wind']['speed']
        ]);

    } else if ($model === 'gemini') {
        // Gemini API configuration
        $apiKey = getenv('GEMINI_API_KEY') ?: 'AIzaSyC5Kr3Mx_drSaLqB1R1swtwmI1FSnuQYQk';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;

        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $message]
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
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            throw new Exception('Network Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorDetails = json_decode($response, true);
            $errorMessage = $errorDetails['error']['message'] ?? 'Unknown API error';
            throw new Exception('API Error: ' . $errorMessage);
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }

        $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$aiResponse) {
            throw new Exception('Unexpected API response structure');
        }

    } else if ($model === 'openai') {
        // OpenAI API configuration
        $apiKey = getenv('OPENAI_API_KEY') ?: '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';
        $url = 'https://api.openai.com/v1/chat/completions';

        $requestData = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $message]
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
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            throw new Exception('Network Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorDetails = json_decode($response, true);
            $errorMessage = $errorDetails['error']['message'] ?? 'Unknown API error';
            throw new Exception('API Error: ' . $errorMessage);
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }

        $aiResponse = $responseData['choices'][0]['message']['content'] ?? null;
        if (!$aiResponse) {
            throw new Exception('Unexpected API response structure');
        }

    } else if ($model === 'unsplash') {
        // Unsplash API configuration
        $unsplashApiKey = getenv('UNSPLASH_API_KEY') ?: 'zGwmo9iiFfKuqbmFkJVIPKnbhNeC9aZrl1g6F2okB_4';
        $searchQuery = urlencode($message);
        
        // Build Unsplash URL with parameters
        $unsplashParams = [
            'query' => $searchQuery,
            'per_page' => 9, // Show more images when Unsplash is the main model
            'client_id' => $unsplashApiKey
        ];

        $unsplashUrl = "https://api.unsplash.com/search/photos?" . http_build_query($unsplashParams);

        $ch = curl_init($unsplashUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Version: v1',
            'Authorization: Client-ID ' . $unsplashApiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $unsplashResponse = curl_exec($ch);
        $unsplashHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($unsplashHttpCode === 200) {
            $unsplashData = json_decode($unsplashResponse, true);
            if (isset($unsplashData['results'])) {
                foreach ($unsplashData['results'] as $photo) {
                    $images[] = [
                        'url' => $photo['urls']['regular'],
                        'thumb' => $photo['urls']['thumb'],
                        'alt' => $photo['alt_description'] ?? 'Unsplash image',
                        'author' => $photo['user']['name'],
                        'author_url' => $photo['user']['links']['html'],
                        'orientation' => $photo['orientation'] ?? 'landscape',
                        'color' => $photo['color'] ?? '#000000'
                    ];
                }
            }
        }

        // For Unsplash model, we don't need an AI response
        $aiResponse = '';
    } else {
        throw new Exception('Invalid model selected');
    }

    // Save the conversation to database
    // Save user message
    $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, is_user, model) VALUES (?, ?, 1, ?)");
    $stmt->execute([$_SESSION['user_id'], $message, $model]);

    // Save AI response only if not using Unsplash model
    if ($model !== 'unsplash') {
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, is_user, model) VALUES (?, ?, 0, ?)");
        $stmt->execute([$_SESSION['user_id'], $aiResponse, $model]);
    }

    echo json_encode([
        'success' => true,
        'response' => $aiResponse,
        'model' => $model,
        'images' => $images
    ]);

} catch (Exception $e) {
    error_log("Chat Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 