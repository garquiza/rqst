<?php
// Start session or include any necessary session handling
session_start();

// Check if a PPMP ID is selected
if (isset($_POST['ppmp_id']) && !empty($_POST['ppmp_id'])) {
    // Get the selected PPMP ID from the form submission
    $ppmp_id = $_POST['ppmp_id'];

    // Connect to the database
    include('src/config/database.php');

    // Sanitize the input to prevent SQL injection
    $ppmp_id = mysqli_real_escape_string($conn, $ppmp_id);

    // Query to get project title
    $ppmpQuery = "SELECT project_title FROM ppmp_list WHERE ppmp_id = '$ppmp_id' AND status = 'approved'";
    $ppmpResult = mysqli_query($conn, $ppmpQuery);

    if ($ppmpResult && mysqli_num_rows($ppmpResult) > 0) {
        // Fetch project title
        $ppmpRow = mysqli_fetch_assoc($ppmpResult);
        $project_title = $ppmpRow['project_title'];

        // Query to fetch items for the selected PPMP
        $ppmpFormQuery = "SELECT general_description, quantity_size, unit_cost FROM ppmp_form WHERE ppmp_id = '$ppmp_id'";
        $ppmpFormResult = mysqli_query($conn, $ppmpFormQuery);

        // Prepare an array to store the items
        $items = [];

        if ($ppmpFormResult && mysqli_num_rows($ppmpFormResult) > 0) {
            while ($formRow = mysqli_fetch_assoc($ppmpFormResult)) {
                // Decode the general_description field if it is JSON encoded
                $descriptions = json_decode($formRow['general_description'], true);
                $quantities = json_decode($formRow['quantity_size'], true); // Assuming quantity_size is an array
                $unit_costs = json_decode($formRow['unit_cost'], true); // Assuming unit_cost is an array

                // Loop through descriptions and match with quantity_size and unit_cost
                if (is_array($descriptions)) {
                    foreach ($descriptions as $index => $description) {
                        $items[] = [
                            'general_description' => $description,
                            'quantity_size' => isset($quantities[$index]) ? $quantities[$index] : '',
                            'unit_cost' => isset($unit_costs[$index]) ? $unit_costs[$index] : ''
                        ];
                    }
                } else {
                    // If descriptions is not an array, just add the data
                    $items[] = [
                        'general_description' => $descriptions,
                        'quantity_size' => isset($quantities[0]) ? $quantities[0] : '',
                        'unit_cost' => isset($unit_costs[0]) ? $unit_costs[0] : ''
                    ];
                }
            }

            // Return the data as JSON
            echo json_encode([
                'success' => true,
                'project_title' => $project_title,
                'items' => $items
            ]);
        } else {
            // No items found for this PPMP
            echo json_encode([
                'success' => false,
                'message' => 'No items found for this PPMP.'
            ]);
        }
    } else {
        // No PPMP found
        echo json_encode([
            'success' => false,
            'message' => 'No approved PPMP found with the selected ID.'
        ]);
    }
} else {
    // No PPMP selected
    echo json_encode([
        'success' => false,
        'message' => 'No PPMP selected.'
    ]);
}
