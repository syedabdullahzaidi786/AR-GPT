<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['otp']) || !isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp']) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['temp_user']['name'],
            $_SESSION['temp_user']['email'],
            $_SESSION['temp_user']['password']
        ]);
        
        // Clear session data
        unset($_SESSION['otp']);
        unset($_SESSION['temp_user']);
        
        // Set user session
        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['user_name'] = $_SESSION['temp_user']['name'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - AI Chat Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light background */
            font-family: 'Google Sans', 'Roboto', sans-serif;
            color: #202124;
        }
        .auth-container {
            max-width: 400px;
            margin: auto;
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary-modern {
            background-color: #1a73e8; /* Google Blue */
            border-color: #1a73e8;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary-modern:hover {
            background-color: #1558b3; /* Darker Blue */
            border-color: #1558b3;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .form-control-modern {
            border-radius: 8px;
            border: 1px solid #d1d5db; /* gray-300 */
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control-modern:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.1);
            outline: none;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center py-12 px-4">
    <div class="auth-container w-full">
        <div class="text-center mb-8">
             <h1 class="text-3xl font-bold text-gray-800">Verify Your Email</h1>
             <p class="text-gray-600 mt-2">Please enter the OTP sent to your email address.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form class="space-y-6" method="POST">
            <div class="form-group">
                <label for="otp" class="sr-only">OTP</label>
                <input id="otp" name="otp" type="text" required class="form-control-modern w-full text-center" placeholder="Enter OTP">
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-base font-medium rounded-md text-white btn-primary-modern focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Verify OTP
                </button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 