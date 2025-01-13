<?php
// Include your database connection file
require_once '../config/database.php';

// Query to get the highest item number
$sql = "SELECT MAX(CAST(SUBSTRING(item_no, 6) AS UNSIGNED)) AS max_item_no FROM items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $maxItemNo = $row['max_item_no'] ? $row['max_item_no'] + 1 : 1; // Start from 1 if no items exist
    $nextItemNo = "ITEM-" . str_pad($maxItemNo, 4, "0", STR_PAD_LEFT); // Format as ITEM-0001
    echo json_encode(["success" => true, "item_no" => $nextItemNo]);
} else {
    echo json_encode(["success" => false, "message" => "Error fetching item number."]);
}

$conn->close();
