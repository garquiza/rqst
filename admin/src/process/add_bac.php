<?php
// Include database connection
require_once '../config/pdo.php';

// Initialize the response array
$response = ['success' => false, 'message' => ''];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $permissions = isset($_POST['permissions']) ? implode(',', $_POST['permissions']) : '';

    // Password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $check_email_query = "SELECT id FROM bac_users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($check_email_query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $response['message'] = 'Email is already taken.';
    } else {
        // Insert the new BAC user into the database
        try {
            $insert_query = "INSERT INTO bac_users (first_name, last_name, email, password, permission_access) 
                            VALUES (:first_name, :last_name, :email, :password, :permissions)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':permissions', $permissions);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'BAC user created successfully!';
            } else {
                $response['message'] = 'There was an error creating the user. Please try again.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Send the response as JSON
echo json_encode($response);
