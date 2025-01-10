<?php
session_start();
header('Content-Type: application/json');

include '../config/pdo.php';
// Retrieve POST data
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['rememberMe']) ? (bool) $_POST['rememberMe'] : false;

// Validate input
if (!$email || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    exit;
}

try {
    // Check if the user exists and retrieve user details
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, status FROM budget_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Check if the user is activated
            if ($user['status'] === 'activate') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];

                // Handle "Remember Me"
                if ($rememberMe) {
                    $stmt = $pdo->prepare("UPDATE budget_users SET remember_me = 1 WHERE id = ?");
                    $stmt->execute([$user['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE budget_users SET remember_me = 0 WHERE id = ?");
                    $stmt->execute([$user['id']]);
                }

                // Respond with success
                echo json_encode(["status" => "success", "message" => "Login successful."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Your account is not activated. Please contact the administrator."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
    exit;
}
