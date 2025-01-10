<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User  not logged in.']);
    exit();
}

// Include database connection
require_once '../config/pdo.php';

// Handle form submission to update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    $id = intval($_POST['id']); // Make sure to get the user ID from the form

    // Check if the email is unique
    $email_check_sql = "SELECT COUNT(*) FROM budget_users WHERE email = :email AND id != :id";
    $email_check_stmt = $pdo->prepare($email_check_sql);
    $email_check_stmt->execute(['email' => $email, 'id' => $id]);
    $email_exists = $email_check_stmt->fetchColumn();

    if ($email_exists > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit();
    }

    // Update query
    $update_sql = "UPDATE budget_users 
                   SET first_name = :first_name, last_name = :last_name, email = :email, status = :status, updated_at = NOW()
                   WHERE id = :id";

    $update_stmt = $pdo->prepare($update_sql);

    try {
        $update_stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'status' => $status,
            'id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'User  updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
