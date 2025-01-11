<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');

// Check if ppmp_id is set in the URL
if (isset($_GET['ppmp_id'])) {
    $ppmp_id = intval($_GET['ppmp_id']);

    // Fetch the existing PPMP data
    $query = "SELECT * FROM ppmp_list WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the PPMP exists
    if ($result->num_rows === 0) {
        die("PPMP not found.");
    }

    $ppmp = $result->fetch_assoc();
} else {
    die("Invalid request.");
}

// Handle form submission for ppmp_form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = $_POST['year'];
    $code = $_POST['code'];
    $general_description = $_POST['general_description'];
    $quantity_size = $_POST['quantity_size'];
    $estimated_budget = $_POST['estimated_budget'];

    // Handle schedule checkboxes
    $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : [];
    $schedule_json = json_encode($schedule);

    // Update the ppmp_form data
    $updateQuery = "UPDATE ppmp_form SET year = ?, code = ?, general_description = ?, quantity_size = ?, estimated_budget = ?, schedule = ? WHERE ppmp_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssssi", $year, $code, $general_description, $quantity_size, $estimated_budget, $schedule_json, $ppmp_id);

    if ($updateStmt->execute()) {
        header("Location: ppmp_list.php?message=PPMP form updated successfully");
        exit();
    } else {
        $error = "Error updating PPMP form: " . $conn->error;
    }
}

// Fetch related ppmp_form data
$formQuery = "SELECT * FROM ppmp_form WHERE ppmp_id = ?";
$formStmt = $conn->prepare($formQuery);
$formStmt->bind_param("i", $ppmp_id);
$formStmt->execute();
$formResult = $formStmt->get_result();
$formData = $formResult->fetch_assoc();
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
                        <p><strong>Project Title:</strong> <?php echo htmlspecialchars($ppmp['project_title']); ?></p>
                        <p><strong>Approver:</strong> <?php echo htmlspecialchars($ppmp['approver']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($ppmp['status'])); ?></p>
                        <p><strong>Date Created:</strong> <?php echo date("F j, Y", strtotime($ppmp['date_created'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2>Edit PPMP Form Details</h2>
        <form id="ppmpForm">
            <input type="hidden" name="ppmp_id" value="<?php echo htmlspecialchars($ppmp_id); ?>">
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

            <!-- <div class="mb-3">
                <label for="general_description" class="form-label">General Description</label>
                <textarea class="form-control" id="general_description" name="general_description" rows="3" required><?php echo htmlspecialchars($formData['general_description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="quantity_size" class="form-label">Quantity / Size</label>
                <input type="text" class="form-control" id="quantity_size" name="quantity_size" value="<?php echo htmlspecialchars($formData['quantity_size']); ?>" required>
            </div> -->

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
                        <th>General Description(Items)</th>
                        <th>Unit of Measurement</th>
                        <th>Quantity / Size</th>
                        <th>Unit Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Example: Fetch the rows from the database
                    $itemsQuery = "SELECT * FROM ppmp_form"; // Example query
                    $itemsResult = mysqli_query($conn, $itemsQuery);

                    while ($item = mysqli_fetch_assoc($itemsResult)) {
                    ?>
                        <tr class="item-row">
                            <td>
                                <select name="general_description[]" class="form-control item-dropdown" required>
                                    <!-- Items will be dynamically populated here -->
                                    <option value="<?php echo $item['general_description']; ?>" selected>
                                        <?php echo $item['general_description']; ?>
                                    </option>
                                </select>
                            </td>
                            <td><input type="text" name="unit_measurement[]" class="form-control" value="<?php echo $item['unit_measurement']; ?>" required></td>
                            <td><input type="number" name="quantity_size[]" class="form-control" value="<?php echo $item['quantity_size']; ?>" required></td>
                            <td><input type="number" name="unit_cost[]" class="form-control" value="<?php echo $item['unit_cost']; ?>" required></td>
                            <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Update PPMP Form</button>
                <a href="ppmp_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('ppmpForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save the changes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);

                    fetch('src/process/save_edit_ppmp.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = 'ppmp_list.php'; // Redirect after confirmation
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An unexpected error occurred.',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        });
        document.getElementById('add-row').addEventListener('click', function() {
            const table = document.getElementById('items-table').getElementsByTagName('tbody')[0];
            const newRow = document.querySelector('.item-row').cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(input => input.value = '');
            table.appendChild(newRow);
        });

        document.getElementById('items-table').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                const row = e.target.closest('tr');
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                }
            }
        });
    </script>
</body>

</html>