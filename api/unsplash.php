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
    // Get the request body
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No input received');
    }
    
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $query = $data['message'] ?? '';
    if (empty($query)) {
        throw new Exception('Search query is required');
    }

    // Unsplash API configuration
    $accessKey = 'sSPE5oOAtnbsQ4gxKAtWdn6yoCLRIcK1FkJT1eMByBw';
    $url = "https://api.unsplash.com/search/photos?query=" . urlencode($query) . "&per_page=5";

    // Log the request details
    error_log("Unsplash API Request - URL: " . $url);
    error_log("Unsplash API Request - Query: " . $query);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Client-ID ' . $accessKey,
        'Accept-Version: v1'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    // Detailed debug logging
    error_log("Unsplash API Response - HTTP Code: " . $httpCode);
    error_log("Unsplash API Response - Raw Response: " . $response);
    
    curl_close($ch);

    if ($curlErrno) {
        error_log("Curl Error: " . $curlError);
        throw new Exception('Network Error: ' . $curlError);
    }

    if ($httpCode !== 200) {
        error_log("API Error - HTTP Code: " . $httpCode);
        $errorDetails = json_decode($response, true);
        $errorMessage = $errorDetails['errors'][0] ?? 'Unknown API error';
        throw new Exception('Unsplash API Error: ' . $errorMessage);
    }

    $imageData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        throw new Exception('Invalid JSON response from API');
    }

    error_log("Decoded Image Data: " . print_r($imageData, true));

    if (empty($imageData['results'])) {
        error_log("No images found in response");
        throw new Exception('No images found for your search query');
    }

    // Create an array of image data
    $images = [];
    foreach ($imageData['results'] as $image) {
        error_log("Processing image: " . print_r($image, true));
        
        if (isset($image['urls']['regular']) && isset($image['user']['name']) && isset($image['links']['html'])) {
            $imageData = [
                'url' => $image['urls']['regular'],
                'photographer' => $image['user']['name'],
                'unsplash_link' => $image['links']['html']
            ];
            error_log("Adding image data: " . print_r($imageData, true));
            $images[] = $imageData;
        } else {
            error_log("Missing required fields in image data");
        }
    }

    if (empty($images)) {
        error_log("No valid images found in the response");
        throw new Exception('No valid images found in the response');
    }

    error_log("Final images array: " . print_r($images, true));

    // Save to database
    try {
        $db = Database::getInstance()->getConnection();
        
        // Save user's search query
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $query, '', 'unsplash']);
        
        // Save AI's response
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], '', json_encode($images), 'unsplash']);
    } catch (PDOException $e) {
        error_log("Failed to save chat history: " . $e->getMessage());
    }

    // Return JSON response
    $response = [
        'success' => true,
        'images' => $images,
        'response' => 'Here are some images for your search query:'
    ];
    error_log("Sending response: " . print_r($response, true));
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    error_log("Unsplash Error: " . $e->getMessage());
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