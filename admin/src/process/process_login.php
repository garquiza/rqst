<?php
// Include your database connection file (adjust as necessary)
require_once '../config/pdo.php'; // Make sure to replace with your actual connection file

// Get the POST data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['rememberMe']) ? 1 : 0;

// Function to verify the password
function verifyPassword($inputPassword, $storedPassword)
{
    return password_verify($inputPassword, $storedPassword);
}

// Check if both email and password are provided
if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email and password are required.'
    ]);
    exit;
}

try {
    // Prepare and execute the query to check if the user exists
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && verifyPassword($password, $user['password'])) {
        // Successful login
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];

        // Set remember me cookie if selected
        if ($rememberMe) {
            setcookie('user_id', $user['id'], time() + (86400 * 30), "/"); // 30 days
            setcookie('remember_me', true, time() + (86400 * 30), "/");
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful. Redirecting...',
        ]);
    } else {
        // Invalid credentials
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing your request.'
    ]);
    error_log($e->getMessage()); // Log the error for debugging purposes
}
