<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Include database connection
include('src/config/database.php');

// Fetch PPMP entries for the dropdown
$ppmpQuery = "SELECT ppmp_id, project_title FROM ppmp_list WHERE status = 'approved'";

// Execute the query to fetch approved PPMP entries
$ppmpResult = mysqli_query($conn, $ppmpQuery);

// Process the form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ppmp_id']) && !empty($_POST['ppmp_id'])) {
        $selected_ppmp_id = $_POST['ppmp_id']; // Get the selected ppmp_id

        // Log the selected PPMP ID for debugging
        error_log("Selected PPMP ID: " . $selected_ppmp_id);

        // Query to fetch items from ppmp_form for the selected PPMP ID
        $ppmpFormItemsQuery = "
            SELECT general_description, quantity_size, unit_cost
            FROM ppmp_form
            WHERE ppmp_id = $selected_ppmp_id
        ";

        // Log the query for debugging
        error_log("Executing PPMP Form Items query: " . $ppmpFormItemsQuery);

        // Execute the query to fetch ppmp_form items
        $ppmpFormItemsResult = mysqli_query($conn, $ppmpFormItemsQuery);

        // Check if the query executed successfully
        if ($ppmpFormItemsResult) {
            $ppmpFormItems = [];

            // Fetch the results and store them in the array
            while ($row = mysqli_fetch_assoc($ppmpFormItemsResult)) {
                $ppmpFormItems[] = $row;
            }

            // Log the fetched items for debugging
            error_log("Fetched PPMP Form items: " . json_encode($ppmpFormItems));

            // Check if any results were returned
            if (empty($ppmpFormItems)) {
                error_log("No items found for PPMP ID: " . $selected_ppmp_id);
                echo "No approved items found.";
            } else {
                // Output the fetched items (you can use this for display)
                echo "<pre>";
                print_r($ppmpFormItems);
                echo "</pre>";
            }
        } else {
            // Log any errors that occur during the execution of the query
            error_log("Error executing PPMP Form Items query: " . mysqli_error($conn));
            echo "Error executing query: " . mysqli_error($conn);
        }
    } else {
        error_log("No PPMP ID selected.");
        echo "Please select a PPMP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Purchase Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <h1 class="mb-4">Create Purchase Request</h1>
        <div class="form-section">
            <form id="ppmpForm">
                <!-- Select Approved PPMP -->
                <label for="ppmp_id" class="form-label">Select Approved PPMP:</label>
                <select class="form-select" id="ppmp_id" name="ppmp_id" required>
                    <option value="">Select Approved PPMP</option>
                    <?php
                    // Check if the query executed successfully
                    if ($ppmpResult) {
                        // Fetch and populate the dropdown with approved PPMP entries
                        while ($ppmpRow = mysqli_fetch_assoc($ppmpResult)): ?>
                            <option value="<?php echo $ppmpRow['ppmp_id']; ?>">
                                <?php echo htmlspecialchars($ppmpRow['project_title']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php } else {
                        echo "<option value=''>No approved PPMPs found</option>";
                    } ?>
                </select>

            </form>

            <div class="row g-3">
                <!-- Department -->
                <div class="col-md-6">
                    <label for="department" class="form-label">Department:</label>
                    <input type="text" class="form-control" id="department" name="department" required>
                </div>
                <!-- Section -->
                <div class="col-md-6">
                    <label for="section" class="form-label">Section:</label>
                    <input type="text" class="form-control" id="section" name="section" required>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <!-- Select Item Dropdown -->
                <div class="col-md-12">
                    <label class="form-label">Select Item:</label>
                    <select class="form-select" id="general_item" name="general_item" required>
                        <option value="">Select Item</option>
                        <!-- Dropdown options will be dynamically populated -->
                    </select>
                </div>
            </div>


            <!-- Quantity and Unit Cost inputs follow -->
            <div class="row g-3 mt-3">
                <!-- Quantity -->
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity:</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                </div>

                <div class="col-md-4">
                    <label for="unit_cost" class="form-label">Unit Cost:</label>
                    <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0" step="0.01" readonly>
                </div>

            </div>


            <!-- Total Cost -->
            <div class="col-md-4">
                <label for="total_cost" class="form-label">Total Cost:</label>
                <input type="number" class="form-control" id="total_cost" name="total_cost" readonly>
            </div>
        </div>


        <div class="row g-3 mt-4">
            <!-- Purpose -->
            <div class="col-md-12">
                <label for="purpose" class="form-label">Purpose:</label>
                <input type="text" class="form-control" id="purpose" name="purpose" required>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="row g-3 mt-4">
            <div class="col-md-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary me-2" id="submitBtn">Submit</button>
                <a href="pr.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
    </div>
    <script>
        // Wait for the page to load
        document.addEventListener("DOMContentLoaded", function() {

            // Event listener for when PPMP ID is changed
            document.getElementById('ppmp_id').addEventListener('change', function() {
                var ppmp_id = this.value; // Get the selected PPMP ID

                if (ppmp_id) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'process_ppmp.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText); // Parse the JSON response

                            var dropdown = document.getElementById('general_item');
                            dropdown.innerHTML = '<option value="">Select Item</option>'; // Clear existing options

                            if (response.success) {
                                console.log('Selected PPMP: ' + response.project_title);

                                response.items.forEach(function(item) {
                                    var option = document.createElement('option');
                                    option.value = JSON.stringify(item); // Store item as JSON string in the value
                                    option.textContent = item.general_description; // Use general_description as display text
                                    dropdown.appendChild(option);
                                });
                            } else {
                                var option = document.createElement('option');
                                option.value = '';
                                option.textContent = response.message;
                                dropdown.appendChild(option);
                            }
                        }
                    };
                    xhr.send('ppmp_id=' + ppmp_id); // Send PPMP ID to the backend
                } else {
                    document.getElementById('general_item').innerHTML = '<option value="">Select Item</option>';
                }
            });

            // Event listener for when the general item is selected
            document.getElementById('general_item').addEventListener('change', function() {
                var selectedItem = this.value;

                if (selectedItem) {
                    var itemData = JSON.parse(selectedItem); // Parse JSON string from value

                    // Extract quantity_size and unit_cost for the selected general description
                    var quantitySize = itemData.quantity_size;
                    var unitCost = itemData.unit_cost;

                    // If quantity_size and unit_cost are arrays, use only the first item
                    if (Array.isArray(quantitySize)) {
                        quantitySize = quantitySize[0];
                    }
                    if (Array.isArray(unitCost)) {
                        unitCost = unitCost[0];
                    }

                    // Populate quantity and unit cost
                    document.getElementById('quantity').value = quantitySize || ''; // Populate quantity
                    document.getElementById('unit_cost').value = unitCost || ''; // Populate unit cost

                    // Calculate total cost (quantity * unit_cost)
                    var totalCost = parseFloat(quantitySize) * parseFloat(unitCost);
                    document.getElementById('total_cost').value = isNaN(totalCost) ? '' : totalCost.toFixed(2); // Display total cost
                } else {
                    document.getElementById('quantity').value = '';
                    document.getElementById('unit_cost').value = '';
                    document.getElementById('total_cost').value = ''; // Clear total cost if no item is selected
                }
            });

        });
        document.getElementById('submitBtn').addEventListener('click', function() {
            var ppmp_id = document.getElementById('ppmp_id').value;
            var department = document.getElementById('department').value;
            var section = document.getElementById('section').value;
            var general_item = document.getElementById('general_item').value; // This will contain the JSON string
            var quantity = document.getElementById('quantity').value;
            var unit_cost = document.getElementById('unit_cost').value;
            var purpose = document.getElementById('purpose').value;

            // Check if an item is selected
            if (general_item) {
                // Parse the general_item string to get the general_description
                var item = JSON.parse(general_item);
                general_item = item.general_description; // Only extract general_description
            } else {
                console.error('No item selected');
                return; // Exit if no item is selected
            }

            // Create a form data object to submit the data
            var formData = new FormData();
            formData.append('ppmp_id', ppmp_id);
            formData.append('department', department);
            formData.append('section', section);
            formData.append('general_item', general_item); // Pass only general_description
            formData.append('quantity', quantity);
            formData.append('unit_cost', unit_cost);
            formData.append('purpose', purpose);

            // Send the data to the PHP script via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/rqst/end-user/src/process/add_pr.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    // Check if response indicates success
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: 'Purchase Request Created Successfully!',
                            icon: 'success',
                            timer: 2500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirect after 5 seconds
                            window.location.href = 'pr.php';
                        });
                    } else {
                        // Show error message
                        Swal.fire('Error!', response.message, 'error');
                    }
                } else {
                    // Handle error when status is not 200
                    Swal.fire('Error!', 'There was an issue with the request. Please try again later.', 'error');
                }
            };
            xhr.send(formData); // Send the data
        });
    </script>
</body>

</html>