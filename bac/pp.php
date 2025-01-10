<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

include '../bac/src/config/database.php';

// Fetch user details from the session
$userFirstName = $_SESSION['first_name'] ?? 'User  ';
$userLastName = $_SESSION['last_name'] ?? '';
$email = $_SESSION['email'] ?? 'Not Available';
$permissions = explode(',', $_SESSION['permission_access'] ?? ''); // Convert permissions to an array

// Define the available processes
$processes = [
    'APP' => 'app.php',
    'PPMP' => 'ppmp_list.php',
    'PR' => 'pr.php',
    'PMAF' => 'pmf.php',
    'RFQ' => 'rfq.php',
    'AOQ' => 'aoq.php',
    'RESO' => 'reso.php',
    'NOA' => 'noa.php',
    'NTP' => 'ntp.php',
    'PO' => 'po.php',
    'PMR' => 'pmr.php',
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Process</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .header {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #ffffff;
        }

        .process-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px 0;
        }

        .process-btn {
            width: 100%;
            height: 70px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #ffffff;
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .process-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .process-btn.active {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }

        .process-btn.disabled {
            background: #e9ecef;
            color: #adb5bd;
            pointer-events: none;
        }

        .card-info .card {
            transition: transform 0.3s ease-in-out;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 15px;
        }

        .card-info .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="container">
                <div class="card p-5">
                    <h2 class="text-center header mb-4">Procurement Process</h2>

                    <div class="process-container">
                        <?php foreach ($processes as $process => $link): ?>
                            <a href="<?= in_array($process, $permissions) ? $link : '#' ?>" class="btn btn-link"
                                onclick="<?= !in_array($process, $permissions) ? "event.preventDefault(); showAccessDeniedAlert();" : '' ?>">
                                <button class="process-btn <?= in_array($process, $permissions) ? '' : 'disabled' ?>"
                                    <?= !in_array($process, $permissions) ? 'disabled' : '' ?>><?= $process ?></button>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Main Content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showAccessDeniedAlert() {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'You do not have permission to access this section.',
                confirmButtonText: 'OK'
            });
        }
    </script>
</body>

</html>