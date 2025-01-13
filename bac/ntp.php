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

// Fetch NOA records
$noaRecords = [];
if ($conn) {
    $sql = "SELECT noa_id, authorized_representative, designation, company_name, project_title 
            FROM notice_of_award";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $noaRecords[] = $row;
        }
    }
}

// Default values for NOA data
$authorizedRepresentative = '';
$designation = '';
$companyName = '';
$projectTitle = '';
$dateToday = date('F d, Y');

// Update values if a specific NOA is selected
if (isset($_GET['noa_id'])) {
    $selectedNOA = intval($_GET['noa_id']);
    $sql = "SELECT authorized_representative, designation, company_name, project_title 
            FROM notice_of_award 
            WHERE noa_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selectedNOA);
    $stmt->execute();
    $stmt->bind_result($authorizedRepresentative, $designation, $companyName, $projectTitle);
    $stmt->fetch();
    $stmt->close();
}
$current_page = 'ntp.php';

// Fetch procurement titles 
$titleQuery = "SELECT * FROM procurement_titles";
$titleResult = mysqli_query($conn, $titleQuery);
$titles = [];

if ($titleResult) {

    while ($titleRow = mysqli_fetch_assoc($titleResult)) {

        $titles[$titleRow['page']] = $titleRow;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice to Proceed</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="src/css/pr.css">
    <style>
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .notice-container {
            border: 1px solid #000;
            padding: 20px;
            margin: auto;
            max-width: 800px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .signature-section {
            margin-top: 30px;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .btn-proceed {
            display: block;
            margin: 30px auto;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content flex-grow-1 animate__animated animate__fadeIn">
            <div class="header-card mb-4">
                <h2><?= isset($titles[$current_page]) ? $titles[$current_page]['title'] : 'Notice to Proceed'; ?></h2>
                <form method="GET" class="mb-4">
                    <label for="noa_id" class="form-label">Select NOA:</label>
                    <select name="noa_id" id="noa_id" class="form-select" onchange="this.form.submit()">
                        <option value="" selected disabled>Choose...</option>
                        <?php foreach ($noaRecords as $noa): ?>
                            <option value="<?= $noa['noa_id'] ?>"
                                <?= isset($_GET['noa_id']) && $_GET['noa_id'] == $noa['noa_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($noa['project_title']) ?> (<?= htmlspecialchars($noa['company_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Notice to Proceed Layout -->
            <div class="notice-container">
                <h2 class="text-center fw-bold">NOTICE TO PROCEED</h2>
                <p class="text-end"><?= $dateToday ?></p>

                <p>
                    <?= htmlspecialchars($authorizedRepresentative) ?> <br>
                    <?= htmlspecialchars($designation) ?> <br>
                    <strong><?= htmlspecialchars($companyName) ?></strong> <br>
                </p>

                <p>Sir/Madam:</p>

                <p>
                    Please be informed that you are given this <strong>Notice to Proceed</strong> to execute the contract
                    for the project <em>“<?= htmlspecialchars($projectTitle) ?>”</em>,
                    copy of which is hereto attached.
                </p>

                <p>
                    We appreciate your interest in this project, and we look forward to a satisfactory performance of
                    your obligations under the contract.
                </p>

                <p>
                    Kindly acknowledge receipt and acceptance of this notice on the space provided below.
                </p>

                <p>Very truly yours,</p>

                <p class="fw-bold">PURABELLA R. AGRON</p>
                <p>Vice President for Admin and Finance</p>

                <!-- Signature Section -->
                <div class="signature-section mt-4">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th class="text-center" colspan="2">Received by:</th>
                        </tr>
                        <tr>
                            <td style="height: 60px;">(SIGNATURE OVER PRINTED NAME & DATE)</td>
                            <td style="height: 60px;">NAME OF COMPANY</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center">
                                <small>(PLEASE RETURN THIS NOTICE TO PROCEED)</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Proceed Button Section -->
            <div class="d-flex justify-content-center align-items-end" style="min-height: 100px;">
                <a href="pp.php" class="btn btn-primary">Proceed to PO</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>