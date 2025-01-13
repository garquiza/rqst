<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../bac/src/config/pdo.php';

// Fetch project titles from the rfq table
$query = "SELECT project_title FROM rfq";
$stmt = $pdo->prepare($query);
$stmt->execute();
$project_titles = $stmt->fetchAll(PDO::FETCH_COLUMN);

$current_page = 'noa.php';

// Fetch procurement titles
$titleQuery = $pdo->prepare("SELECT * FROM procurement_titles");
$titleQuery->execute();
$titles = [];

while ($titleRow = $titleQuery->fetch(PDO::FETCH_ASSOC)) {
    $titles[$titleRow['page']] = $titleRow;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAC - Notice of Award</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .header-card {
            margin-bottom: 20px;
        }

        .form-card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
                <h2><?= isset($titles[$current_page]) ? $titles[$current_page]['title'] : 'Notice of Award'; ?></h2>
            </div>

            <div class="card form-card">
                <div class="card-body">
                    <form id="noaForm" method="POST">
                        <div class="mb-3">
                            <label for="authorized_representative" class="form-label">Name of the Authorized Representative:</label>
                            <input type="text" class="form-control" id="authorized_representative" name="authorized_representative" required>
                        </div>
                        <div class="mb-3">
                            <label for="designation" class="form-label">Designation:</label>
                            <input type="text" class="form-control" id="designation" name="designation" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name:</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="project_title" class="form-label">Project Title:</label>
                            <select class="form-select" id="project_title" name="project_title" required>
                                <option value="" disabled selected>Select a project title</option>
                                <?php foreach ($project_titles as $title): ?>
                                    <option value="<?php echo htmlspecialchars($title); ?>"><?php echo htmlspecialchars($title); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="contract_amount_words" class="form-label">Contract Amount in Words:</label>
                            <input type="text" class="form-control" id="contract_amount_words" name="contract_amount_words" required>
                        </div>
                        <div class="mb-3">
                            <label for="contract_amount_figures" class="form-label">Contract Amount in Figures:</label>
                            <input type="text" class="form-control" id="contract_amount_figures" name="contract_amount_figures" required>
                        </div>
                        <div class="mb-3">
                            <label for="philgeps_reference" class="form-label">PhilGEPS Reference Number:</label>
                            <input type="text" class="form-control" id="philgeps_reference" name="philgeps_reference" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#noaForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                // Show SweetAlert2 confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to submit this Notice of Award?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, submit it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, submit the form using AJAX
                        const formData = $(this).serialize(); // Serialize form data
                        $.ajax({
                            type: 'POST',
                            url: 'src/process/add_noa.php', // Adjust this URL to your processing script
                            data: formData,
                            success: function(response) {
                                // Handle success response
                                Swal.fire({
                                    title: 'Submitted!',
                                    text: 'Your Notice of Award has been submitted successfully.',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'Proceed to Notice to Proceed',
                                    cancelButtonText: 'Download',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Redirect to noa.php
                                        window.location.href = `ntp.php?noa_id=${JSON.parse(response).noa_id}`;
                                    } else if (result.isDismissed) {
                                        // Trigger download (you can adjust the URL to your download file)
                                        window.location.href = `src/process/download_pdf_noa.php?noa_id=${JSON.parse(response).noa_id}`;
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                // Handle error response
                                Swal.fire(
                                    'Error!',
                                    'There was an error submitting your Notice of Award. Please try again.',
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