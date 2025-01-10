<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../budget/src/config/database.php';

if (isset($_GET['ppmp_id'])) {
    $ppmp_id = $_GET['ppmp_id'];

    $query = "SELECT * FROM ppmp_list WHERE ppmp_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ppmp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ppmp = $result->fetch_assoc();
    } else {
        die("Error: No data found for the provided ppmp_id.");
    }
} else {
    die("Error: ppmp_id not provided in URL.");
}

$query = "SELECT ppmp_list.ppmp_id, ppmp_list.project_title, app.pmo_end_user, app.early_procurement_activity, app.mode_of_procurement, 
          app.advertisement_posting_ib_rei, app.submission_opening_bids, app.notice_of_award, app.contract_signing, 
          app.source_of_funds, app.total, app.mooe, app.co, app.remarks
          FROM ppmp_list
          LEFT JOIN app ON ppmp_list.ppmp_id = app.ppmp_id
          WHERE ppmp_list.ppmp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ppmp_id);
$stmt->execute();
$formResult = $stmt->get_result();


if ($formResult->num_rows > 0) {
    $ppmp = $formResult->fetch_assoc();
} else {
    die("Error: No associated data found for the provided ppmp_id.");
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget - Edit APP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            overflow-x: auto;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #343a40;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <div class="sidebar">
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card">
                <h1 class="mb-4">Edit APP</h1>
                <p class="mb-0">Modify the details of the selected APP.</p>
            </div>

            <div class="container mt-5">
                <form action="src/process/update_app.php" method="POST">
                    <input type="hidden" name="ppmp_id" value="<?php echo $ppmp['ppmp_id']; ?>">

                    <div class="mb-3">
                        <label for="project_title" class="form-label">Project Title</label>
                        <input type="text" class="form-control" id="project_title" name="project_title" value="<?php echo htmlspecialchars($ppmp['project_title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="pmo" class="form-label">PMO/End-User</label>
                        <input type="text" class="form-control" id="pmo" name="pmo" value="<?php echo htmlspecialchars($ppmp['pmo_end_user']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Is this an Early Procurement Activity?</label>
                        <select class="form-select" id="status" name="status">
                            <option value="YES" <?php echo (isset($ppmp['early_procurement_activity']) && $ppmp['early_procurement_activity'] == 'YES') ? 'selected' : ''; ?>>Yes</option>
                            <option value="NO" <?php echo (isset($ppmp['early_procurement_activity']) && $ppmp['early_procurement_activity'] == 'NO') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modeOfProcurement" class="form-label">Mode of Procurement</label>
                        <select class="form-select" id="modeOfProcurement" name="modeOfProcurement">
                            <option value="Competitive Bidding" <?php echo ($ppmp['mode_of_procurement'] == 'Competitive Bidding') ? 'selected' : ''; ?>>Competitive Bidding</option>
                            <option value="Limited Source Bidding" <?php echo ($ppmp['mode_of_procurement'] == 'Limited Source Bidding') ? 'selected' : ''; ?>>Limited Source Bidding</option>
                            <option value="Direct Contracting" <?php echo ($ppmp['mode_of_procurement'] == 'Direct Contracting') ? 'selected' : ''; ?>>Direct Contracting</option>
                            <option value="Repeat Order" <?php echo ($ppmp['mode_of_procurement'] == 'Repeat Order') ? 'selected' : ''; ?>>Repeat Order</option>
                            <option value="Shopping" <?php echo ($ppmp['mode_of_procurement'] == 'Shopping') ? 'selected' : ''; ?>>Shopping</option>
                            <option value="NP-53.1 Two Failed Biddings" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.1 Two Failed Biddings') ? 'selected' : ''; ?>>NP-53.1 Two Failed Biddings</option>
                            <option value="NP-53.2 Emergency Cases" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.2 Emergency Cases') ? 'selected' : ''; ?>>NP-53.2 Emergency Cases</option>
                            <option value="Emergency Procurement under the Bayanihan Act" <?php echo ($ppmp['mode_of_procurement'] == 'Emergency Procurement under the Bayanihan Act') ? 'selected' : ''; ?>>Emergency Procurement under the Bayanihan Act</option>
                            <option value="NP-53.3 Take-Over of Contracts" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.3 Take-Over of Contracts') ? 'selected' : ''; ?>>NP-53.3 Take-Over of Contracts</option>
                            <option value="NP-53.4 Adjacent or Contiguous" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.4 Adjacent or Contiguous') ? 'selected' : ''; ?>>NP-53.4 Adjacent or Contiguous</option>
                            <option value="NP-53.5 Agency-to-Agency" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.5 Agency-to-Agency') ? 'selected' : ''; ?>>NP-53.5 Agency-to-Agency</option>
                            <option value="NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services') ? 'selected' : ''; ?>>NP-53.6 Scientific, Scholarly, Artistic Work, Exclusive Technology and Media Services</option>
                            <option value="NP-53.7 Highly Technical Consultants" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.7 Highly Technical Consultants') ? 'selected' : ''; ?>>NP-53.7 Highly Technical Consultants</option>
                            <option value="NP-53.8 Defense Cooperation Agreement" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.8 Defense Cooperation Agreement') ? 'selected' : ''; ?>>NP-53.8 Defense Cooperation Agreement</option>
                            <option value="NP-53.9 - Small Value Procurement" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.9 - Small Value Procurement') ? 'selected' : ''; ?>>NP-53.9 - Small Value Procurement</option>
                            <option value="NP-53.10 Lease of Real Property and Venue" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.10 Lease of Real Property and Venue') ? 'selected' : ''; ?>>NP-53.10 Lease of Real Property and Venue</option>
                            <option value="NP-53.11 NGO Participation" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.11 NGO Participation') ? 'selected' : ''; ?>>NP-53.11 NGO Participation</option>
                            <option value="NP-53.12 Community Participation" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.12 Community Participation') ? 'selected' : ''; ?>>NP-53.12 Community Participation</option>
                            <option value="NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions" <?php echo ($ppmp['mode_of_procurement'] == "NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions") ? 'selected' : ''; ?>>NP-53.13 UN Agencies, Int'l Organizations or Intentional Financing Institutions</option>
                            <option value="NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets" <?php echo ($ppmp['mode_of_procurement'] == 'NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets') ? 'selected' : ''; ?>>NP-53.14 Direct Retail Purchase of Petroleum Fuel, Oil and Lubricant (POL) Products and Airline Tickets</option>
                            <option value="Others - Foreign-funded procurements" <?php echo ($ppmp['mode_of_procurement'] == 'Others - Foreign-funded procurements') ? 'selected' : ''; ?>>Others - Foreign-funded procurements</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Advertisement/PostingofIB/REI" class="form-label">Advertisement/Posting of IB/REI</label>
                        <input type="date" class="form-control" id="Advertisement/PostingofIB/REI" name="Advertisement/PostingofIB/REI" value="<?php echo isset($ppmp['advertisement_posting_ib_rei']) ? htmlspecialchars($ppmp['advertisement_posting_ib_rei']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="Submission/OpeningofBids" class="form-label">Submission/Opening of Bids</label>
                        <input type="date" class="form-control" id="Submission/OpeningofBids" name="Submission/OpeningofBids" value="<?php echo isset($ppmp['submission_opening_bids']) ? htmlspecialchars($ppmp['submission_opening_bids']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="NoticeofAward" class="form-label">Notice of Award</label>
                        <input type="date" class="form-control" id="NoticeofAward" name="NoticeofAward" value="<?php echo isset($ppmp['notice_of_award']) ? htmlspecialchars($ppmp['notice_of_award']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ContractSigning" class="form-label">Contract Signing</label>
                        <input type="date" class="form-control" id="ContractSigning" name="ContractSigning" value="<?php echo isset($ppmp['contract_signing']) ? htmlspecialchars($ppmp['contract_signing']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="sourceOfFunds" class="form-label">Source of Funds</label>
                        <select class="form-select" id="sourceOfFunds" name="sourceOfFunds" required>
                            <option value="GoP" <?php echo ($ppmp['source_of_funds'] == 'GoP') ? 'selected' : ''; ?>>GoP</option>
                            <option value="Foreign" <?php echo ($ppmp['source_of_funds'] == 'Foreign') ? 'selected' : ''; ?>>Foreign</option>
                            <option value="Special Purpose Fund" <?php echo ($ppmp['source_of_funds'] == 'Special Purpose Fund') ? 'selected' : ''; ?>>Special Purpose Fund</option>
                            <option value="Corporate Budget" <?php echo ($ppmp['source_of_funds'] == 'Corporate Budget') ? 'selected' : ''; ?>>Corporate Budget</option>
                            <option value="Income" <?php echo ($ppmp['source_of_funds'] == 'Income') ? 'selected' : ''; ?>>Income</option>
                            <option value="Others" <?php echo ($ppmp['source_of_funds'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Total" class="form-label">Total</label>
                        <input type="text" class="form-control" id="Total" name="Total" value="<?php echo htmlspecialchars($ppmp['total']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="MOOE" class="form-label">MOOE</label>
                        <input type="text" class="form-control" id="MOOE" name="MOOE" value="<?php echo htmlspecialchars($ppmp['mooe']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="CO" class="form-label">CO</label>
                        <input type="text" class="form-control" id="CO" name="CO" value="<?php echo htmlspecialchars($ppmp['co']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (Brief Description of Project)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" required><?php echo htmlspecialchars($ppmp['remarks']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update APP</button>
                    <a href="app.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>