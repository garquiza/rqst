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
$query = $pdo->prepare("
    SELECT 
        ppmp_list.ppmp_id AS code_pap,
        ppmp_list.project_title AS procurement_project,
        app.pmo_end_user,
        app.early_procurement_activity,
        app.mode_of_procurement,
        app.advertisement_posting_ib_rei,
        app.submission_opening_bids,
        app.notice_of_award,
        app.contract_signing,
        app.source_of_funds,
        app.total,
        app.mooe,
        app.co,
        app.remarks
    FROM app
    INNER JOIN ppmp_list ON app.ppmp_id = ppmp_list.ppmp_id
");
$query->execute();
$data = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing data for the procurement monitoring report
$query = $pdo->prepare("SELECT * FROM procurement_monitoring_report WHERE id = 1");
$query->execute();
$row = $query->fetch(PDO::FETCH_ASSOC);

// Check if ID is passed to the URL for editing
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission and update the data
    $fields = ['code_pap', 'procurement_project', 'pmo_end_user', 'early_procurement_activity', 'mode_of_procurement', 'pre_proc_conference', 'advertisement_posting_ib_rei', 'pre_bid_conference', 'eligibility_check', 'submission_opening_bids', 'bid_evaluation', 'post_qualification', 'bac_recommendation', 'notice_of_award', 'contract_signing', 'delivery_completion', 'inspection_acceptance', 'source_of_funds', 'total', 'mooe', 'co', 'total_contract_cost', 'mooe_contract_cost', 'co_contract_cost', 'pre_bid_invitation', 'eligibility_check_invitation', 'sub_open_bids_invitation', 'bid_evaluation_invitation', 'post_qualification_invitation', 'delivery_completion_invitation', 'invited_observers', 'remarks'];

    // Initialize variables with POST data
    foreach ($fields as $field) {
        $$field = isset($_POST[$field]) ? $_POST[$field] : '';
    }

    // Prepare the update query
    $updateQuery = $pdo->prepare("
        UPDATE procurement_monitoring_report
        SET 
            code_pap = :code_pap,
            procurement_project = :procurement_project,
            pmo_end_user = :pmo_end_user,
            early_procurement_activity = :early_procurement_activity,
            mode_of_procurement = :mode_of_procurement,
            pre_proc_conference = :pre_proc_conference,
            advertisement_posting_ib_rei = :advertisement_posting_ib_rei,
            pre_bid_conference = :pre_bid_conference,
            eligibility_check = :eligibility_check,
            submission_opening_bids = :submission_opening_bids,
            bid_evaluation = :bid_evaluation,
            post_qualification = :post_qualification,
            bac_recommendation = :bac_recommendation,
            notice_of_award = :notice_of_award,
            contract_signing = :contract_signing,
            delivery_completion = :delivery_completion,
            inspection_acceptance = :inspection_acceptance,
            source_of_funds = :source_of_funds,
            total = :total,
            mooe = :mooe,
            co = :co,
            total_contract_cost = :total_contract_cost,
            mooe_contract_cost = :mooe_contract_cost,
            co_contract_cost = :co_contract_cost,
            pre_bid_invitation = :pre_bid_invitation,
            eligibility_check_invitation = :eligibility_check_invitation,
            sub_open_bids_invitation = :sub_open_bids_invitation,
            bid_evaluation_invitation = :bid_evaluation_invitation,
            post_qualification_invitation = :post_qualification_invitation,
            delivery_completion_invitation = :delivery_completion_invitation,
            invited_observers = :invited_observers,
            remarks = :remarks
        WHERE id = :id
    ");

    // Execute the update query and handle errors
    if ($updateQuery->execute([
        'code_pap' => $code_pap,
        'procurement_project' => $procurement_project,
        'pmo_end_user' => $pmo_end_user,
        'early_procurement_activity' => $early_procurement_activity,
        'mode_of_procurement' => $mode_of_procurement,
        'pre_proc_conference' => $pre_proc_conference,
        'advertisement_posting_ib_rei' => $advertisement_posting_ib_rei,
        'pre_bid_conference' => $pre_bid_conference,
        'eligibility_check' => $eligibility_check,
        'submission_opening_bids' => $submission_opening_bids,
        'bid_evaluation' => $bid_evaluation,
        'post_qualification' => $post_qualification,
        'bac_recommendation' => $bac_recommendation,
        'notice_of_award' => $notice_of_award,
        'contract_signing' => $contract_signing,
        'delivery_completion' => $delivery_completion,
        'inspection_acceptance' => $inspection_acceptance,
        'source_of_funds' => $source_of_funds,
        'total' => $total,
        'mooe' => $mooe,
        'co' => $co,
        'total_contract_cost' => $total_contract_cost,
        'mooe_contract_cost' => $mooe_contract_cost,
        'co_contract_cost' => $co_contract_cost,
        'pre_bid_invitation' => $pre_bid_invitation,
        'eligibility_check_invitation' => $eligibility_check_invitation,
        'sub_open_bids_invitation' => $sub_open_bids_invitation,
        'bid_evaluation_invitation' => $bid_evaluation_invitation,
        'post_qualification_invitation' => $post_qualification_invitation,
        'delivery_completion_invitation' => $delivery_completion_invitation,
        'invited_observers' => $invited_observers,
        'remarks' => $remarks,
        'id' => $id
    ])) {
        // Redirect back to pmr.php after saving
        header('Location: pmr.php?update=success');
        exit();
    } else {
        // Handle SQL errors if any
        echo "Error: " . implode(" - ", $updateQuery->errorInfo());
    }
} else {
    // If the page is not a POST request, fetch the data for editing
    if ($id) {
        $query = $pdo->prepare("SELECT * FROM procurement_monitoring_report WHERE id = :id");
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Populate the form with fetched data
            $code_pap = $result['code_pap'];
            $procurement_project = $result['procurement_project'];
            $pmo_end_user = $result['pmo_end_user'];
            $early_procurement_activity = $result['early_procurement_activity'];
            $mode_of_procurement = $result['mode_of_procurement'];
            $pre_proc_conference = $result['pre_proc_conference'];
            $advertisement_posting_ib_rei = $result['advertisement_posting_ib_rei'];
            $pre_bid_conference = $result['pre_bid_conference'];
            $eligibility_check = $result['eligibility_check'];
            $submission_opening_bids = $result['submission_opening_bids'];
            $bid_evaluation = $result['bid_evaluation'];
            $post_qualification = $result['post_qualification'];
            $bac_recommendation = $result['bac_recommendation'];
            $notice_of_award = $result['notice_of_award'];
            $contract_signing = $result['contract_signing'];
            $delivery_completion = $result['delivery_completion'];
            $inspection_acceptance = $result['inspection_acceptance'];
            $source_of_funds = $result['source_of_funds'];
            $total = $result['total'];
            $mooe = $result['mooe'];
            $co = $result['co'];
            $total_contract_cost = $result['total_contract_cost'];
            $mooe_contract_cost = $result['mooe_contract_cost'];
            $co_contract_cost = $result['co_contract_cost'];
            $pre_bid_invitation = $result['pre_bid_invitation'];
            $eligibility_check_invitation = $result['eligibility_check_invitation'];
            $sub_open_bids_invitation = $result['sub_open_bids_invitation'];
            $bid_evaluation_invitation = $result['bid_evaluation_invitation'];
            $post_qualification_invitation = $result['post_qualification_invitation'];
            $delivery_completion_invitation = $result['delivery_completion_invitation'];
            $invited_observers = $result['invited_observers'];
            $remarks = $result['remarks'];
        } else {
            // Redirect if no record found
            header('Location: pmr.php');
            exit();
        }
    }
}

// Insert data into procurement_monitoring_report table
foreach ($data as $row) { // Use $data instead of $tableRows
    $insertQuery = $pdo->prepare("
        INSERT INTO procurement_monitoring_report (
            code_pap, 
            procurement_project, 
            pmo_end_user, 
            early_procurement_activity, 
            mode_of_procurement, 
            pre_proc_conference, 
            advertisement_posting_ib_rei, 
            pre_bid_conference, 
            eligibility_check, 
            submission_opening_bids, 
            bid_evaluation, 
            post_qualification, 
            bac_recommendation, 
            notice_of_award, 
            contract_signing, 
            delivery_completion, 
            inspection_acceptance, 
            source_of_funds, 
            total, 
            mooe, 
            co, 
            total_contract_cost, 
            mooe_contract_cost, 
            co_contract_cost, 
            pre_bid_invitation, 
            eligibility_check_invitation, 
            sub_open_bids_invitation, 
            bid_evaluation_invitation, 
            post_qualification_invitation, 
            delivery_completion_invitation, 
            invited_observers, 
            remarks
        ) VALUES (
            :codePap, 
            :procurementProject, 
            :pmoEndUser, 
            :earlyProcurementActivity, 
            :modeOfProcurement, 
            :preProcConference, 
            :advertisementPostingIbRei, 
            :preBidConference, 
            :eligibilityCheck, 
            :submissionOpeningBids, 
            :bidEvaluation, 
            :postQualification, 
            :bacRecommendation, 
            :noticeOfAward, 
            :contractSigning, 
            :deliveryCompletion, 
            :inspectionAcceptance, 
            :sourceOfFunds, 
            :total, 
            :mooe, 
            :co, 
            :totalContractCost, 
            :mooeContractCost, 
            :coContractCost, 
            :preBidInvitation, 
            :eligibilityCheckInvitation, 
            :subOpenBidsInvitation, 
            :bidEvaluationInvitation, 
            :postQualificationInvitation, 
            :deliveryCompletionInvitation, 
            :invitedObservers, 
            :remarks
        )
    ");

    $insertQuery->execute([
        'codePap' => $row['code_pap'],
        'procurementProject' => $row['procurement_project'],
        'pmoEndUser' => $row['pmo_end_user'],
        'earlyProcurementActivity' => $row['early_procurement_activity'],
        'modeOfProcurement' => $row['mode_of_procurement'],
        'preProcConference' => isset($row['pre_proc_conference']) ? $row['pre_proc_conference'] : null,
        'advertisementPostingIbRei' => $row['advertisement_posting_ib_rei'],
        'preBidConference' => isset($row['pre_bid_conference']) ? $row['pre_bid_conference'] : null,
        'eligibilityCheck' => isset($row['eligibility_check']) ? $row['eligibility_check'] : null,
        'submissionOpeningBids' => $row['submission_opening_bids'],
        'bidEvaluation' => isset($row['bid_evaluation']) ? $row['bid_evaluation'] : null,
        'postQualification' => isset($row['post_qualification']) ? $row['post_qualification'] : null,
        'bacRecommendation' => isset($row['bac_recommendation']) ? $row['bac_recommendation'] : null,
        'noticeOfAward' => $row['notice_of_award'],
        'contractSigning' => $row['contract_signing'],
        'deliveryCompletion' => isset($row['delivery_completion']) ? $row['delivery_completion'] : null,
        'inspectionAcceptance' => isset($row['inspection_acceptance']) ? $row['inspection_acceptance'] : null,
        'sourceOfFunds' => $row['source_of_funds'],
        'total' => $row['total'],
        'mooe' => $row['mooe'],
        'co' => $row['co'],
        'totalContractCost' => isset($row['total_contract_cost']) ? $row['total_contract_cost'] : null,
        'mooeContractCost' => isset($row['mooe_contract_cost']) ? $row['mooe_contract_cost'] : null,
        'coContractCost' => isset($row['co_contract_cost']) ? $row['co_contract_cost'] : null,
        'preBidInvitation' => isset($row['pre_bid_invitation']) ? $row['pre_bid_invitation'] : null,
        'eligibilityCheckInvitation' => isset($row['eligibility_check_invitation']) ? $row['eligibility_check_invitation'] : null,
        'subOpenBidsInvitation' => isset($row['sub_open_bids_invitation']) ? $row['sub_open_bids_invitation'] : null,
        'bidEvaluationInvitation' => isset($row['bid_evaluation_invitation']) ? $row['bid_evaluation_invitation'] : null,
        'postQualificationInvitation' => isset($row['post_qualification_invitation']) ? $row['post_qualification_invitation'] : null,
        'deliveryCompletionInvitation' => isset($row['delivery_completion_invitation']) ? $row['delivery_completion_invitation'] : null,
        'invitedObservers' => isset($row['invited_observers']) ? $row['invited_observers'] : null,
        'remarks' => $row['remarks']
    ]);
}

?>


<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit - PMR</title>
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
            overflow-x: hidden;
        }

        .signature-section .signature-box {
            border: 1px solid #ccc;
            padding: 10px;
            width: 48%;
            text-align: center;
            overflow-x: hidden;
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
            margin-top: 20px;
            align-items: center;
            text-align: center;
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

                <h2>Technological University of the Philippines - Manila</h2>
                <br>

            <!-- Form Section: -->
            <form method="POST">
                <table class="pmr-table table table-bordered">
                    <thead>
                        <tr>
                            <!-- Table Headers -->
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
                            // Add all your table columns as before
                            $activities = ["Pre-Proc Conference", "Ads/Post of IB", "Pre-Bid Conference", "Eligibility Check", "Submission / Opening of Bids", "Bid Evaluation", "Post Qualification", "BAC Recommendation", "Notice of Award", "Contract Signing", "Delivery Completion", "Inspection & Acceptance"];
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
                            // Invitation columns
                            $invitations = ["Pre-Bid Invitation", "Eligibility Check Invitation", "Sub/Open Bids Invitation", "Bid Evaluation Invitation", "Post Qualification Invitation", "Delivery Completion Invitation"];
                            foreach ($invitations as $invitation) {
                                echo "<th>" . htmlspecialchars($invitation) . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- Populate form fields with PHP variables -->
                            <td><?= htmlspecialchars($row['code_pap']); ?></td>
                            <td><?= htmlspecialchars($row['procurement_project']); ?></td>
                            <td><?= htmlspecialchars($row['pmo_end_user']); ?></td>
                            <td><?= htmlspecialchars($row['early_procurement_activity']); ?></td>
                            <td><?= htmlspecialchars($row['mode_of_procurement']); ?></td>                                
                            <!-- Actual procurement activities -->
                            <td><input type="date" class="form-control" name="pre_proc_conference[]" value="<?= htmlspecialchars($pre_proc_conference ?? '') ?>" /></td>
                            <td><?= htmlspecialchars($row['advertisement_posting_ib_rei']); ?></td>   
                            <td><input type="date" class="form-control" name="pre_bid_conference[]" value="<?= htmlspecialchars($pre_bid_conference ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="eligibility_check[]" value="<?= htmlspecialchars($eligibility_check ?? '') ?>" /></td>
                                <td><?= htmlspecialchars($row['submission_opening_bids']); ?></td>
                            <td><input type="date" class="form-control" name="bid_evaluation[]" value="<?= htmlspecialchars($bid_evaluation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="post_qualification[]" value="<?= htmlspecialchars($post_qualification ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="bac_recommendation[]" value="<?= htmlspecialchars($bac_recommendation ?? '') ?>" /></td>
                            <td><?= htmlspecialchars($row['notice_of_award']); ?></td>
                            <td><?= htmlspecialchars($row['contract_signing']); ?></td>
                            <td><input type="date" class="form-control" name="delivery_completion[]" value="<?= htmlspecialchars($delivery_completion ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="inspection_acceptance[]" value="<?= htmlspecialchars($inspection_acceptance ?? '') ?>" /></td>
                            
                            <!-- Financial fields -->
                            <td><?= htmlspecialchars($row['source_of_funds']); ?></td>
                            <td><?= htmlspecialchars($row['total']); ?></td>
                            <td><?= htmlspecialchars($row['mooe']); ?></td>
                            <td><?= htmlspecialchars($row['co']); ?></td>
                            <td><input type="number" class="form-control" name="total_contract_cost[]" value="<?= htmlspecialchars($total_contract_cost ?? '') ?>" /></td>
                            <td><input type="number" class="form-control" name="mooe_contract_cost[]" value="<?= htmlspecialchars($mooe_contract_cost ?? '') ?>" /></td>
                            <td><input type="number" class="form-control" name="co_contract_cost[]" value="<?= htmlspecialchars($co_contract_cost ?? '') ?>" /></td>
                            <td><input type="text" class="form-control" name="invited_observers[]" value="<?= htmlspecialchars($invited_observers ?? '') ?>" /></td>
                            <!-- Invitations -->
                            <td><input type="date" class="form-control" name="pre_bid_invitation[]" value="<?= htmlspecialchars($pre_bid_invitation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="eligibility_check_invitation[]" value="<?= htmlspecialchars($eligibility_check_invitation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="sub_open_bids_invitation[]" value="<?= htmlspecialchars($sub_open_bids_invitation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="bid_evaluation_invitation[]" value="<?= htmlspecialchars($bid_evaluation_invitation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="post_qualification_invitation[]" value="<?= htmlspecialchars($post_qualification_invitation ?? '') ?>" /></td>
                            <td><input type="date" class="form-control" name="delivery_completion_invitation[]" value="<?= htmlspecialchars($delivery_completion_invitation ?? '') ?>" /></td>
                            <!-- Remarks -->
                            <td><?= htmlspecialchars($row['remarks']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="signature-section">
                    <div class="signature-box">
                        <p>Prepared By:</p>
                        <td><input type="number" class="form-control" /></td>
                        <br>
                        <!-- Button to trigger file input -->
                        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('signatureInput').click();">
                            Add Signature
                        </button>

                        <!-- Hidden file input for image upload -->
                        <input type="file" id="signatureInput" style="display: none;" accept="image/*" onchange="previewImage(event)">

                        <!-- Preview the uploaded image -->
                        <img id="signaturePreview" style="display: none; width: 150px; height: auto;" alt="Signature Preview">
                    </div>
                    <div class="signature-box">
                        <p>Recommended for Approval By:</p>
                        <td><input type="number" class="form-control" /></td>
                        <br>
                        <!-- Button to trigger file input -->
                        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('signatureInput').click();">
                            Add Signature
                        </button>

                        <!-- Hidden file input for image upload -->
                        <input type="file" id="signatureInput" style="display: none;" accept="image/*" onchange="previewImage(event)">

                        <!-- Preview the uploaded image -->
                        <img id="signaturePreview" style="display: none; width: 150px; height: auto;" alt="Signature Preview">

                    </div>

                </div>

                <button type="submit" class="btn btn-primary">Save</button>
            </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            var output = document.getElementById('signaturePreview');
            var previewContainer = document.getElementById('signaturePreviewContainer');
            var file = event.target.files[0];
            
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    output.src = e.target.result;
                    previewContainer.style.display = 'block';  // Show the preview container with image
                }
                
                reader.readAsDataURL(file);
            }
        }

    </script>
</body>
</html>
