<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../admin/src/config/pdo.php';

// Fetch procurement data
$query = $pdo->prepare("SELECT * FROM procurement_monitoring_report");
$query->execute();
$pmrData = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PMR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            overflow-x: hidden;
        }

        .header-card {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .header-card h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .table-container {
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: white;
            padding: 20px;
            overflow-x: auto;
        }

        .pmr-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pmr-table th,
        .pmr-table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .pmr-table thead th {
            background-color: rgb(248, 249, 250);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .signature-section .signature-box {
            border: 1px solid #ccc;
            padding: 10px;
            width: 48%;
            text-align: center;
        }

        .action-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .action-buttons button {
            margin: 5px;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .download-button, .edit-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none; /* Remove underline */
            font-size: 14px;
            display: inline-block;
            cursor: pointer;
            float: right;
        }

        .edit-button {
            background-color: #ffc107; /* Yellow color for Edit button */
            color: #212529; /* Dark text for contrast */
            margin-right: 10px; /* Add spacing between Edit and Download buttons */
        }

        .download-button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .edit-button:hover {
            background-color: #e0a800; /* Darker yellow on hover */
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content">
            <div class="header-card">
                <h1>Procurement Monitoring Report as of <?= date('F j, Y'); ?></h1>
            </div>
            <div class="table-container">
                <a href="src/process/download_excel_pmr.php" class="btn btn-success download-button" id="download-pmr-btn">
                    <i class="fas fa-download"></i> Download
                </a>

                <!-- Edit Button for the entire table -->
                <button class="btn btn-warning edit-button" id="edit-button">
                    <i class="fas fa-edit"></i> Edit
                </button>
                


                <h2>Technological University of the Philippines - Manila</h2>
                <br>
                <table class="pmr-table table table-bordered">
                    
                    <thead>
                        <tr>
                            <th rowspan="2">CODE (APP)</th>
                            <th rowspan="2">PROCUREMENT PROJECT</th>
                            <th rowspan="2">PROJECT MANAGEMENT OFFICE (PMO) / END-USER</th>
                            <th rowspan="2">EARLY PROCUREMENT ACTIVITY?</th>
                            <th rowspan="2">MODE OF PROCUREMENT</th>
                            <th colspan="12">ACTUAL PROCUREMENT ACTIVITIES</th>
                            <th rowspan="2">SOURCE OF FUNDS</th>
                            <th colspan="3">ABC (PhP)</th>
                            <th colspan="3">CONTRACT COST (PhP)</th>
                            <th rowspan="2">LIST OF INVITED OBSERVERS</th>
                            <th colspan="6">DATE OF RECEIPT OF INVITATIONS</th>
                            <th rowspan="2">REMARKS</th>
                        </tr>
                        <tr>
                            <?php
                            // Array of activities for actual procurement activities
                            $activities = [
                                "Pre-Proc Conference",
                                "Ads/Post of IB",
                                "Pre-Bid Conference",
                                "Eligibility Check",
                                "Submission / Opening of Bids",
                                "Bid Evaluation",
                                "Post Qualification",
                                "BAC Recommendation",
                                "Notice of Award",
                                "Contract Signing",
                                "Delivery Completion",
                                "Inspection & Acceptance"
                            ];
                            foreach ($activities as $activity) {
                                echo "<th>$activity</th>";
                            }
                            ?>
                            <th>Total</th>
                            <th>MOOE</th>
                            <th>CO</th>
                            <th>Total</th>
                            <th>MOOE</th>
                            <th>CO</th>
                            <?php
                            // Array of invitation-related headers
                            $invitations = [
                                "Pre-Bid Invitation",
                                "Eligibility Check Invitation",
                                "Sub/Open Bids Invitation",
                                "Bid Evaluation Invitation",
                                "Post Qualification Invitation",
                                "Delivery Completion Invitation"
                            ];
                            foreach ($invitations as $invitation) {
                                echo "<th>" . htmlspecialchars($invitation) . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pmrData as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['code_pap']); ?></td>
                                <td><?= htmlspecialchars($row['procurement_project']); ?></td>
                                <td><?= htmlspecialchars($row['pmo_end_user']); ?></td>
                                <td><?= htmlspecialchars($row['early_procurement_activity']); ?></td>
                                <td><?= htmlspecialchars($row['mode_of_procurement']); ?></td>
                                <td><?= htmlspecialchars($row['pre_proc_conference']); ?></td>
                                <td><?= htmlspecialchars($row['advertisement_posting_ib_rei']); ?></td>
                                <td><?= htmlspecialchars($row['pre_bid_conference']); ?></td>
                                <td><?= htmlspecialchars($row['eligibility_check']); ?></td>
                                <td><?= htmlspecialchars($row['submission_opening_bids']); ?></td>
                                <td><?= htmlspecialchars($row['bid_evaluation']); ?></td>
                                <td><?= htmlspecialchars($row['post_qualification']); ?></td>
                                <td><?= htmlspecialchars($row['bac_recommendation']); ?></td>
                                <td><?= htmlspecialchars($row['notice_of_award']); ?></td>
                                <td><?= htmlspecialchars($row['contract_signing']); ?></td>
                                <td><?= htmlspecialchars($row['delivery_completion']); ?></td>
                                <td><?= htmlspecialchars($row['inspection_acceptance']); ?></td>
                                <td><?= htmlspecialchars($row['source_of_funds']); ?></td>
                                <td><?= htmlspecialchars($row['total']); ?></td>
                                <td><?= htmlspecialchars($row['mooe']); ?></td>
                                <td><?= htmlspecialchars($row['co']); ?></td>
                                <td><?= htmlspecialchars($row['total_contract_cost']); ?></td>
                                <td><?= htmlspecialchars($row['mooe_contract_cost']); ?></td>
                                <td><?= htmlspecialchars($row['co_contract_cost']); ?></td>
                                <td><?= htmlspecialchars($row['pre_bid_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['eligibility_check_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['sub_open_bids_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['bid_evaluation_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['post_qualification_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['delivery_completion_invitation']); ?></td>
                                <td><?= htmlspecialchars($row['invited_observers']); ?></td>
                                <td><?= htmlspecialchars($row['remarks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <p>Prepared By:</p>
                    <br>
                </div>
                <div class="signature-box">
                    <p>Recommended for Approval By:</p>
                    <br>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-primary">SEE COMPLETED PROCUREMENT ACTIVITIES</button>
                <button class="btn btn-primary">SEE ONGOING PROCUREMENT ACTIVITIES</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editButton = document.getElementById('edit-button');
            if (editButton) {
                editButton.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent default behavior
                    console.log('Edit button clicked'); // Debug log
                    Swal.fire({
                        title: 'Confirm Action',
                        text: 'Are you sure you want to edit?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Edit!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'edit_pmr.php';
                        }
                    });
                });
            }
        });


    </script>



</body>




</html>
