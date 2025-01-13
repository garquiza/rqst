<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../bac/src/config/database.php';

// Fetch AOQ records
$aoqs = [];
$sql_aoq = "SELECT aoq_id, project_title FROM abstract_of_quotation";
$result_aoq = $conn->query($sql_aoq);
if ($result_aoq->num_rows > 0) {
    while ($row = $result_aoq->fetch_assoc()) {
        $aoqs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAC - Abstract of Quotation Next</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card mb-4">
                <h1>Select Abstract of Quotation</h1>
                <p>Fill out the details below</p>
            </div>

            <!-- AOQ Selection Form -->
            <form id="aoq-selection-form">
                <div class="mb-4">
                    <label for="aoq" class="form-label">Select AOQ</label>
                    <select id="aoq" name="aoq" class="form-control" required>
                        <option value="">Select an AOQ</option>
                        <?php foreach ($aoqs as $aoq): ?>
                            <option value="<?php echo $aoq['aoq_id']; ?>">
                                <?php echo htmlspecialchars($aoq['project_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" class="btn btn-primary" id="load-aoq">Load AOQ Details</button>
            </form>

            <!-- Specifications Table -->
            <div id="specifications-section" class="mt-4" style="display: none;">
                <h2>Specifications</h2>
                <table class="table table-bordered" id="specifications-table">
                    <thead>
                        <tr>
                            <th>Specification</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="form-control" name="specification[]" required></td>
                            <td><input type="number" class="form-control" name="quantity[]" required></td>
                            <td><input type="text" class="form-control" name="unit[]" required></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bidders Table -->
            <div id="bidders-section" class="mt-4" style="display: none;">
                <h2>Bidders</h2>
                <table class="table table-bordered" id="bidders-table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Bidders Specification</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="form-control" name="company_name[]" required></td>
                            <td><input type="text" class="form-control" name="bidders_specification[]" required></td>
                            <td><input type="number" class="form-control" name="bidders_quantity[]" required></td>
                            <td><input type="number" class="form-control" name="unit_price[]" required></td>
                            <td><input type="number" class="form-control" name="total_price[]" readonly></td>
                            <td><button type="button" class="btn btn-danger remove-bidder">Remove</button> </td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary" id="add-bidder">Add Bidder</button>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary mt-4" id="submit-form">Submit</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Load AOQ details when selected
            $('#load-aoq').on('click', function() {
                const aoqId = $('#aoq').val();
                if (aoqId) {
                    $('#specifications-section').show();
                    $('#bidders-section').show();
                } else {
                    alert('Please select an AOQ.');
                }
            });

            // Add bidder row
            $('#add-bidder').on('click', function() {
                $('#bidders-table tbody').append(`
                    <tr>
                        <td><input type="text" class="form-control" name="company_name[]" required></td>
                        <td><input type="text" class="form-control" name="bidders_specification[]" required></td>
                        <td><input type="number" class="form-control" name="bidders_quantity[]" required></td>
                        <td><input type="number" class="form-control" name="unit_price[]" required></td>
                        <td><input type="number" class="form-control" name="total_price[]" readonly></td>
                        <td><button type="button" class="btn btn-danger remove-bidder">Remove</button></td>
                    </tr>
                `);
            });

            // Remove bidder row
            $(document).on('click', '.remove-bidder', function() {
                $(this).closest('tr').remove();
            });

            // Calculate total price on quantity or unit price change
            $(document).on('input', 'input[name="bidders_quantity[]"], input[name="unit_price[]"]', function() {
                const row = $(this).closest('tr');
                const quantity = row.find('input[name="bidders_quantity[]"]').val();
                const unitPrice = row.find('input[name="unit_price[]"]').val();
                const totalPrice = (quantity * unitPrice) || 0;
                row.find('input[name="total_price[]"]').val(totalPrice.toFixed(2));
            });

            // Handle form submission with SweetAlert
            $('#submit-form').on('click', function(e) {
                e.preventDefault();

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to submit the AOQ specifications?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, submit it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Gather form data
                        const formData = $('#aoq-selection-form').serializeArray();
                        formData.push({
                            name: 'aoq',
                            value: $('#aoq').val()
                        }); // Add AOQ ID to form data
                        // Add specifications and bidders data
                        $('#specifications-table tbody tr').each(function() {
                            const specification = $(this).find('input[name="specification[]"]').val();
                            const quantity = $(this).find('input[name="quantity[]"]').val();
                            const unit = $(this).find('input[name="unit[]"]').val();
                            formData.push({
                                name: 'specification[]',
                                value: specification
                            });
                            formData.push({
                                name: 'quantity[]',
                                value: quantity
                            });
                            formData.push({
                                name: 'unit[]',
                                value: unit
                            });
                        });
                        $('#bidders-table tbody tr').each(function() {
                            const companyName = $(this).find('input[name="company_name[]"]').val();
                            const biddersSpecification = $(this).find('input[name="bidders_specification[]"]').val();
                            const biddersQuantity = $(this).find('input[name="bidders_quantity[]"]').val();
                            const unitPrice = $(this).find('input[name="unit_price[]"]').val();
                            const totalPrice = $(this).find('input[name="total_price[]"]').val();
                            formData.push({
                                name: 'company_name[]',
                                value: companyName
                            });
                            formData.push({
                                name: 'bidders_specification[]',
                                value: biddersSpecification
                            });
                            formData.push({
                                name: 'bidders_quantity[]',
                                value: biddersQuantity
                            });
                            formData.push({
                                name: 'unit_price[]',
                                value: unitPrice
                            });
                            formData.push({
                                name: 'total_price[]',
                                value: totalPrice
                            });
                        });

                        // Send the data via AJAX
                        $.ajax({
                            type: 'POST',
                            url: 'src/process/add_aoq_specification.php',
                            data: formData,
                            success: function(response) {
                                // Handle success response
                                Swal.fire({
                                    title: 'Submitted!',
                                    text: 'Your AOQ specifications have been submitted successfully.',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Proceed to RESO',
                                    cancelButtonText: 'Download'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Redirect to RESO page
                                        window.location.href = 'reso.php';
                                    } else {
                                        // Trigger download (you can adjust the URL to your download endpoint)
                                        window.location.href = 'src/process/download_aoq.php'; // Adjust this URL as needed
                                    }
                                });

                                // Optionally, you can reset the form or hide sections
                                $('#aoq-selection-form')[0].reset();
                                $('#specifications-section').hide();
                                $('#bidders-section').hide();
                            },
                            error: function(xhr, status, error) {
                                // Handle error response
                                Swal.fire(
                                    'Error!',
                                    'There was an error submitting your AOQ specifications. Please try again.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>