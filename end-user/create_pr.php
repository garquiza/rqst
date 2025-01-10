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

// Query to fetch inventory items
$query = "SELECT inventory_id, item_name, item_description, unit_cost FROM inventory";
$result = mysqli_query($conn, $query);

// Query to fetch approved PPMP entries
$ppmpQuery = "SELECT ppmp_id, project_title FROM ppmp_list WHERE status = 'approved'";
$ppmpResult = mysqli_query($conn, $ppmpQuery);
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
            <form id="purchaseRequestForm" method="post">
                <div class="row g-3 mt-3">
                    <!-- Select Approved PPMP -->
                    <div class="col-md-12 mb-4">
                        <label for="ppmp_id" class="form-label">Select Approved PPMP:</label>
                        <select class="form-select" id="ppmp_id" name="ppmp_id" required>
                            <option value="">Select Approved PPMP</option>
                            <?php while ($ppmpRow = mysqli_fetch_assoc($ppmpResult)): ?>
                                <option value="<?php echo $ppmpRow['ppmp_id']; ?>">
                                    <?php echo htmlspecialchars($ppmpRow['project_title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

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
                    <!-- Select Item -->
                    <div class="col-md-12">
                        <label for="inventory_item" class="form-label">Select Item:</label>
                        <select class="form-select" id="inventory_item" name="inventory_item" required>
                            <option value="">Select Item</option>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <option value="<?php echo $row['inventory_id']; ?>" data-unit-cost="<?php echo $row['unit_cost']; ?>">
                                    <?php echo htmlspecialchars($row['item_name']); ?> - <?php echo htmlspecialchars($row['item_description']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <!-- Quantity -->
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <!-- Unit Cost (from selected item) -->
                    <div class="col-md-4">
                        <label for="unit_cost" class="form-label">Unit Cost:</label>
                        <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0" step="0.01" readonly>
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

                <div class="row g-3 mt-4">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary me-2" id="submitBtn">Submit</button>
                        <a href="pr.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically calculate Total Cost based on Quantity and Unit Cost
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('inventory_item').addEventListener('change', updateUnitCost);

        function calculateTotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const unitCost = parseFloat(document.getElementById('unit_cost').value) || 0;
            document.getElementById('total_cost').value = (quantity * unitCost).toFixed(2);
        }

        function updateUnitCost() {
            const selectedItem = document.getElementById('inventory_item').selectedOptions[0];
            const unitCost = selectedItem.getAttribute('data-unit-cost');
            document.getElementById('unit_cost').value = unitCost;
            calculateTotal(); // Recalculate total cost based on new unit cost
        }

        document.getElementById('submitBtn').addEventListener('click', function() {
            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to submit this purchase request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'No, Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Get the form data
                    const form = document.getElementById('purchaseRequestForm');
                    const formData = new FormData(form);

                    // Submit the form via AJAX to add_pr.php
                    fetch('src/process/add_pr.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json()) // Assuming the response is JSON
                        .then(data => {
                            // Check the response status
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(() => {
                                    window.location.href = 'pr.php';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed!',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again later.'
                            });
                        });
                }
            });
        });
    </script>
</body>

</html>