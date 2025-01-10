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

                <!-- Code and General Description -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="code">Code:</label>
                        <input type="text" id="code" name="code" value="<?php echo $randomCode; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="general_description">General Description:</label>
                        <input type="text" id="general_description" name="general_description" required>
                    </div>
                </div>

                <!-- Quantity / Size and Estimated Budget -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="quantity_size">Quantity / Size:</label>
                        <input type="text" id="quantity_size" name="quantity_size" required>
                    </div>
                    <div class="col-md-6">
                        <label for="estimated_budget">Estimated Budget:</label>
                        <input type="number" id="estimated_budget" name="estimated_budget" required>
                    </div>
                </div>

                <!-- Schedule / Milestone of Activities -->
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
            $('#create-ppmp-form').on('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                var formData = $(this).serialize(); // Get all form data

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
        });
    </script>

</body>

</html>