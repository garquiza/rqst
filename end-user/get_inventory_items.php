<?php
include('src/config/database.php');

if (isset($_GET['ppmp_id'])) {
    $ppmpId = $_GET['ppmp_id'];

    // Fetch items corresponding to the selected PPMP
    $query = "SELECT general_description FROM ppmp_form WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmpId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Decode general_description JSON into an array
        $descriptions = json_decode($row['general_description'], true);
        if (is_array($descriptions)) {
            $items[] = ['general_description' => $descriptions];
        }
    }

    echo json_encode(['items' => $items]);
} else {
    echo json_encode(['items' => []]);
}
