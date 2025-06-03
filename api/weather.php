<?php
session_start();
require_once '../config/database.php';

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
    $city = $data['city'] ?? '';

    if (empty($city)) {
        throw new Exception('City name is required');
    }

    // OpenWeatherMap API configuration
    $apiKey = 'b60015e9046ef4afb65133b77adfe1d1';
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $apiKey . "&units=metric";

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

    // Save weather data to database
    $stmt = $db->prepare("INSERT INTO weather_history (user_id, city, temperature, description, humidity, wind_speed) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $weatherData['name'],
        $weatherData['main']['temp'],
        $weatherData['weather'][0]['description'],
        $weatherData['main']['humidity'],
        $weatherData['wind']['speed']
    ]);

    echo json_encode([
        'success' => true,
        'weather' => [
            'city' => $weatherData['name'],
            'temperature' => $weatherData['main']['temp'],
            'description' => $weatherData['weather'][0]['description'],
            'humidity' => $weatherData['main']['humidity'],
            'wind_speed' => $weatherData['wind']['speed'],
            'icon' => $weatherData['weather'][0]['icon']
        ]
    ]);

} catch (Exception $e) {
    error_log("Weather Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 