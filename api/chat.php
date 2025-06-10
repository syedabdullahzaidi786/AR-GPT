<?php
// Prevent any output before JSON response
ob_start();

// Disable error display but enable logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

session_start();
require_once '../config/database.php';

// Clear any previous output
ob_clean();

// Set proper content type header
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit();
}

try {
    // Get database connection using singleton pattern
    $db = Database::getInstance()->getConnection();
    
    // Get the request body
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No input received');
    }
    
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $message = $data['message'] ?? '';
    $model = $data['model'] ?? 'gemini'; // Default to Gemini if not specified

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    $aiResponse = '';
    
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
        $apiKey = 'AIzaSyC5Kr3Mx_drSaLqB1R1swtwmI1FSnuQYQk';
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
    }

    // Save chat history
    try {
        // First save the user's message
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $message, '', $model]);
        
        // Then save the AI's response
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], '', $aiResponse, $model]);
    } catch (PDOException $e) {
        // Log the error but don't fail the request
        error_log("Failed to save chat history: " . $e->getMessage());
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'response' => $aiResponse
    ]);

} catch (Exception $e) {
    error_log("Chat Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// End output buffering and send response
ob_end_flush();
?> 