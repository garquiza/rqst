<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include('src/config/database.php');

// Check if the PR number is provided
if (!isset($_GET['pr_number'])) {
    header("Location: pr.php"); // Redirect if no PR number is provided
    exit();
}

$pr_number = $_GET['pr_number'];

// Fetch the existing purchase request details
$query = "SELECT pr.*, e.first_name, e.last_name FROM purchase_requests pr JOIN end_users e ON pr.end_user_id = e.id WHERE pr.pr_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pr_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: pr.php"); // Redirect if PR not found
    exit();
}

$purchase_request = $result->fetch_assoc();

// Fetch items associated with the purchase request
$query_items = "SELECT pri.*, i.item_name, i.unit, i.item_no FROM purchase_request_items pri JOIN inventory i ON pri.inventory_id = i.inventory_id WHERE pri.pr_id = ?";
$stmt_items = $conn->prepare($query_items);
$stmt_items->bind_param("i", $purchase_request['pr_id']);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

$total_amount = 0;

// Initialize variables for department, section, and purpose
$department = '';
$section = '';
$purpose = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $approver = $_POST['approver'];
    $status = $_POST['status'];
    $pr_process_status = $_POST['pr_process_status'];

    // Update the purchase request
    $update_query = "UPDATE purchase_requests SET approver = ?, status = ?, pr_process_status = ? WHERE pr_number = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssss", $approver, $status, $pr_process_status, $pr_number);

    if ($update_stmt->execute()) {
        // Redirect to the PR list with a success message
        header("Location: pr.php?message=Purchase request updated successfully.");
        exit();
    } else {
        $error_message = "Error updating purchase request: " . $conn->error;
    }
}

// Fetch the first item's department, section, and purpose for display
if ($result_items->num_rows > 0) {
    $first_item = $result_items->fetch_assoc();
    $department = $first_item['department'];
    $section = $first_item['section'];
    $purpose = $first_item['purpose']; // Fetch purpose from the first item
    // Reset the result set pointer to the beginning
    $result_items->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Purchase Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/dashboard.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content animate__animated animate__fadeIn">
        <div class="header-card mb-4">
            <h1 class="display-5 mb-2">Update Purchase Request</h1>
            <p class="text-light">Update the details of the purchase request.</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="post" id="update-pr-form">
            <div class="row mb-3">
                <div class="col">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" required>
                </div>
                <div class="col">
                    <label for="pr_number" class="form-label">PR Number</label>
                    <input type="text" class="form-control" id="pr_number" name="pr_number" value="<?php echo htmlspecialchars($purchase_request['pr_number']); ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label for="section" class="form-label">Section</label>
                    <input type="text" class="form-control" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" required>
                </div>
                <div class="col">
                    <label for="sai_number" class="form-label">SAI Number</label>
                    <input type="text" class="form-control" id="sai_number" name="sai_number" value="" placeholder="Leave Empty">
                </div>
            </div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ITEM NO.</th>
            <th>UNIT</th>
            <th>ITEM DESCRIPTION</th>
            <th>QUANTITY</th>
            <th>UNIT COST</th>
            <th>TOTAL COST</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $result_items->fetch_assoc()): 
            $total_amount += $item['total_cost']; 
        ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_no']); ?></td>
                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>
                    <input type="number" class="form-control" name="unit_cost[<?php echo $item['inventory_id']; ?>]" value="<?php echo number_format($item['unit_cost'], 2); ?>" step="0.01" style="width: 100%;" required>
                </td>
                <td><?php echo number_format($item['total_cost'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <input type="text" class="form-control" id="purpose" name="purpose" value="<?php echo htmlspecialchars($purpose); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label for="requested_by" class="form-label">Requested By</label>
                    <input type="text" class="form-control" id="requested_by" name="requested_by" value="<?php echo htmlspecialchars($purchase_request['first_name'] . ' ' . $purchase_request['last_name']); ?>" readonly>
                </div>
                <div class="col">
                    <label for="approver" class="form-label">Approver</label>
                    <input type="text" class="form-control" id="approver" name="approver" value="<?php echo htmlspecialchars($purchase_request['approver']); ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label for="pr_process_status" class="form-label">PR Process Status</label>
                <input type="text" class="form-control" id="pr_process_status" name="pr_process_status" value="<?php echo htmlspecialchars($purchase_request['pr_process_status']); ?>" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Update Purchase Request</button>
            <a href="pr.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('update-pr-form').addEventListener('submit', function(e) {
            e.preventDefault(); 

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update this purchase request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(this);

                    fetch('src/process/save_edit_pr.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    title: 'Updated!',
                                    text: data.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = 'pr.php'; 
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong. Please try again later.',
                                icon: 'error'
                            });
                        });
                }
            });
        });
    </script>
</body>

</html>