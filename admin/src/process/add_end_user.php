<?php
// Include database connection
require_once '../config/database.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $sector_name = htmlspecialchars(trim($_POST['sector']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Validate the sector field
    if (empty($sector_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sector is required.'
        ]);
        exit();
    }

    // Fetch the sector_id based on the selected sector_name
    $query = "SELECT id FROM sector WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sector_name);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($sector_id);

    // Check if the sector exists
    if ($stmt->fetch()) {
        $stmt->close();

        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL statement to insert new end user with sector_id
        $stmt = $conn->prepare("INSERT INTO end_users (first_name, last_name, sector_id, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $first_name, $last_name, $sector_id, $email, $hashed_password);

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

        $stmt->close();
    } else {
        // If sector does not exist
        echo json_encode([
            'success' => false,
            'message' => 'Selected sector does not exist.'
        ]);
    }

    // Close the database connection
    $conn->close();
}
