<?php
ob_start();

// Include the TCPDF library and database connection
require_once('../../vendor/autoload.php');
require_once('../config/database.php');

// Check if project_title parameter exists
if (!isset($_GET['project_title'])) {
    die("Project title is required!");
}

// Get the project title from URL
$projectTitle = $_GET['project_title'];

try {
    // Query to fetch project and PMAF data
    $query = "SELECT p.modality, 
                 p.fund, 
                 p.mooe_items, 
                 p.co_amount, 
                 p.total_abc, 
                 pl.project_title as ppmp_title, 
                 pl.ppmp_id, 
                 CONCAT(eu.first_name, ' ', eu.last_name) as end_user
          FROM pmaf p
          LEFT JOIN ppmp_list pl ON p.project_title = pl.project_title
          LEFT JOIN end_users eu ON pl.user_id = eu.id
          WHERE pl.project_title = ?
          ORDER BY p.id DESC";


    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $projectTitle);
    if (!$stmt->execute()) {
        throw new Exception("SQL execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("No records found for the project: " . $projectTitle);
    }

    $data = $result->fetch_assoc();

    function decodeTripleJson($jsonString)
    {
        $firstDecode = json_decode($jsonString, true);
        $secondDecode = json_decode($firstDecode, true);
        return json_decode($secondDecode, true);
    }
    
    // Clean and process modality, fund, and MOOE data
    $modalityArray = decodeTripleJson(json_encode($data['modality']));
    $fundSource = $data['fund'];
    $mooe = $data['mooe_items'];
    
    $cleanModalities = !empty($modalityArray) ? array_map('trim', $modalityArray) : [];

    function convertNumberToWords($number) {
        $integerPart = floor($number);
        $decimalPart = round(($number - $integerPart) * 100);
    
        $integerWords = number_format($integerPart);  // Simple numeric representation
        $decimalWords = $decimalPart > 0 ? "{$decimalPart} centavos" : "";
    
        return ucfirst("{$integerWords} pesos " . ($decimalWords ? "and {$decimalWords}" : ""));
    }

    // Ensure these variables are defined before using them in the HTML
    $totalAbc = isset($data['total_abc']) ? (float)$data['total_abc'] : 0.00;
    $totalAbcFormatted = number_format($totalAbc, 2);  // Keep original format for numeric display
    $totalAbcInWords = convertNumberToWords($totalAbc);  // Convert to words

    

    // Determine approver based on Total ABC
    if ($totalAbc >= 500000) {
        $approverName = 'ENGR. REYNALDO P. RAMOS, Ph.D. EnP';
        $approverPosition = 'President';
    } else {
        $approverName = 'PURABELLA R. AGRON';
        $approverPosition = 'Vice President for Admin and Finance (IO No. 18, s., 2019)';
    }


    // Create PDF class with custom header
    class MYPDF extends TCPDF
    {
        public function Header()
        {
            $this->SetFont('helvetica', 'B', 10);
            $this->Image('../../../assets/images/logo.jpg', 20, 6, 17);
            $html = <<<EOD
            <div style="margin-top: 10px;"></div> 
            <table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;text-align:center;">
                <tr style="background-color:white;color:black;">
                    <td width="15%"></td>
                    <td width="85%">
                        TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES<br>
                        Ayala Blvd., Ermita, Manila, 1000, Philippines<br>
                        Tel No. +632-5301-3001 local 115 | Fax No. +632-8521-4063<br>
                        Email: bac@tup.edu.ph | Website: www.tup.edu.ph
                    </td>
                </tr>
            </table>
            EOD;
            $this->writeHTML($html, true, false, false, false, '');
        }
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('PMAF System');
    $pdf->SetAuthor('System Administrator');
    $pdf->SetTitle('PMAF - ' . ($data['ppmp_title'] ?? 'Untitled'));
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    $html = <<<EOD
    <style>
        .section-title { font-weight: bold; font-size: 13pt; margin-top: 15px;}
        .content { margin-left: 10px; }
        .label { font-weight: bold; color: #34495e; }
        .timestamp { font-size: 9pt; color: #7f8c8d; }
        .item-list { margin-left: 15px; }
    </style>

    <table width="100%" cellpadding="5" style="margin-bottom: 10px; border: 1px solid #000;">
        <tr>
            <td style="text-align:left;font-weight:bold;padding:10px;border: 1px solid #000;">
                To the members of the Bids and Awards Committee
            </td>
        </tr>
        <tr>
            <td style="text-align:left;padding:10px;border: 1px solid #000;">
                Goods, Equipment, other services, and Small Value Procurement
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:justify;padding:10px;">
                The attached Purchase Request/s is/are for evaluation to ensure the completeness of the technical 
                specifications, terms of reference, reasonable estimated price, delivery schedule, completeness of 
                bill of quantity and scope of works if the project includes civil works, after sales services, 
                warranty terms, and the procurement modality.
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:left;padding:10px;">
                Thank you.
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:left;padding-left:10px;">
                <div style="margin-top:10px;">
                    <strong style="text-decoration: underline;">PERAGRINO B. AMADOR, JR.</strong><br>
                    Head, Procurement Office
                </div>
            </td>
        </tr>
    </table>
    <div style="margin-top: 5px;"></div>

    <div class="section-title" style="text-align:center;font-weight:bold;padding:10px;border: 1px solid #000;">PROCUREMENT MODALITY</div>
    <table cellpadding="5" width="100%" style="margin-top: 10px; margin-bottom: 20px;border: 1px solid #000;">
EOD;




if (!empty($cleanModalities)) {
    $html .= "<tr>";
    $counter = 0;
    foreach ($cleanModalities as $modality) {
        if (!empty($modality)) {
            if ($counter % 2 == 0 && $counter != 0) {
                $html .= "</tr><tr>";
            }
            $html .= "<td width='50%' style='border: 1px solid #000000; background-color: #f5f5f5; padding: 8px;'>" .
                htmlspecialchars($modality) .
                "</td>";
            $counter++;
        }
    }
    // Fill empty cell if odd number of items
    if ($counter % 2 != 0) {
        $html .= "<td width='50%' style='border: 1px solid #000000; background-color: #f5f5f5; padding: 8px;'></td>";
    }
    $html .= "</tr>";
} else {
    $html .= "<tr><td style='border: 1px solid #000000; background-color: #f5f5f5; padding: 8px;'>No modalities specified</td></tr>";
}

$html .= "</table>";

$html .= <<<EOD
</table>    

<div style="margin-top: 20px;"></div> 
<!-- Project Title Section -->
<table width="100%" style="border: 1px solid #000; border-collapse: collapse; margin-top: 15px; background-color:rgb(255, 255, 255); color: black;">
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold; padding: 10px; border: 1px solid #000000;">
            PROJECT TITLE
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-size: 13px; font-weight: bold; padding: 15px; border: 1px solid #000000; background-color: white; color: black;">
            {$data['ppmp_title']}
        </td>
    </tr>
    <tr>
        <td style="padding: 10px; border: 1px solid #000000; font-size: 10px; text-align: left;background-color: white; color: black;">
            End-user: {$data['end_user']}
        </td>
        <td style="padding: 10px; border: 1px solid #000000; font-size: 10px; text-align: right;background-color: white; color: black;" colspan="2">
            Assigned to: RHEA L. CADAG
        </td>
    </tr>
</table>

<div style="margin-top: 20px;"></div>

<!-- Fund Source Section -->
<table width="100%" cellpadding="5" style="border: 1px solid #000; border-collapse: collapse;">
    <tr>
        <td style="text-align: center; font-weight: bold; padding: 10px; border: 1px solid #000000;">
            FUNDS AVAILABILITY
        </td>
        
    </tr>
    <tr>
        <td style="margin-top: 30px; border: 1px solid #000; padding: 8px;">
            <p><b>Fund Source:</b> {$fundSource}</p>
            <p><b>MOOE:</b> {$mooe}</p>
            <p><b>CO:</b> PHP {$data['co_amount']}</p>
            <p><b>Total ABC:</b> {$totalAbcInWords} (PHP {$totalAbcFormatted})</p>
            <div style="margin-top: 30px; text-align: center;">
                <p>Signature: _____________________</p>
                <p>Name: <u><b>VIVIAN C. SANTOS</b></u></p>
                <p>Accounting / Budget Officer</p>
            </div>
        </td>
    </tr>
</table>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->AddPage();



 // Bids And Awards Committee
    $new_html = <<<EOD
        <div style="margin-top: 30px;"></div> 
        <table width="100%" cellpadding="5" style="border:1px solid black;">
            <!-- Header Row -->
            <tr>
                <td colspan="4" style="text-align: center; font-weight: bold; border: 1px solid #000;">
                    BIDS AND AWARDS COMMITTEE
                </td>
            </tr>
            <!-- Content Row -->
            <tr>
                <td colspan="4" style="padding: 10px;">
                    BAC recommends for approval the conduct of procurement process in compliance with R.A. 9184 and its IRR also known
                    as the Government Procurement Reform Act to procure the request based on the technical specifications and market
                    study of the end-user stated on its submitted purchase request, terms of reference, justification, and other relevant
                    documents.<br><br>
                    Recommending Approval:
                </td>
            </tr>
            <!-- Members Row -->
            <div style="margin-top: 10px;"></div>
            <tr>
                <td style="text-align:center;font-weight: bold; padding: 10px;">JONEL R. MACALISANG<br><span style="font-weight: normal;">Member</span></td>
                <td colspan="2" style="text-align: center; font-weight: bold; padding: 10px;">ROVENSON V. SEVILLA<br><span style="font-weight: normal;">Member</span></td>
                <td style="text-align: center; font-weight: bold; padding: 10px;">ELPIDIO S. VIRREY<br><span style="font-weight: normal;">Member</span></td>
            </tr>
            
            <div style="margin-top: 10px;"></div>
            <tr>   
                <td colspan="2" style="text-align: center; font-weight: bold; padding: 10px;">
                    ANDREW JOHN J. MABAQUIAO<br>
                    <span style="font-weight: normal;">Vice Chairperson</span>
                </td>
                <td colspan="2" style="text-align: center; font-weight: bold; padding: 10px;">RYAN C. REYES, DT<br><span style="font-weight: normal;">Chairperson</span></td>
            </tr>

            <!-- Approval Row -->
            
            <tr>
                <td colspan="4" style="text-align: center; padding: 10px;">
                    <p style="margin: 0;">Approved:</p>
                    <p style="font-weight: bold; margin: 5px 0;">{$approverName}</p>
                    <p style="margin: 0;">{$approverPosition}</p>
                </td>
            </tr>
        </table>
EOD;

    $pdf->writeHTML($new_html, true, false, true, false, '');
    ob_end_clean();
    $pdf->Output('PMAF_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $data['ppmp_title']) . '.pdf', 'D');

} catch (Exception $e) {
    ob_end_clean();
    die("Error: " . $e->getMessage());
}

