<?php
// Include your database connection file
require_once '../config/database.php';

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $itemNo = $_POST['item_no'];
    $itemName = $_POST['item_name'];
    $unitOfMeasurement = $_POST['unit_of_measurement'];
    $categoryId = $_POST['new_category_id'];

    // Basic validation
    if (empty($itemNo) || empty($itemName) || empty($unitOfMeasurement) || empty($categoryId)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // SQL to insert the item into the database
    $sql = "INSERT INTO items (item_no, item_name, unit_of_measurement, category_id) VALUES (?, ?, ?, ?)";

    // Prepare the SQL statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("sssi", $itemNo, $itemName, $unitOfMeasurement, $categoryId);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Item added successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error adding item."]);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Database query error."]);
    }

    // Close the database connection
    $conn->close();
}
