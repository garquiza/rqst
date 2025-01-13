<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/database.php';

// Fetch project titles from rfq table
$projects = [];
$sql = "SELECT rfq_id, project_title FROM rfq";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Get logged-in user's details
$user_id = $_SESSION['user_id'];
$user_name = "";
$sql_user = "SELECT first_name, last_name FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_user = $stmt->get_result();
    if ($result_user && $result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    }
    $stmt->close();
}

if (isset($_POST['rfq_id'])) {
    $rfq_id = intval($_POST['rfq_id']);
    $sql = "SELECT eu.sector, r.approved_budget
            FROM purchase_requests pr
            JOIN end_users eu ON pr.end_user_id = eu.id
            JOIN ppmp_list ppmp ON pr.ppmp_id = ppmp.ppmp_id
            JOIN rfq r ON ppmp.project_title = r.project_title
            WHERE r.rfq_id = ?
            AND pr.status = 'Approved'
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rfq_id);
    $stmt->execute();
    $stmt->bind_result($end_user, $approved_budget);
    $stmt->fetch();

    echo json_encode([
        'end_user' => $end_user ?: 'N/A',
        'approved_budget' => $approved_budget ?: '0'
    ]);
    exit();
}

if (isset($_POST['rfq_id'])) {
    $rfq_id = intval($_POST['rfq_id']);
    $sql = "SELECT eu.sector, rfq.project_title, rfq.quantity, rfq.unit 
            FROM purchase_requests pr
            JOIN end_users eu ON pr.end_user_id = eu.id
            JOIN ppmp_list ppmp ON pr.ppmp_id = ppmp.ppmp_id
            JOIN rfq ON rfq.project_title = ppmp.project_title
            WHERE rfq.rfq_id = ? AND pr.status = 'Approved' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rfq_id);
    $stmt->execute();
    $stmt->bind_result($end_user, $specification, $quantity, $unit);
    $stmt->fetch();
    echo json_encode([
        'end_user' => $end_user ?: 'N/A',
        'specification' => $specification ?: 'N/A',
        'quantity' => $quantity ?: '0',
        'unit' => $unit ?: 'N/A'
    ]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = $_POST['project'];
    $end_user = $_POST['end_user'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO aoq (project_id, end_user) VALUES (?, ?)");
    $stmt->bind_param("is", $project_id, $end_user);

    if ($stmt->execute()) {
        echo "<script>alert('AOQ saved successfully.'); window.location.href='';</script>";
    } else {
        echo "<script>alert('Error saving AOQ.');</script>";
    }

    $stmt->close();
}

$current_page = 'aoq.php';

// Fetch procurement titles 
$titleQuery = "SELECT * FROM procurement_titles";
$titleResult = mysqli_query($conn, $titleQuery);
$titles = [];

if ($titleResult) {

    while ($titleRow = mysqli_fetch_assoc($titleResult)) {

        $titles[$titleRow['page']] = $titleRow;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Abstract of Quotation</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>

<body>

    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card mb-4">
                <h2><?= isset($titles[$current_page]) ? $titles[$current_page]['title'] : 'Abstract for Quotation'; ?></h2>
            </div>

            <!-- Abstract of Quotation Form -->
            <form id="aoq-form" method="POST">
                <div class="mb-4">
                    <label for="project" class="form-label">Project</label>
                    <select id="project" name="project" class="form-control" required>
                        <option value="">Select a project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo htmlspecialchars($project['rfq_id']); ?>">
                                <?php echo htmlspecialchars($project['project_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="end-user" class="form-label">End User</label>
                    <input type="text" id="end-user" name="end_user" class="form-control" placeholder="Auto-filled End User" readonly required>
                </div>


                <div class="mb-4">
                    <label for="project-location" class="form-label">Project Location</label>
                    <input type="text" id="project-location" name="project_location" class="form-control" placeholder="Enter project location" required>
                </div>
                <div class="mb-4">
                    <label for="implementing-office" class="form-label">Implementing Office</label>
                    <input type="text" id="implementing-office" name="implementing_office" class="form-control" placeholder="Enter office details" required>
                </div>
                <div class="mb-4">
                    <label for="approved-budget" class="form-label">Approved Budget for the Contract</label>
                    <input type="number" id="approved-budget" name="approved_budget" class="form-control" placeholder="Auto-filled Approved Budget for the Contract" readonly required>
                </div>


                <!-- Specifications Table -->
                <div id="specifications-section" class="mt-4">
                    <h2>Specifications</h2>
                    <table class="table" id="specifications-table">
                        <thead>
                            <tr>
                                <th>Specification</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Default empty row for manual input -->
                            <tr>
                                <td><input type="text" class="form-control" name="specification[]" required></td>
                                <td><input type="number" class="form-control" name="quantity[]" required></td>
                                <td><input type="text" class="form-control" name="unit[]" required></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Optional button to manually add more rows -->
                    <button type="button" id="add-spec-row" class="btn btn-secondary">Add Row</button>

                </div>

                <!-- Bidders Table -->
                <div id="bidders-section" class="mt-4">
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
                                <td><button type="button" class="btn btn-danger remove-bidder">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" id="add-bidder">Add Bidder</button>
                </div>

                <!-- Prepared and Verified By -->
                <div class="d-flex justify-content-between mb-4" style="margin-top: 50px;" >
                    <div>
                        <label for="prepared-by" class="form-label">Prepared By:</label>
                        <input type="text" id="prepared-by" name="prepared_by" class="form-control" placeholder="Enter prepared by" required>
                    </div>
                    <div>
                        <label for="verified-by" class="form-label">Verified By:</label>
                        <input type="text" id="verified-by" name="verified_by" class="form-control" placeholder="Enter verified by" required>
                    </div>
                </div>
                <!-- Save Button -->
                <div style="text-align: center">
                    <button type="submit" class="btn btn-primary" style="text-align: center;">Save and Proceed</button>
                </div>
                
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Handle project selection to fetch data
            $('#project').change(function() {
                var rfq_id = $(this).val();
                if (rfq_id !== "") {
                    $.ajax({
                        url: '',  // Same file
                        method: 'POST',
                        data: { rfq_id: rfq_id },
                        dataType: 'json',
                        success: function(response) {
                            // Auto-fill End User and Approved Budget
                            $('#end-user').val(response.end_user || 'N/A');
                            $('#approved-budget').val(response.approved_budget || '');

                            // Populate Specifications Table without removing the empty row
                            if (response.specifications && response.specifications.length > 0) {
                                // Clear all rows except the first empty row
                                $('#specifications-table tbody').empty();

                                // Append rows with fetched data
                                response.specifications.forEach(function(spec) {
                                    $('#specifications-table tbody').append(`
                                        <tr>
                                            <td><input type="text" class="form-control" name="specification[]" value="${spec.specification}" required></td>
                                            <td><input type="number" class="form-control" name="quantity[]" value="${spec.quantity}" required></td>
                                            <td><input type="text" class="form-control" name="unit[]" value="${spec.unit}" required></td>
                                        </tr>
                                    `);
                                });

                                // Add an extra empty row for manual input
                                $('#specifications-table tbody').append(`
                                    <tr>
                                        <td><input type="text" class="form-control" name="specification[]" required></td>
                                        <td><input type="number" class="form-control" name="quantity[]" required></td>
                                        <td><input type="text" class="form-control" name="unit[]" required></td>
                                    </tr>
                                `);
                            }
                        }
                    });
                } else {
                    // Clear all fields if no project is selected
                    $('#end-user').val('');
                    $('#approved-budget').val('');
                    $('#specifications-table tbody').html(`
                        <tr>
                            <td><input type="text" class="form-control" name="specification[]" required></td>
                            <td><input type="number" class="form-control" name="quantity[]" required></td>
                            <td><input type="text" class="form-control" name="unit[]" required></td>
                        </tr>
                    `);
                }
            });

            // Allow manual addition of new rows
            $('#add-spec-row').click(function() {
                $('#specifications-table tbody').append(`
                    <tr>
                        <td><input type="text" class="form-control" name="specification[]" required></td>
                        <td><input type="number" class="form-control" name="quantity[]" required></td>
                        <td><input type="text" class="form-control" name="unit[]" required></td>
                    </tr>
                `);
            });
        });

        $(document).ready(function() {
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
                const quantity = parseFloat(row.find('input[name="bidders_quantity[]"]').val()) || 0;
                const unitPrice = parseFloat(row.find('input[name="unit_price[]"]').val()) || 0;
                const totalPrice = quantity * unitPrice;
                row.find('input[name="total_price[]"]').val(totalPrice.toFixed(2));
            });

            $('#aoq-form').on('submit', function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Show a confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to save the details?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, save it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Gather form data
                        const formData = $('#aoq-form').serialize();

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
                                        try {
                                            const res = JSON.parse(response);
                                            window.location.href = `src/process/download_pdf_aoq.php?aoq_id=${res.aoqId}`;
                                        } catch (e) {
                                            console.error('Error parsing response:', e);
                                            Swal.fire('Error!', 'Failed to initiate download.', 'error');
                                        }
                                    }
                                });

                                $('#aoq-form')[0].reset();

                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', status, error);
                                Swal.fire('Error!', 'There was an error processing your request.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
