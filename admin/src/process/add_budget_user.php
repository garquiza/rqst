<?php
// Start session
session_start();

// Include database connection
require_once '../config/pdo.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // Prepare SQL query to insert data into the database
    $query = "INSERT INTO budget_users (first_name, last_name, email, password, status) 
              VALUES (:first_name, :last_name, :email, :password, :status)";

    // Prepare statement
    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);

    // Set default status as 'disabled'
    $status = 'disabled';

    // Execute the query and check for success
    if ($stmt->execute()) {
        // Return success message as JSON
        echo json_encode(['success' => true, 'message' => 'Budget User created successfully!']);
    } else {
        // Return error message as JSON
        echo json_encode(['success' => false, 'message' => 'There was an error creating the user.']);
    }
} else {
    // If the request method is not POST, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
