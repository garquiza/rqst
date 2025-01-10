<?php
session_start();
require_once '../config/pdo.php'; // Ensure this points to your database connection script

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rememberMe = isset($_POST['rememberMe']) ? 1 : 0;

    if (empty($email) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email and password are required.'
        ]);
        exit;
    }

    try {
        // Check if the user exists
        $stmt = $pdo->prepare("SELECT * FROM bac_users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ]);
            exit;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify account status
        if ($user['status'] !== 'activate') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Your account is not activated. Please contact the administrator.'
            ]);
            exit;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ]);
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['permission_access'] = $user['permission_access'];

        // Handle Remember Me functionality
        if ($rememberMe) {
            $stmt = $pdo->prepare("UPDATE bac_users SET remember_me = 1 WHERE id = :id");
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("UPDATE bac_users SET remember_me = 0 WHERE id = :id");
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'An error occurred while processing your request.'
        ]);
        error_log($e->getMessage()); // Log error for debugging
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
