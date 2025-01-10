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

// Simulating logged-in user info
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Fetch PPMP data based on ppmp_id
if (isset($_GET['ppmp_id'])) {
    $ppmp_id = intval($_GET['ppmp_id']);
    $query = "SELECT * FROM ppmp_list WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("PPMP not found.");
    }

    $ppmp = $result->fetch_assoc();
} else {
    die("Invalid request.");
}

// Fetch associated PPMP forms
$formQuery = "SELECT * FROM ppmp_form WHERE ppmp_id = ?";
$formStmt = $conn->prepare($formQuery);
$formStmt->bind_param("i", $ppmp_id);
$formStmt->execute();
$formResult = $formStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAC - Edit PPMP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .signature-image {
            max-width: 200px;
            /* Adjust as needed */
            max-height: 100px;
            /* Adjust as needed */
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card">
                <h1 class="mb-4">Edit PPMP</h1>
                <p class="mb-0">Modify the details of the selected PPMP.</p>
            </div>

            <div class="container mt-5">
                <form action="src/process/update_ppmp.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="ppmp_id" value="<?php echo $ppmp['ppmp_id']; ?>">
                    <input type="hidden" name="approver_name" value="<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>">
                    <div class="mb-3">
                        <label for="project_title" class="form-label">Project Title</label>
                        <input type="text" class="form-control" id="project_title" name="project_title" value="<?php echo htmlspecialchars($ppmp['project_title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="approver" class="form-label">Approver</label>
                        <input type="text" class="form-control" id="approver" name="approver" value="<?php echo htmlspecialchars($ppmp['approver']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="approved" <?php echo ($ppmp['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="pending" <?php echo ($ppmp['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="rejected" <?php echo ($ppmp['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="signature" class="form-label">Upload Signature</label>
                        <input type="file" class="form-control" id="signature" name="signature" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="current_signature" class="form-label">Current Signature</label>
                        <?php if (!empty($ppmp['signature'])): ?>
                            <img src="data:image/png;base64,<?php echo base64_encode($ppmp['signature']); ?>" class="signature-image" alt="Signature">
                        <?php else: ?>
                            <p>No signature uploaded.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update PPMP</button>
                    <a href="ppmp_list.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>

            <div class="container mt-5">
                <h2>Associated PPMP Forms</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Code</th>
                            <th>General Description</th>
                            <th>Quantity/Size</th>
                            <th>Estimated Budget</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($formResult->num_rows > 0): ?>
                            <?php while ($form = $formResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($form['year']); ?></td>
                                    <td><?php echo htmlspecialchars($form['code']); ?></td>
                                    <td><?php echo htmlspecialchars($form['general_description']); ?></td>
                                    <td><?php echo htmlspecialchars($form['quantity_size']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($form['estimated_budget'], 2)); ?></td>
                                    <td><?php echo date("F j, Y", strtotime($form['date_created'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No associated forms found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>