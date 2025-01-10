<?php
ob_start();

// Include the TCPDF library and database connection
require_once('../../vendor/autoload.php');
require_once('../config/database.php');

// Check if noa_id parameter exists
if (!isset($_GET['noa_id'])) {
    die("NOA ID is required!");
}

// Get the noa id from URL
$noa_id = intval($_GET['noa_id']);

// Fetch aoq
$sql = "SELECT * FROM notice_of_award WHERE noa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $noa_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('No data found for the given NOA ID.');
}

$data = $result->fetch_assoc();

class MYPDF extends TCPDF { 
        public function Header() {
            $this->SetFont('helvetica', 'B', 10);
            
            // Add image using TCPDF Image method
            // Parameters: Image(file, x, y, width, height)
            $this->Image('../../../assets/images/logo.jpg', 20, 6, 17); // Adjust coordinates and size as needed
            
            $html = <<<EOD
            <div style="margin-top: 10px;"></div> 
            <table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;text-align:center;">
                <tr style="background-color:white;color:black;">
                    <td width="15%">
                    </td>
                    <td width="85%">
                        TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES<br>
                        Ayala Blvd., Ermita, Manila, 1000, Philippines<br>
                        Tel No. +632-5301-3001 local 115 | Fax No. +632-8521-4063<br>
                        Email: bac@tup.edu.ph | Website: www.tup.edu.ph
                    </td>
                </tr>
                <!-- Rest of your table -->
            </table>
            EOD;
            $this->writeHTML($html, true, false, false, false, '');
        }
        
    }



    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Admin');
    $pdf->SetAuthor('System Administrator');
    $pdf->SetTitle('Notice of Award');

    // Set margins and add page
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $date = date('m/d/Y', strtotime($data['created_at']));
    $amountInWords = ucwords($data['contract_amount_words']);

    $html = <<<EOD
        <p style="text-align: left">
            <b>Date: </b>{$date}
            <br>
            <br>
            <br>
            <br>
            <b>{$data['authorized_representative']}</b><br>
            {$data['designation']}<br>
            <b>{$data['company_name']}</b><br><br><br><br>

            Dear {$data['authorized_representative']}:<br>
            We are pleased to inform you that your quotation for the "{$data['project_title']} <small>(PhilGEPS Reference Number: {$data['philgeps_reference']})</small>"
            with a corresponding bid price of {$amountInWords} (PHP {$data['contract_amount_figures']}) has been determined to be the <small>Reason: </small> _______________________________.<br><br>

            We appreciate your interest in this project, and we look forward to a satisfactory performance of your obligations under the contract.
            <br>
            <br>
            <br>
            Very truly yours,
            <br>
            <br>
            <br>
            <br>
            <br>
            ___________________________<br>
            Signature over Printed Name
            <br>
            <br>
            <br>
            <br>
            <br>
            Conforme :<br>
            <br>
            <br>
            <br>
            <br>
            <br>
            Date & Time : ___________________<br>
            <small><b><i>*Kindly email or fax signed notice to sender <br>
            to acknowledge receipt and acceptance
            </i></b></small>
        </p>
        

    EOD;
    // Write the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Clear output buffer
    ob_end_clean();

    // Output PDF document
    $pdf->Output('Notice of Award.pdf', 'D');
?>