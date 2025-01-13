<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');

// Check if ppmp_form_id is set in the URL
if (isset($_GET['ppmp_form_id'])) {
    $ppmp_form_id = intval($_GET['ppmp_form_id']);

    // Fetch the corresponding ppmp_id from ppmp_form table
    $query = "SELECT ppmp_id FROM ppmp_form WHERE ppmp_form_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmp_form_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the ppmp_form_id exists
    if ($result->num_rows === 0) {
        die("PPMP Form not found.");
    }

    // Fetch the ppmp_id related to the ppmp_form_id
    $ppmpForm = $result->fetch_assoc();
    $ppmp_id = $ppmpForm['ppmp_id'];

    // Now, fetch the corresponding PPMP data from ppmp_list using ppmp_id
    $query = "SELECT * FROM ppmp_list WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the PPMP exists
    if ($result->num_rows === 0) {
        die("PPMP not found.");
    }

    // Fetch the PPMP data
    $ppmp = $result->fetch_assoc();
} else {
    die("Invalid request.");
}


// Handle form submission for ppmp_form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $year = $_POST['year'];
    $code = $_POST['code'];
    $general_description = $_POST['general_description'];
    $quantity_size = $_POST['quantity_size'];
    $estimated_budget = $_POST['estimated_budget'];

    // Handle schedule checkboxes
    $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : [];
    $schedule_json = json_encode($schedule); // Convert schedule to JSON

    // Convert arrays to JSON for items (general_description, quantity_size, unit_measurement, unit_cost)
    $general_description_json = json_encode($general_description);
    $quantity_size_json = json_encode($quantity_size);
    $unit_measurement_json = json_encode($_POST['unit_measurement']);
    $unit_cost_json = json_encode($_POST['unit_cost']);
}

// Fetch related ppmp_form data
$formQuery = "SELECT * FROM ppmp_form WHERE ppmp_id = ?";
$formStmt = $conn->prepare($formQuery);
$formStmt->bind_param("i", $ppmp_id);
$formStmt->execute();
$formResult = $formStmt->get_result();
$formData = $formResult->fetch_assoc();

// Fetch categories from the database
$categoryQuery = "SELECT * FROM categories";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categoryList = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update PPMP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="src/css/ppmp_list.css">
    <link rel="stylesheet" href="src/css/ppmp.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #mode_of_procurement {
            background-color: #f8f9fa;
            /* Light gray background */
            border: 1px solid #ced4da;
            /* Border color */
            border-radius: 0.25rem;
            /* Rounded corners */
            padding: 0.375rem 1.2rem;
            /* Padding inside the dropdown */
            font-size: 1rem;
            /* Font size */
        }

        #mode_of_procurement:focus {
            border-color: #80bdff;
            /* Blue border on focus */
            outline: none;
            /* Remove outline */
        }

        #mode_of_procurement option {
            font-size: 1rem;
            /* Font size of options */
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <h1 class="header-card mb-4 text-center">Update PPMP</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>PPMP Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="col-md-6 mb-3">
                            <label for="project_title" class="form-label">Project Title</label>
                            <input type="text" class="form-control" id="project_title" name="project_title" value="<?php echo htmlspecialchars($formData['project_title'] ?? $ppmp['project_title']); ?>" required>
                        </div>
                        <p><strong>Approver:</strong> <?php echo htmlspecialchars($ppmp['approver']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($ppmp['status'])); ?></p>
                        <p><strong>Date Created:</strong> <?php echo date("F j, Y", strtotime($ppmp['date_created'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2>Edit PPMP Form Details</h2>
        <form method="POST" action="/admin/src/process/submit_form.php">
            <input type="hidden" name="ppmp_form_id" value="<?php echo htmlspecialchars($ppmp_form_id); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="year" class="form-label">Year</label>
                    <input type="text" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($formData['year']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($formData['code']); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="mode_of_procurement">Mode of Procurement</label>
                    <select id="mode_of_procurement" name="mode_of_procurement" required>
                        <option value="Competitive Bidding" <?php echo ($formData['mode_of_procurement'] == 'Competitive Bidding') ? 'selected' : ''; ?>>Competitive Bidding</option>
                        <option value="Limited Source Bidding" <?php echo ($formData['mode_of_procurement'] == 'Limited Source Bidding') ? 'selected' : ''; ?>>Limited Source Bidding</option>
                        <option value="Direct Contracting" <?php echo ($formData['mode_of_procurement'] == 'Direct Contracting') ? 'selected' : ''; ?>>Direct Contracting</option>
                        <option value="Repeat Order" <?php echo ($formData['mode_of_procurement'] == 'Repeat Order') ? 'selected' : ''; ?>>Repeat Order</option>
                        <option value="Shopping" <?php echo ($formData['mode_of_procurement'] == 'Shopping') ? 'selected' : ''; ?>>Shopping</option>
                        <option value="NP-53.1 Two Failed Biddings" <?php echo ($formData['mode_of_procurement'] == 'NP-53.1 Two Failed Biddings') ? 'selected' : ''; ?>>NP-53.1 Two Failed Biddings</option>
                        <option value="NP-53.2 Emergency Cases" <?php echo ($formData['mode_of_procurement'] == 'NP-53.2 Emergency Cases') ? 'selected' : ''; ?>>NP-53.2 Emergency Cases</option>
                        <option value="Emergency Procurement under the Bayanihan Act" <?php echo ($formData['mode_of_procurement'] == 'Emergency Procurement under the Bayanihan Act') ? 'selected' : ''; ?>>Emergency Procurement under the Bayanihan Act</option>
                        <option value="NP-53.3 Take-Over of Contracts" <?php echo ($formData['mode_of_procurement'] == 'NP-53.3 Take-Over of Contracts') ? 'selected' : ''; ?>>NP-53.3 Take-Over of Contracts</option>
                        <option value="NP-53.4 Adjacent or Contiguous" <?php echo ($formData['mode_of_procurement'] == 'NP-53.4 Adjacent or Contiguous') ? 'selected' : ''; ?>>NP-53.4 Adjacent or Contiguous</option>
                        <option value="NP-53.5 Agency-to-Agency" <?php echo ($formData['mode_of_procurement'] == 'NP-53.5 Agency-to-Agency') ? 'selected' : ''; ?>>NP-53.5 Agency-to-Agency</option>
                        <option value="NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services" <?php echo ($formData['mode_of_procurement'] == 'NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services') ? 'selected' : ''; ?>>NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services</option>
                        <option value="NP-53.7 Highly Technical Consultants" <?php echo ($formData['mode_of_procurement'] == 'NP-53.7 Highly Technical Consultants') ? 'selected' : ''; ?>>NP-53.7 Highly Technical Consultants</option>
                        <option value="NP-53.8 Defense Cooperation Agreement" <?php echo ($formData['mode_of_procurement'] == 'NP-53.8 Defense Cooperation Agreement') ? 'selected' : ''; ?>>NP-53.8 Defense Cooperation Agreement</option>
                        <option value="NP-53.9 - Small Value Procurement" <?php echo ($formData['mode_of_procurement'] == 'NP-53.9 - Small Value Procurement') ? 'selected' : ''; ?>>NP-53.9 - Small Value Procurement</option>
                        <option value="NP-53.10 Lease of Real Property and Venue" <?php echo ($formData['mode_of_procurement'] == 'NP-53.10 Lease of Real Property and Venue') ? 'selected' : ''; ?>>NP-53.10 Lease of Real Property and Venue</option>
                        <option value="NP-53.11 NGO Participation" <?php echo ($formData['mode_of_procurement'] == 'NP-53.11 NGO Participation') ? 'selected' : ''; ?>>NP-53.11 NGO Participation</option>
                        <option value="NP-53.12 Community Participation" <?php echo ($formData['mode_of_procurement'] == 'NP-53.12 Community Participation') ? 'selected' : ''; ?>>NP-53.12 Community Participation</option>
                        <option value="NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions" <?php echo ($formData['mode_of_procurement'] == 'NP-53.13 UN Agencies, Int\'l Organizations or Intentional Financing Institutions') ? 'selected' : ''; ?>>NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions</option>
                        <option value="NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets" <?php echo ($formData['mode_of_procurement'] == 'NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets') ? 'selected' : ''; ?>>NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets</option>
                        <option value="Others - Foreign-funded procurements" <?php echo ($formData['mode_of_procurement'] == 'Others - Foreign-funded procurements') ? 'selected' : ''; ?>>Others - Foreign-funded procurements</option>
                    </select>
                </div>
            </div>


            <div class="mb-3">
                <label for="estimated_budget" class="form-label">Estimated Budget</label>
                <input type="number" step="0.01" class="form-control" id="estimated_budget" name="estimated_budget" value="<?php echo htmlspecialchars($formData['estimated_budget']); ?>" required>
            </div>

            <h3>Schedule</h3>
            <div class="row mb-3">
                <?php
                // Decode the JSON schedule to get selected months
                $selectedMonths = json_decode($formData['schedule'], true);
                $months = [
                    'January',
                    'February',
                    'March',
                    'April',
                    'May',
                    'June',
                    'July',
                    'August',
                    'September',
                    'October',
                    'November',
                    'December'
                ];
                foreach ($months as $month) {
                    $checked = in_array($month, $selectedMonths) ? 'checked' : '';
                    echo '<div class="col-md-4 form-check">';
                    echo '<input class="form-check-input" type="checkbox" name="schedule[]" value="' . $month . '" id="' . strtolower($month) . '" ' . $checked . '>';
                    echo '<label class="form-check-label" for="' . strtolower($month) . '">' . $month . '</label>';
                    echo '</div>';
                }
                ?>
            </div>
            <!-- Table for Items -->
            <table class="table" id="items-table">
                <thead>
                    <tr>
                        <th>General Description (Items)</th>
                        <th>Unit of Measurement</th>
                        <th>Quantity / Size</th>
                        <th>Unit Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch rows from the ppmp_form table
                    $itemsQuery = "SELECT * FROM ppmp_form WHERE ppmp_id = ?"; // Ensure we filter based on the ppmp_id
                    $stmt = $conn->prepare($itemsQuery);
                    $stmt->bind_param("i", $ppmp_id); // Bind the ppmp_id parameter to the query
                    $stmt->execute();
                    $itemsResult = $stmt->get_result();

                    // Loop through the results and display them in the table
                    while ($item = $itemsResult->fetch_assoc()) {
                        // Decode the JSON encoded fields
                        $general_descriptions = json_decode($item['general_description']);
                        $quantity_sizes = json_decode($item['quantity_size']);
                        $unit_measurements = json_decode($item['unit_measurement']);
                        $unit_cost_array = json_decode($item['unit_cost']);



                        // Loop through each item in the arrays and display them on separate rows
                        $num_items = count($general_descriptions); // Assuming all arrays have the same length
                        for ($i = 0; $i < $num_items; $i++) {
                            $general_description = $general_descriptions[$i];
                            $quantity_size = $quantity_sizes[$i];
                            $unit_measurement = $unit_measurements[$i];
                            $unit_cost = $unit_cost_array[$i];
                    ?>
                            <tr class="item-row">
                                <td>
                                    <select name="general_description[]" class="form-control item-dropdown" required>
                                        <option value="<?php echo htmlspecialchars($general_description); ?>" selected>
                                            <?php echo htmlspecialchars($general_description); ?>
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="unit_measurement[]" class="form-control" value="<?php echo htmlspecialchars($unit_measurement); ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="quantity_size[]" class="form-control" value="<?php echo htmlspecialchars($quantity_size); ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="unit_cost[]" class="form-control" value="<?php echo htmlspecialchars($unit_cost); ?>" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-row">Remove</button>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            <!-- New Table for Items -->
            <table class="add-table" id="items-table-new">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>General Description (Items)</th>
                        <th>Unit of Measurement</th>
                        <th>Quantity / Size</th>
                        <th>Unit Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="add-item-row">
                        <td>
                            <select name="category[]" class="form-control category-dropdown">
                                <option value="">Select Category</option>
                                <?php foreach ($categoryList as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo $category['category_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="general_description[]" class="form-control item-select-dropdown">
                                <!-- Items will be dynamically populated here -->
                            </select>

                        </td>
                        <td><input type="text" name="unit_measurement[]" class="form-control"></td>
                        <td><input type="number" name="quantity_size[]" class="form-control"></td>
                        <td><input type="number" name="unit_cost[]" class="form-control"></td>
                        <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                    </tr>
                </tbody>
            </table>

            <!-- Add Row Button -->
            <div class="mb-4 text-center">
                <button type="button" id="add-row" class="btn btn-success">Add Item</button>
            </div>





            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Update PPMP Form</button>
                <a href="ppmp_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to calculate the total estimated budget
            function updateEstimatedBudget() {
                let totalBudget = 0;

                // Loop through each row to calculate quantity * unit cost
                $('#items-table tbody tr').each(function() {
                    let quantity = $(this).find('input[name="quantity_size[]"]').val();
                    let unitCost = $(this).find('input[name="unit_cost[]"]').val();

                    // Check if both quantity and unit cost are valid numbers
                    if (quantity && unitCost) {
                        totalBudget += (parseFloat(quantity) * parseFloat(unitCost));
                    }
                });

                // Update the estimated budget field with the calculated total
                $('#estimated_budget').val(totalBudget.toFixed(2)); // Keep it to two decimal places
            }
            // Handle Category Dropdown Change to Fetch Items
            $(document).on('change', '.category-dropdown', function() {
                console.log("Category dropdown change event triggered!"); // Debugging

                var categoryId = $(this).val(); // Get selected category_id
                var itemDropdown = $(this).closest('tr').find('.item-select-dropdown'); // Find corresponding item dropdown in the same row

                console.log("Selected categoryId: ", categoryId); // Debugging

                if (categoryId) {
                    $.ajax({
                        url: 'src/process/get_items.php', // Ensure this path is correct
                        type: 'GET',
                        data: {
                            category_id: categoryId
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log("AJAX Response: ", response); // Debugging

                            if (response.status === 'success') {
                                var items = response.items;
                                console.log("Items retrieved: ", items); // Debugging

                                // Create a Set of existing item IDs in the dropdown to avoid duplicates
                                var existingItems = new Set();
                                itemDropdown.find('option').each(function() {
                                    existingItems.add($(this).val());
                                });

                                var options = ''; // Initialize an empty string for new options

                                items.forEach(function(item) {
                                    if (!existingItems.has(item.item_id)) {
                                        // Add only new items to the options string
                                        options += `<option value="${item.item_id}">${item.item_name}</option>`;
                                    }
                                });

                                // Append the new options to the dropdown
                                itemDropdown.append(options);

                                console.log("New options appended: ", options); // Debugging
                            } else {
                                alert('Error fetching items: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Error in AJAX request: ", status, error);
                            alert('Error fetching items. Please try again.');
                        }
                    });
                }
            });


            const categories = <?php echo json_encode($categoryList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

            // Add a new row when the 'Add Item' button is clicked
            $('#add-row').on('click', function() {
                let categoryOptions = '<option value="">Select Category</option>';
                categories.forEach(category => {
                    categoryOptions += `<option value="${category.category_id}">${category.category_name}</option>`;
                });

                const newRow = `
        <tr class="add-item-row">
            <td>
                <select name="category[]" class="form-control category-dropdown" required>
                    ${categoryOptions}
                </select>
            </td>
            <td>
                <select name="general_description[]" class="form-control item-select-dropdown" required>
                    <!-- Items will be dynamically populated here -->
                </select>
            </td>
            <td><input type="text" name="unit_measurement[]" class="form-control" required></td>
            <td><input type="number" name="quantity_size[]" class="form-control" required></td>
            <td><input type="number" name="unit_cost[]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
        </tr>
    `;
                $('#items-table-new tbody').append(newRow);
            });

            // Recalculate the budget whenever quantity or unit cost changes
            $(document).on('input', 'input[name="quantity_size[]"], input[name="unit_cost[]"]', function() {
                updateEstimatedBudget(); // Recalculate estimated budget
            });

            // Remove a row when the 'Remove' button is clicked
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateEstimatedBudget(); // Recalculate estimated budget after row removal
            });

            // Initialize the budget calculation when the page loads (in case there are existing rows)
            updateEstimatedBudget();
        });




        document.addEventListener('DOMContentLoaded', function() {
            const itemsTable = document.getElementById('items-table');
            const ppmpForm = document.getElementById('ppmpForm');

            if (itemsTable) {
                // Handle table row removal
                itemsTable.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-row')) {
                        const row = e.target.closest('tr');
                        if (document.querySelectorAll('.item-row').length > 1) {
                            row.remove();
                        }
                    }
                });

                // Event delegation for dynamically added rows (handle remove button clicks)
                const itemsTableBody = itemsTable.querySelector('tbody');
                if (itemsTableBody) {
                    itemsTableBody.addEventListener('click', function(e) {
                        if (e.target && e.target.classList.contains('remove-row')) {
                            console.log("Remove button clicked");
                            const row = e.target.closest('tr');
                            if (row) {
                                row.remove();
                                console.log("Row removed");
                            } else {
                                console.log("Row not found");
                            }
                        }
                    });
                }
            } else {
                console.error('Element with id "items-table" not found in the DOM.');
            }


            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Gather form data
                var formData = {
                    'ppmp_form_id': $('input[name="ppmp_form_id"]').val(),
                    'year': $('input[name="year"]').val(),
                    'code': $('input[name="code"]').val(),
                    'project_title': $('input[name="project_title"]').val(),
                    'estimated_budget': $('input[name="estimated_budget"]').val(),
                    'mode_of_procurement': $('select[name="mode_of_procurement"]').val(),
                    'schedule': $('input[name="schedule[]"]:checked').map(function() {
                        return this.value;
                    }).get(), // Collect checked schedule values
                    'general_description': [], // Collect item names here
                    'unit_measurement': [],
                    'quantity_size': [],
                    'unit_cost': []
                };

                // Combine values from both existing and new rows
                $('#items-table tbody tr, #items-table-new tbody tr').each(function() {
                    var generalDescription = $(this)
                        .find('select[name="general_description[]"] option:selected')
                        .text(); // Get the item name (text) from the dropdown
                    var unitMeasurement = $(this).find('input[name="unit_measurement[]"]').val();
                    var quantitySize = $(this).find('input[name="quantity_size[]"]').val();
                    var unitCost = $(this).find('input[name="unit_cost[]"]').val();

                    // Add data only if all required fields are filled
                    if (generalDescription && unitMeasurement && quantitySize && unitCost) {
                        formData.general_description.push(generalDescription);
                        formData.unit_measurement.push(unitMeasurement);
                        formData.quantity_size.push(quantitySize);
                        formData.unit_cost.push(unitCost);
                    }
                });

                // Ensure all required data is present
                if (
                    formData.general_description.length === 0 ||
                    formData.unit_measurement.length === 0 ||
                    formData.quantity_size.length === 0 ||
                    formData.unit_cost.length === 0
                ) {
                    alert('Please fill in all required fields');
                    return; // Stop submission if any data is missing
                }

                // Submit form via AJAX
                $.ajax({
                    url: '/rqst/end-user/src/process/submit_form.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Form submitted successfully!');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response Text:', xhr.responseText);
                        alert('There was an error submitting the form. Please check the console for details.');
                    }
                });



                // Other existing row handling logic (adding, removing rows, etc.)
                $(document).on('click', '.remove-row', function() {
                    $(this).closest('tr').remove();
                });

                $(document).on('click', '.new-remove-row', function() {
                    $(this).closest('tr').remove();
                });
            });
        });
    </script>

</body>

</html>