<?php
// Include database connection
require_once '../config/database.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert new end user
    $stmt = $conn->prepare("INSERT INTO end_users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

    // Execute query and check if successful
    if ($stmt->execute()) {
        // Respond with success message
        echo json_encode([
            'success' => true,
            'message' => 'End user created successfully!'
        ]);
    } else {
        // Respond with error message
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create end user. Please try again later.'
        ]);
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
