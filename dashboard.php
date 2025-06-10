<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    // Get database connection using singleton pattern
    $db = Database::getInstance()->getConnection();
    
    // Get user data with plan information
    $stmt = $db->prepare("SELECT u.*, p.name as plan_name FROM users u JOIN plans p ON u.plan_id = p.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // If user not found, destroy session and redirect
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // Get user's current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_password = $stmt->fetchColumn();

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    // Handle error appropriately
    die("An error occurred. Please try again later.");
}

// Get user's chat history
$conn = $db;

// Fetch unique chat sessions for the user
$stmt = $conn->prepare("
    SELECT DISTINCT DATE(created_at) as chat_date, 
           MIN(created_at) as session_start,
           MAX(created_at) as session_end
    FROM chat_history 
    WHERE user_id = ? 
    GROUP BY DATE(created_at)
    ORDER BY session_start DESC
");
$stmt->execute([$_SESSION['user_id']]);
$chat_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get messages for the most recent session (or a specific session if selected)
$current_session = isset($_GET['session']) ? $_GET['session'] : null;
$params = [$_SESSION['user_id']];
$date_condition = "";

if ($current_session) {
    $date_condition = "AND DATE(created_at) = ?";
    $params[] = $current_session;
}

$stmt = $conn->prepare("
    SELECT 
        id,
        user_id,
        message,
        response,
        model,
        created_at,
        CASE 
            WHEN message != '' AND response = '' THEN 1
            ELSE 0
        END as is_user
    FROM chat_history 
    WHERE user_id = ? $date_condition
    ORDER BY created_at ASC
");
$stmt->execute($params);
$chat_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR GPT | Dahboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #1558b3;
            --background-color: #f8f9fa;
            --sidebar-color: #ffffff;
            --chat-bg: #ffffff;
            --user-message-bg: #e8f0fe; /* Lighter blue for user */
            --ai-message-bg: #ffffff;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --border-color: #e0e0e0;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Google Sans', 'Roboto', sans-serif;
            height: 100vh;
            overflow: hidden;
            color: var(--text-primary);
            display: flex;
            position: relative; /* Add position relative */
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
            height: 100vh;
            background-color: var(--sidebar-color);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            padding: 1rem 0;
            box-shadow: 1px 0 5px rgba(0,0,0,0.05);
            position: fixed; /* Make sidebar fixed */
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            transition: transform 0.3s ease;
        }

        .logo-container:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 600;
            color: #202123; /* ChatGPT dark color */
            letter-spacing: -0.5px;
            font-family: 'Google Sans', 'Roboto', sans-serif;
        }

        @media (max-width: 768px) {
            .logo-icon {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 480px) {
            .logo-icon {
                width: 35px;
                height: 35px;
            }
        }

        .new-chat-btn {
            background-color: var(--primary-color);
            color: white;
            border-radius: 24px; /* More rounded */
            padding: 0.75rem 1.5rem;
            margin: 0 1.5rem 1rem 1.5rem; /* Adjusted margin */
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            font-size: 1rem;
            font-weight: 500;
             box-shadow: 0 2px 4px rgba(0,0,0,0.1);
             text-decoration: none; /* Remove underline for link style */
             width: calc(100% - 3rem); /* Adjust width for margin */
             justify-content: center; /* Center content */
        }

        .new-chat-btn:hover {
            background-color: var(--secondary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .chat-history {
            flex-grow: 1;
            padding: 0 1rem;
        }

        .chat-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            color: var(--text-primary);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            overflow: hidden; /* Hide overflowing text */
            text-overflow: ellipsis; /* Add ellipsis for overflowing text */
            white-space: nowrap; /* Prevent text wrapping */
        }

        .chat-item:hover {
            background-color: #f1f3f4;
        }

        .chat-item.active {
            background-color: #e8f0fe;\
            color: var(--primary-color);
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
            background-color: #f8f9fa;
        }

        .plan-info {
            background-color: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .plan-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .plan-value {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .plan-value i {
            font-size: 1rem;
            color: #10b981; /* Green color for the checkmark */
        }

        .change-plan-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 1rem;
        }

        .change-plan-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .change-plan-btn i {
            font-size: 0.9rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem;
            background-color: #fee2e2;
            color: #dc2626;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #fecaca;
            color: #b91c1c;
            transform: translateY(-1px);
        }

        .logout-btn i {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar-footer {
                padding: 1rem;
            }

            .plan-info {
                padding: 0.75rem;
            }

            .plan-value {
                font-size: 1rem;
            }

            .change-plan-btn {
                padding: 0.6rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar-footer {
                padding: 0.75rem;
            }

            .plan-info {
                padding: 0.6rem;
            }

            .plan-label {
                font-size: 0.85rem;
            }

            .plan-value {
                font-size: 0.95rem;
            }

            .change-plan-btn {
                padding: 0.5rem;
                font-size: 0.85rem;
            }
        }

        /* Main Chat Area */
        .main-content {
            flex-grow: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--chat-bg);
            margin-left: 280px; /* Add margin equal to sidebar width */
            width: calc(100% - 280px); /* Adjust width */
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

         /* Navigation Bar within main content */
        .navbar {
            background-color: var(--chat-bg) !important;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
            z-index: 1;
        }

        .navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-radius: 20px;
            margin-right: 1rem;
        }

        .user-welcome .welcome-text {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .user-welcome .user-name {
            color: var(--primary-color);
            font-weight: 600;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #fee2e2;
            color: #dc2626;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #fecaca;
            color: #b91c1c;
            transform: translateY(-1px);
        }

        .logout-btn i {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 1rem;
            }

            .user-welcome {
                padding: 0.4rem 0.8rem;
                margin-right: 0.5rem;
            }

            .user-welcome .welcome-text {
                font-size: 0.9rem;
            }

            .logout-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .user-welcome {
                padding: 0.3rem 0.6rem;
            }

            .user-welcome .welcome-text {
                font-size: 0.85rem;
            }

            .logout-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.85rem;
            }
        }

        .chat-container {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        }

        .message {
            max-width: 85%;
            padding: 1rem;
            border-radius: 18px;
            position: relative;
            animation: fadeIn 0.3s ease;
            font-size: 1rem;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin-bottom: 0.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-message {
            background-color: var(--user-message-bg);
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .ai-message {
            background-color: var(--ai-message-bg);
            margin-right: auto;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color); /* Add a subtle border */
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.75rem; /* Increased gap */
            margin-bottom: 0.75rem; /* Increased margin */
             font-weight: 600;
             color: var(--text-secondary);
             font-size: 0.9rem;
        }

        .ai-avatar {
            width: 36px; /* Slightly larger */
            height: 36px; /* Slightly larger */
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
             flex-shrink: 0;
        }

        .message-content {
            padding: 1rem;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Input Area */
        .input-area {
            padding: 1rem;
            background-color: var(--chat-bg);
            border-top: 1px solid var(--border-color);
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .input-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            border-radius: 28px;
            background-color: #f8f9fa;
            transition: box-shadow 0.3s ease;
        }

        .input-container:focus-within {
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }

        .chat-input {
            width: 100%;
            padding: 1rem 3rem 1rem 1.5rem; /* Adjusted padding */
            border: none; /* Remove border */
            border-radius: 24px; /* More rounded */
            font-size: 1rem;
            resize: none;
            min-height: 50px;
            max-height: 150px; /* Reduced max height */
            overflow-y: auto;
            transition: background-color 0.3s ease;
             background-color: transparent; /* Transparent background */
        }

        .chat-input:focus {
            outline: none;
            background-color: #ffffff; /* White background on focus */
        }

        .send-button {
            position: absolute;
            right: 12px; /* Adjusted position */
            bottom: 10px; /* Adjusted position */
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px; /* Slightly smaller */
            height: 30px; /* Slightly smaller */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
             font-size: 0.9rem;
        }

        .send-button:hover {
            background-color: var(--secondary-color);
        }

        .send-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 2px 0 8px rgba(0,0,0,0.15);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .main-content.sidebar-active {
                margin-left: 280px;
                width: calc(100% - 280px);
            }

            .chat-container {
                padding: 1rem;
            }

            .message {
                max-width: 90%;
                padding: 0.8rem;
            }

            .input-area {
                padding: 0.75rem;
            }

            .input-container {
                border-radius: 24px;
            }

            .chat-input {
                padding: 0.75rem 2.5rem 0.75rem 1rem;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .message {
                max-width: 95%;
                padding: 0.7rem;
            }

            .message-header {
                gap: 0.5rem;
                margin-bottom: 0.4rem;
            }

            .ai-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }

            .input-area {
                padding: 0.5rem;
            }

            .chat-input {
                padding: 0.6rem 2.2rem 0.6rem 0.8rem;
                font-size: 0.9rem;
                min-height: 40px;
            }

            .send-button {
                width: 28px;
                height: 28px;
                right: 6px;
                bottom: 6px;
            }
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
            }
        }

        /* Add styles for model selector */
        .model-selector {
            min-width: 150px;
        }

        .model-selector select {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background-color: white;
            color: var(--text-primary);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .model-selector select:hover {
            border-color: var(--primary-color);
        }

        .model-selector select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        @media (max-width: 768px) {
            .model-selector {
                min-width: 120px;
            }
            
            .model-selector select {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
        }

        /* Add styles for images */
        .message-images {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 12px;
        }

        .image-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .image-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .image-container img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .image-container:hover img {
            transform: scale(1.05);
        }

        .image-attribution {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 0.75rem;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
            color: white;
            font-size: 0.9rem;
            text-align: left;
        }

        .image-attribution a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .image-attribution a:hover {
            color: #e0e0e0;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .message-images {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .image-container img {
                height: 200px;
            }
        }

        .welcome-message {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            color: var(--text-secondary);
            opacity: 0.7;
            gap: 1.5rem;
        }

        .welcome-logo {
            margin-bottom: 1rem;
        }

        .welcome-logo .logo-icon {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transition: transform 0.3s ease;
        }

        .welcome-logo .logo-icon:hover {
            transform: scale(1.05);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .help-title {
            font-size: 2rem;
            font-weight: 500;
            margin: 0;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .powered-by {
            margin-top: 0.5rem;
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 500;
            opacity: 0.8;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .welcome-logo .logo-icon {
                width: 60px;
                height: 60px;
            }
            .welcome-title {
                font-size: 2rem;
                padding: 0.8rem 1.5rem;
            }
            .help-title {
                font-size: 1.5rem;
                padding: 0.8rem 1.5rem;
            }
            .powered-by {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .welcome-logo .logo-icon {
                width: 50px;
                height: 50px;
            }
            .welcome-title {
                font-size: 1.8rem;
                padding: 0.6rem 1.2rem;
            }
            .help-title {
                font-size: 1.2rem;
                padding: 0.6rem 1.2rem;
            }
            .powered-by {
                font-size: 0.9rem;
            }
        }

        /* Unsplash Image Styles */
        .image-container {
            margin: 1rem 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .unsplash-image {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }

        .unsplash-image:hover {
            transform: scale(1.02);
        }

        .photographer-credit {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0.5rem 0;
        }

        .unsplash-link {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .unsplash-link a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .unsplash-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="./images/logo.png" alt="ChatGPT Logo" class="logo-icon">
            </div>
        </div>
        <a href="#" class="new-chat-btn" id="newChatBtn">
            <i class="fas fa-plus"></i>
            New Chat
        </a>
        <div class="chat-history">
            <?php foreach ($chat_sessions as $session): ?>
                <div class="chat-item <?php echo ($current_session == $session['chat_date']) ? 'active' : ''; ?>" 
                     data-session="<?php echo $session['chat_date']; ?>">
                    <div class="flex items-center">
                        <i class="fas fa-comment-alt mr-2 text-blue-500"></i>
                        <span><?php echo date('F j, Y', strtotime($session['chat_date'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="sidebar-footer">
            <div class="plan-info">
                <span class="plan-label">Current Plan</span>
                <div class="plan-value">
                    <i class="fas fa-crown"></i>
                    <?php echo htmlspecialchars($user['plan_name']); ?>
                </div>
            </div>
            <a href="plans.php" class="change-plan-btn">
                <i class="fas fa-sync-alt"></i>
                Change Plan
            </a>
            <a href="#" class="change-plan-btn" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key"></i>
                Change Password
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="d-flex items-center ms-auto">
                    <!-- Add Model Selection Dropdown -->
                    <div class="model-selector me-3">
                        <select class="form-select" id="modelSelector">
                            <option value="gemini">Gemini AI</option>
                            <option value="openai">OpenAI GPT</option>
                            <option value="weather">Weather</option>
                            <option value="unsplash">Unsplash Images</option>
                        </select>
                    </div>
                    <div class="user-welcome">
                        <span class="welcome-text">Welcome,</span>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>
        <!-- Chat Container -->
        <div class="chat-container" id="chatContainer">
            <?php if (empty($chat_history)): ?>
                <div class="welcome-message">
                    <div class="welcome-logo">
                        <img src="./images/logo.png" alt="AR GPT Logo" class="logo-icon">
                    </div>
                    <h1 class="welcome-title">Welcome To AR GPT</h1>
                    <h2 class="help-title">What Can I Help You With?</h2>
                    <div class="powered-by">Powered By: AR Developers</div>
                </div>
            <?php else: ?>
                <?php foreach ($chat_history as $chat): ?>
                    <div class="message <?php echo $chat['is_user'] ? 'user-message' : 'ai-message'; ?>">
                        <?php if (!$chat['is_user']): ?>
                            <div class="message-header">
                                <div class="ai-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <span>AI Assistant</span>
                            </div>
                        <?php endif; ?>
                        <div class="message-content">
                            <?php echo htmlspecialchars($chat['is_user'] ? $chat['message'] : $chat['response']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-container">
                <textarea 
                    id="message" 
                    class="chat-input" 
                    placeholder="Type your message here..." 
                    rows="1"
                    onInput="this.parentNode.dataset.replicatedValue = this.value"
                ></textarea>
                <button type="submit" class="send-button" id="sendButton">
                    <i class="fas fa-arrow-up"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Plan Modal -->
    <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="planModalLabel">Choose Your Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="planModalBody">
                    <!-- Content from plans.php will be loaded here -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-gray-600">Loading plans...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    if (isset($_SESSION['password_message'])) {
                        echo '<div class="alert alert-' . $_SESSION['password_message_type'] . '">' . $_SESSION['password_message'] . '</div>';
                        unset($_SESSION['password_message']);
                        unset($_SESSION['password_message_type']);
                    }
                    ?>
                    <form action="change_password.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" value="<?php echo htmlspecialchars($current_password); ?>" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea
        const textarea = document.querySelector('.chat-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Handle sidebar toggle and overlay
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const overlay = document.getElementById('overlay');
        let isSidebarOpen = false;

        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open on mobile
            document.body.style.overflow = isSidebarOpen ? 'hidden' : '';
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Close sidebar on window resize if screen becomes large
        function handleResize() {
            if (window.innerWidth > 768 && isSidebarOpen) {
                toggleSidebar();
            }
        }
        window.addEventListener('resize', handleResize);

        // Handle form submission (using send button click)
        async function sendMessage() {
            const messageInput = document.getElementById('message');
            const message = messageInput.value.trim();
            const selectedModel = document.getElementById('modelSelector').value;
            
            if (!message) return;
            
            // Disable input and button while processing
            messageInput.disabled = true;
            document.getElementById('sendButton').disabled = true;
            
            // Add user message to chat
            addMessage(message, true);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            try {
                const apiEndpoint = selectedModel === 'unsplash' ? 'api/unsplash.php' : 'api/chat.php';
                console.log('Sending request to:', apiEndpoint);
                
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        message,
                        model: selectedModel
                    }),
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    if (selectedModel === 'unsplash') {
                        if (data.images && Array.isArray(data.images) && data.images.length > 0) {
                            console.log('Processing Unsplash images:', data.images);
                            addImageMessage(data.images);
                        } else {
                            console.error('No images in response:', data);
                            addMessage('No images found for your search query.', false);
                        }
                    } else if (data.response) {
                        addMessage(data.response, false);
                    } else {
                        throw new Error('Invalid response format');
                    }
                } else {
                    console.error('API Error:', data.error);
                    addMessage(`Error: ${data.error || 'An error occurred'}`, false);
                }
            } catch (error) {
                console.error('Error:', error);
                addMessage(`Error: ${error.message}`, false);
            } finally {
                // Re-enable input and button
                messageInput.disabled = false;
                document.getElementById('sendButton').disabled = false;
                messageInput.focus();
            }
        }

        function addMessage(message, isUser) {
            const chatContainer = document.getElementById('chatContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
            
            if (!isUser) {
                messageDiv.innerHTML = `
                    <div class="message-header">
                        <div class="ai-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <span>AI Assistant</span>
                    </div>
                `;
            }
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            messageContent.textContent = message;
            messageDiv.appendChild(messageContent);
            
            chatContainer.appendChild(messageDiv);
            
            // Smooth scroll to bottom
            chatContainer.scrollTo({
                top: chatContainer.scrollHeight,
                behavior: 'smooth'
            });
        }

        function addImageMessage(images) {
            console.log('Adding image message:', images);
            
            const chatContainer = document.getElementById('chatContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ai-message';
            
            let imagesHtml = '';
            images.forEach((image, index) => {
                console.log(`Processing image ${index}:`, image);
                if (image.url && image.photographer && image.unsplash_link) {
                    imagesHtml += `
                        <div class="image-container">
                            <img src="${image.url}" 
                                 alt="Unsplash Image" 
                                 class="unsplash-image" 
                                 onerror="this.onerror=null; console.log('Image failed to load:', this.src);"
                                 onload="console.log('Image loaded successfully:', this.src);">
                            <div class="image-attribution">
                                📸 Photo by ${image.photographer} on Unsplash
                                <br>
                                <a href="${image.unsplash_link}" target="_blank" rel="noopener noreferrer">View on Unsplash</a>
                            </div>
                        </div>`;
                } else {
                    console.error('Invalid image data:', image);
                }
            });
            
            if (!imagesHtml) {
                console.error('No valid images to display');
                addMessage('Error: Could not display images.', false);
                return;
            }
            
            messageDiv.innerHTML = `
                <div class="message-header">
                    <div class="ai-avatar">
                        <i class="fas fa-image"></i>
                    </div>
                    <span>Image Search</span>
                </div>
                <div class="message-content">
                    <div class="message-images">
                        ${imagesHtml}
                    </div>
                </div>
            `;
            
            chatContainer.appendChild(messageDiv);
            
            // Smooth scroll to bottom
            chatContainer.scrollTo({
                top: chatContainer.scrollHeight,
                behavior: 'smooth'
            });
        }

        // Add event listener for send button
        document.getElementById('sendButton').addEventListener('click', sendMessage);

        // Add event listener for Enter key
        document.getElementById('message').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Handle New Chat button
        document.getElementById('newChatBtn').addEventListener('click', (e) => {
            e.preventDefault();
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.innerHTML = `
                <div class="welcome-message">
                    <div class="welcome-logo">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/ChatGPT_logo.svg" alt="AR GPT Logo" class="logo-icon">
                    </div>
                    <h1 class="welcome-title">Welcome To AR GPT</h1>
                    <h2 class="help-title">What Can I Help You With?</h2>
                    <div class="powered-by">Powered By: AR Developers</div>
                </div>
            `;
            
            // Close sidebar on mobile after new chat
            if (window.innerWidth <= 768 && isSidebarOpen) {
                toggleSidebar();
            }
            
            // Clear URL parameter if a session was loaded
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, "dashboard.php");
            }
        });

        // Handle chat history item clicks
        document.querySelectorAll('.chat-item').forEach(item => {
            item.addEventListener('click', () => {
                const session = item.dataset.session;
                window.location.href = `dashboard.php?session=${session}`;
            });
        });

        // Handle Change Plan link click to open modal
        document.getElementById('changePlanLink').addEventListener('click', async (e) => {
            e.preventDefault();
            
            const planModalBody = document.getElementById('planModalBody');
            planModalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-gray-600">Loading plans...</p>
                </div>
            `;

            const planModal = new bootstrap.Modal(document.getElementById('planModal'));
            planModal.show();

            try {
                const response = await fetch('plans.php?modal=true');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const html = await response.text();
                planModalBody.innerHTML = html;

                // Re-attach event listeners for forms within the modal
                planModalBody.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', async function(event) {
                        event.preventDefault();

                        const formData = new FormData(this);
                        const formResponse = await fetch('plans.php', {
                            method: 'POST',
                            body: formData
                        });
                        const responseText = await formResponse.text();
                        
                        // Reload the modal content to show the updated plan
                        document.getElementById('changePlanLink').click();
                    });
                });

            } catch (error) {
                console.error('Error loading plans:', error);
                planModalBody.innerHTML = '<p class="text-center text-red-600">Error loading plans. Please try again later.</p>';
            }
        });

        // Initialize tooltips if using Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Update model selection handling
        document.getElementById('modelSelector').addEventListener('change', function() {
            const selectedModel = this.value;
            // Store the selected model in localStorage
            localStorage.setItem('selectedModel', selectedModel);
            
            // Update the chat interface to reflect the model change
            const aiAvatar = document.querySelector('.ai-avatar i');
            if (selectedModel === 'gemini') {
                aiAvatar.className = 'fas fa-robot';
            } else if (selectedModel === 'openai') {
                aiAvatar.className = 'fas fa-brain';
            } else if (selectedModel === 'weather') {
                aiAvatar.className = 'fas fa-cloud';
            } else if (selectedModel === 'unsplash') {
                aiAvatar.className = 'fas fa-image';
            }
        });

        // Load saved model preference
        const savedModel = localStorage.getItem('selectedModel');
        if (savedModel) {
            document.getElementById('modelSelector').value = savedModel;
        }

        // Add this function to toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 