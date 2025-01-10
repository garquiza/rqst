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

// Retrieve PR number and status from query parameters
$pr_number = isset($_GET['pr_number']) ? htmlspecialchars($_GET['pr_number']) : 'N/A';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : 'N/A';

// Fetch Purchase Request details along with PPMP project details
$query = "
    SELECT 
        purchase_requests.pr_number, 
        purchase_requests.status, 
        purchase_requests.submitted_date,
        ppmp_list.project_title, 
        ppmp_form.code, 
        ppmp_form.estimated_budget 
    FROM purchase_requests
    JOIN ppmp_list ON purchase_requests.ppmp_id = ppmp_list.ppmp_id
    JOIN ppmp_form ON ppmp_list.ppmp_id = ppmp_form.ppmp_id
    WHERE purchase_requests.pr_number = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$pr_number]);
$prDetails = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                    <div class="card-info">
                        <div class="row">
                            <!-- Purchase Request Details -->
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="header-card">Purchase Request Details</h5>
                                        <p><strong>PR Number:</strong> <?php echo $prDetails['pr_number']; ?></p>
                                        <p>
                                            <strong>Status:</strong>
                                            <?php
                                            // Highlight the status with Bootstrap badges
                                            $statusClass = match ($status) {
                                                'Pending' => 'bg-warning',
                                                'Approved' => 'bg-success',
                                                'Rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            echo "<span class='badge $statusClass'>$status</span>";
                                            ?>
                                        </p>
                                        <p><strong>Submitted Date:</strong> <?php echo date('F j, Y, g:i A', strtotime($prDetails['submitted_date'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- PPMP Information -->
                            <div class="col-md-6 mb-3">
                                <?php if ($prDetails): ?>
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="header-card">PPMP Project Information</h5>
                                            <p><strong>Project Title:</strong> <?php echo htmlspecialchars($prDetails['project_title']); ?></p>
                                            <p><strong>Code: </strong><span class="badge text-bg-primary"><?php echo htmlspecialchars($prDetails['code']); ?></span></p>
                                            <p><strong>Estimated Budget:</strong> ₱ <?php echo number_format($prDetails['estimated_budget'], 2); ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mt-2">
                                        No PPMP project details found for this PR number.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="process-container">
                        <a href="app.php" class="btn btn-link">
                            <button class="process-btn active" onclick="checkPermission('APP', 'app.php')">APP</button>
                        </a>
                        <a href="ppmp_list.php" class="btn btn-link">
                            <button class="process-btn" onclick="checkPermission('PPMP', 'ppmp_list.php')">PPMP</button>
                        </a>
                        <a href="pr.php" class="btn btn-link">
                            <button class="process-btn" onclick="checkPermission('PR', 'pr.php')">PR</button>
                        </a>
                        <a href="pmf.php" class="btn btn-link">
                            <button class="process-btn" onclick="checkPermission('PMAF', 'pmf.php')">PMAF</button>
                        </a>
                        <a href="rfq.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">RFQ</button>
                        </a>
                        <a href="aoq.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">AOQ</button>
                        </a>
                        <a href="reso.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">RESO</button>
                        </a>
                        <a href="noa.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">NOA</button>
                        </a>
                        <a href="ntp.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">NTP</button>
                        </a>
                        <a href="po.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">PO</button>
                        </a>
                        <a href="pmr.php" class="btn btn-link">
                            <button class="process-btn disabled" disabled onclick="showError()">PMR</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Main Content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userPermissions = <?php echo json_encode($permissions); ?>; // Pass PHP permissions to JavaScript

        function checkPermission(permission, url) {
            if (userPermissions.includes(permission)) {
                window.location.href = url; // Redirect to the page if permission exists
            } else {
                showError(); // Show error if permission does not exist
            }
        }

        function showError() {
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: "You don't have permission to access this page.",
                confirmButtonText: 'OK'
            });
        }
    </script>
</body>

</html>