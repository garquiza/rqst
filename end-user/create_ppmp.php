<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');

// Fetch current year
$currentYear = date('Y');

// Generate a random 10-character code
$randomCode = strtoupper(bin2hex(random_bytes(5)));  // Random 10 characters (hexadecimal format)

// Fetch categories from the database
$query = "SELECT * FROM categories";
$result = mysqli_query($conn, $query);
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create PPMP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="src/css/ppmp.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="header-card mb-4">
            <h1 class="display-5 mb-2">Create PPMP</h1>
            <p class="text-light">Fill out the form below to create a new PPMP.</p>
        </div>

        <div class="form-container position-relative">
            <form id="create-ppmp-form">
                <!-- Year and Project Title -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="year">Year:</label>
                        <input type="text" id="year" name="year" value="<?php echo $currentYear; ?>" required readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="project_title">Project Title:</label>
                        <input type="text" id="project_title" name="project_title" required>
                    </div>
                </div>

                <!-- Code -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="code">Code:</label>
                        <input type="text" id="code" name="code" value="<?php echo $randomCode; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="mode_of_procurement">Mode of Procurement</label>
                        <select id="mode_of_procurement" name="mode_of_procurement" required>
                            <option value="Competitive Bidding">Competitive Bidding</option>
                            <option value="Limited Source Bidding">Limited Source Bidding</option>
                            <option value="Direct Contracting">Direct Contracting</option>
                            <option value="Repeat Order">Repeat Order</option>
                            <option value="Shopping">Shopping</option>
                            <option value="NP-53.1 Two Failed Biddings">NP-53.1 Two Failed Biddings</option>
                            <option value="NP-53.2 Emergency Cases">NP-53.2 Emergency Cases</option>
                            <option value="Emergency Procurement under the Bayanihan Act">Emergency Procurement under the Bayanihan Act</option>
                            <option value="NP-53.3 Take-Over of Contracts">NP-53.3 Take-Over of Contracts</option>
                            <option value="NP-53.4 Adjacent or Contiguous">NP-53.4 Adjacent or Contiguous</option>
                            <option value="NP-53.5 Agency-to-Agency">NP-53.5 Agency-to-Agency</option>
                            <option value="NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services">NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services</option>
                            <option value="NP-53.7 Highly Technical Consultants">NP-53.7 Highly Technical Consultants</option>
                            <option value="NP-53.8 Defense Cooperation Agreement">NP-53.8 Defense Cooperation Agreement</option>
                            <option value="NP-53.9 - Small Value Procurement">NP-53.9 - Small Value Procurement</option>
                            <option value="NP-53.10 Lease of Real Property and Venue">NP-53.10 Lease of Real Property and Venue</option>
                            <option value="NP-53.11 NGO Participation">NP-53.11 NGO Participation</option>
                            <option value="NP-53.12 Community Participation">NP-53.12 Community Participation</option>
                            <option value="NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions">NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions</option>
                            <option value="NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets">NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets</option>
                            <option value="Others - Foreign-funded procurements">Others - Foreign-funded procurements</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="estimated_budget">Estimated Budget:</label>
                        <input type="text" id="estimated_budget" name="estimated_budget" required readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-4">Schedule / Milestone of Activities:</label>
                    <div class="schedule-checkboxes">
                        <label><input type="checkbox" name="schedule[]" value="January"> January</label>
                        <label><input type="checkbox" name="schedule[]" value="February"> February</label>
                        <label><input type="checkbox" name="schedule[]" value="March"> March</label>
                        <label><input type="checkbox" name="schedule[]" value="April"> April</label>
                        <label><input type="checkbox" name="schedule[]" value="May"> May</label>
                        <label><input type="checkbox" name="schedule[]" value="June"> June</label>
                        <label><input type="checkbox" name="schedule[]" value="July"> July</label>
                        <label><input type="checkbox" name="schedule[]" value="August"> August</label>
                        <label><input type="checkbox" name="schedule[]" value="September"> September</label>
                        <label><input type="checkbox" name="schedule[]" value="October"> October</label>
                        <label><input type="checkbox" name="schedule[]" value="November"> November</label>
                        <label><input type="checkbox" name="schedule[]" value="December"> December</label>
                    </div>
                </div>

                <!-- Table for Items -->
                <table class="table" id="items-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>General Description(Items)</th>
                            <th>Unit of Measurement</th>
                            <th>Quantity / Size</th>
                            <th>Unit Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="category[]" class="form-control category-dropdown" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category) { ?>
                                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <select name="general_description[]" class="form-control item-dropdown" required>
                                    <!-- Items will be dynamically populated here -->
                                </select>
                            </td>
                            <td><input type="text" name="unit_measurement[]" class="form-control" required></td>
                            <td><input type="number" name="quantity_size[]" class="form-control" required></td>
                            <td><input type="number" name="unit_cost[]" class="form-control" required></td>
                            <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Add Row Button -->
                <div class="mb-4 text-center">
                    <button type="button" id="add-row" class="btn btn-success">Add Item</button>
                </div>



                <!-- Submit Button -->
                <div class="mb-4 text-center">
                    <button type="submit">CREATE</button>
                </div>
            </form>
        </div>
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

            // Event listener for category change (use event delegation to handle dynamic rows)
            $(document).on('change', '.category-dropdown', function() {
                var categoryId = $(this).val(); // Get the selected category_id

                var itemDropdown = $(this).closest('tr').find('.item-dropdown'); // Find the corresponding item dropdown in the same row

                // Check if category_id is valid
                if (categoryId) {
                    $.ajax({
                        url: 'src/process/get_items.php',
                        type: 'GET',
                        data: {
                            category_id: categoryId
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log(response); // Add this to debug
                            if (response.status === 'success') {
                                var items = response.items;
                                var itemOptions = '';

                                items.forEach(function(item) {
                                    itemOptions += '<option value="' + item.item_id + '">' + item.item_name + '</option>';
                                });

                                itemDropdown.html(itemOptions);
                            } else {
                                alert('Error fetching items: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            console.error("Response:", xhr.responseText); // Log the response from the server
                            alert('Something went wrong while fetching items.');
                        }
                    });
                } else {
                    itemDropdown.html('<option value="">Select Item</option>'); // Clear item dropdown if no category selected
                }
            });

            // Add a new row when the 'Add Item' button is clicked
            $('#add-row').on('click', function() {
                var newRow = `
            <tr class="item-row">
                <td>
                    <select name="category[]" class="form-control category-dropdown" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category) { ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <select name="general_description[]" class="form-control item-dropdown" required>
                        <!-- Items will be dynamically populated here -->
                    </select>
                </td>
                <td><input type="text" name="unit_measurement[]" class="form-control" required></td>
                <td><input type="number" name="quantity_size[]" class="form-control" required></td>
                <td><input type="number" name="unit_cost[]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
            </tr>
        `;
                $('#items-table tbody').append(newRow);
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

        // Handle form submission
        $('#create-ppmp-form').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            var formData = $(this).serialize(); // Get all form data
            console.log(formData); // Debug: Check serialized data

            $.ajax({
                url: 'src/process/add_ppmp.php',
                type: 'POST',
                data: formData,
                dataType: 'json', // Expecting JSON response
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'PPMP Created!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'ppmp_list.php'; // Redirect to PPMP list page after success
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
</body>

</html>