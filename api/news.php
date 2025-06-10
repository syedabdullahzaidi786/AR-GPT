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

    // News API configuration
    $apiKey = '4747b9b1a0d4469bacb7d3d3816f57df'; // Replace with your News API key
    $url = "https://newsapi.org/v2/everything?q=" . urlencode($query) . "&language=en&sortBy=publishedAt&pageSize=5";

    // Log the request details
    error_log("News API Request - URL: " . $url);
    error_log("News API Request - Query: " . $query);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . $apiKey,
        'User-Agent: AR-Bot/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    // Log the response details
    error_log("News API Response - HTTP Code: " . $httpCode);
    error_log("News API Response - Raw Response: " . $response);
    
    curl_close($ch);

    if ($curlErrno) {
        error_log("Curl Error: " . $curlError);
        throw new Exception('Network Error: ' . $curlError);
    }

    if ($httpCode !== 200) {
        error_log("API Error - HTTP Code: " . $httpCode);
        $errorDetails = json_decode($response, true);
        $errorMessage = $errorDetails['message'] ?? 'Unknown API error';
        throw new Exception('News API Error: ' . $errorMessage);
    }

    $newsData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        throw new Exception('Invalid JSON response from API');
    }

    error_log("Decoded News Data: " . print_r($newsData, true));

    if (empty($newsData['articles'])) {
        error_log("No news articles found in response");
        throw new Exception('No news articles found for your search query');
    }

    // Create an array of news articles
    $articles = [];
    foreach ($newsData['articles'] as $article) {
        error_log("Processing article: " . print_r($article, true));
        
        if (isset($article['title']) && isset($article['url'])) {
            $articleData = [
                'title' => $article['title'],
                'description' => $article['description'] ?? '',
                'url' => $article['url'],
                'source' => $article['source']['name'] ?? 'Unknown Source',
                'publishedAt' => $article['publishedAt'] ?? '',
                'imageUrl' => $article['urlToImage'] ?? ''
            ];
            error_log("Adding article data: " . print_r($articleData, true));
            $articles[] = $articleData;
        } else {
            error_log("Missing required fields in article data");
        }
    }

    if (empty($articles)) {
        error_log("No valid articles found in the response");
        throw new Exception('No valid news articles found in the response');
    }

    error_log("Final articles array: " . print_r($articles, true));

    // Save to database
    try {
        $db = Database::getInstance()->getConnection();
        
        // Save user's search query
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $query, '', 'news']);
        
        // Save AI's response
        $stmt = $db->prepare("INSERT INTO chat_history (user_id, message, response, model) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], '', json_encode($articles), 'news']);
    } catch (PDOException $e) {
        error_log("Failed to save chat history: " . $e->getMessage());
    }

    // Return JSON response
    $response = [
        'success' => true,
        'articles' => $articles,
        'response' => 'Here are the latest news articles for your search query:'
    ];
    error_log("Sending response: " . print_r($response, true));
    echo json_encode($response);
    exit();

} catch (Exception $e) {
    error_log("News Error: " . $e->getMessage());
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