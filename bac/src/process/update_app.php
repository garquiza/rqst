<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the POST data
    $ppmp_id = $_POST['ppmp_id'];
    $project_title = $_POST['project_title'];
    $pmo = $_POST['pmo'];
    $status = $_POST['status'];
    $modeOfProcurement = $_POST['modeOfProcurement'];
    $advertisementPosting = $_POST['Advertisement/PostingofIB/REI'];
    $submissionOpeningBids = $_POST['Submission/OpeningofBids'];
    $noticeOfAward = $_POST['NoticeofAward'];
    $contractSigning = $_POST['ContractSigning'];
    $sourceOfFunds = $_POST['sourceOfFunds'];
    $total = $_POST['Total'];
    $mooe = $_POST['MOOE'];
    $co = $_POST['CO'];
    $remarks = $_POST['remarks'];

    // Check ppmp_id existence in app table
    $query_check = "SELECT ppmp_id FROM app WHERE ppmp_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $ppmp_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $query_insert = "INSERT INTO app (
            ppmp_id, pmo_end_user, early_procurement_activity, mode_of_procurement,
            advertisement_posting_ib_rei, submission_opening_bids, notice_of_award,
            contract_signing, source_of_funds, total, mooe, co, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param(
            "issssssssssss",
            $ppmp_id,
            $pmo,
            $status,
            $modeOfProcurement,
            $advertisementPosting,
            $submissionOpeningBids,
            $noticeOfAward,
            $contractSigning,
            $sourceOfFunds,
            $total,
            $mooe,
            $co,
            $remarks
        );
        $stmt_insert->execute();
    }

    $query = "UPDATE ppmp_list SET
                project_title = ? WHERE ppmp_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $project_title, $ppmp_id);

    if ($stmt->execute()) {
        $query_update = "UPDATE app SET
            pmo_end_user = ?, 
            early_procurement_activity = ?, 
            mode_of_procurement = ?, 
            advertisement_posting_ib_rei = ?, 
            submission_opening_bids = ?, 
            notice_of_award = ?, 
            contract_signing = ?, 
            source_of_funds = ?, 
            total = ?, 
            mooe = ?, 
            co = ?, 
            remarks = ?
            WHERE ppmp_id = ?";

        // Update the app table
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param(
            "sssssssssssss",
            $pmo,
            $status,
            $modeOfProcurement,
            $advertisementPosting,
            $submissionOpeningBids,
            $noticeOfAward,
            $contractSigning,
            $sourceOfFunds,
            $total,
            $mooe,
            $co,
            $remarks,
            $ppmp_id
        );

        if ($stmt_update->execute()) {
            header('Location: /rqst/bac/app.php');
        } else {
            die("Error updating app table: " . $stmt_update->error);
        }
    } else {
        die("Error updating ppmp_list table: " . $stmt->error);
    }
}

$stmt->close();
