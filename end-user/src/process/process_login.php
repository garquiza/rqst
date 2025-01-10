<?php
// Start session to handle user login state
session_start();

// Database connection
$host = 'localhost';
$dbname = 'request_db'; // Replace with your database name
$username = 'root';    // Replace with your database username
$password = '';    // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage(),
    ]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberMe']) ? (bool)$_POST['rememberMe'] : false;

    if (empty($email) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill in all required fields.',
        ]);
        exit();
    }

    try {
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM end_users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'activate') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Your account is currently disabled. Please contact support.',
                ]);
                exit();
            }

            // Set session variables for logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];

            // Handle "Remember Me" feature
            if ($rememberMe) {
                setcookie('user_id', $user['id'], time() + (86400 * 30), '/'); // 30 days
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful. Redirecting...',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.',
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'An error occurred: ' . $e->getMessage(),
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.',
    ]);
}
