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
            <div class="mb-3">
                <label for="general_description" class="form-label">General Description</label>
                <textarea class="form-control" id="general_description" name="general_description" rows="3" required><?php echo htmlspecialchars($formData['general_description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="quantity_size" class="form-label">Quantity / Size</label>
                <input type="text" class="form-control" id="quantity_size" name="quantity_size" value="<?php echo htmlspecialchars($formData['quantity_size']); ?>" required>
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
    </script>
</body>

</html>