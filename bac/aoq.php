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
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Get logged-in user's details
$user_id = $_SESSION['user_id'];
$user_name = "";
$sql_user = "SELECT first_name, last_name FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
}

// Close the statement
$stmt->close();
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
                <h1>Abstract of Quotation</h1>
                <p>Fill out the project details below</p>
            </div>

            <!-- Abstract of Quotation Form -->
            <form id="aoq-form" method="POST">
                <div class="mb-4">
                    <label for="project" class="form-label">Project</label>
                    <select id="project" name="project" class="form-control" required>
                        <option value="">Select a project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['rfq_id']; ?>">
                                <?php echo htmlspecialchars($project['project_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <input type="number" id="approved-budget" name="approved_budget" class="form-control" placeholder="Enter budget amount" required>
                </div>

                <!-- Prepared and Verified By -->
                <div class="d-flex justify-content-between mb-4">
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
                <button type="submit" class="btn btn-primary">Save and Proceed</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
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
                        // If confirmed, submit the form via AJAX
                        $.ajax({
                            url: 'src/process/add_aoq.php', // URL to the PHP script that processes the form
                            type: 'POST',
                            data: $(this).serialize(), // Serialize the form data
                            success: function(response) {
                                console.log(response); // Debugging: log the response
                                try {
                                    // Assuming the response is a JSON object
                                    const res = JSON.parse(response);
                                    if (res.success) {
                                        // Show success message and redirect
                                        Swal.fire(
                                            'Saved!',
                                            'Your details have been saved.',
                                            'success'
                                        ).then(() => {
                                            window.location.href = 'aoq_next.php'; // Redirect to the next page
                                        });
                                    } else {
                                        // Show error message
                                        Swal.fire(
                                            'Error!',
                                            res.message,
                                            'error'
                                        );
                                    }
                                } catch (e) {
                                    // Handle JSON parsing error
                                    console.error('Parsing error:', e); // Debugging: log the error
                                    Swal.fire(
                                        'Error!',
                                        'There was an error processing the response.',
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle AJAX error
                                console.error('AJAX error:', status, error); // Debugging: log the error
                                Swal.fire(
                                    'Error!',
                                    'There was an error processing your request.',
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