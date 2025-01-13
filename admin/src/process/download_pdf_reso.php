<?php
ob_start();

// Include the TCPDF library and database connection
require_once('../../vendor/autoload.php');
require_once('../config/database.php');

// Check if reso_id parameter exists
if (!isset($_GET['reso_id'])) {
    die("RESO ID is required!");
}

// Get the reso id from URL
$reso_id = intval($_GET['reso_id']);


// Fetch resolution and latest statements
$sql = "SELECT r.*, 
       ws.statement AS Wstatement, 
       hs.statement AS Hstatement 
    FROM resolution AS r
    LEFT JOIN whereas_statements ws ON ws.resolution_id = r.resolution_id
    LEFT JOIN it_is_hereby_statements hs ON hs.resolution_id = r.resolution_id
    WHERE r.resolution_id = ?
    ORDER BY ws.created_at DESC, hs.created_at DESC";


$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $reso_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('No data found for the resolution.');
}


$resolution = [];
$whereasStatements = [];
$herebyStatements = [];

while ($row = $result->fetch_assoc()) {
    $resolution = $row;
    if (!empty($row['Wstatement']) && !in_array($row['Wstatement'], $whereasStatements)) {
        $whereasStatements[] = $row['Wstatement'];
    }
    if (!empty($row['Hstatement']) && !in_array($row['Hstatement'], $herebyStatements)) {
        $herebyStatements[] = $row['Hstatement'];
    }
}


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
            </table>
            EOD;
            $this->writeHTML($html, true, false, false, false, '');
        }
        
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Admin');
    $pdf->SetAuthor('System Administrator');
    $pdf->SetTitle('Resolution');

    // Set margins and add page
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);


    $date = date('F j, Y', strtotime($resolution['created_at']));
    $description = ucwords($resolution['description']);
    $name = ucwords($resolution['supplier_name']);
    $amountInWords = ucwords($resolution['total_amount_words']);
    
    // Generate "Whereas" section
    $whereasContent = "";
    foreach ($whereasStatements as $statement) {
        $whereasContent .= "<li>$statement</li>";
    }
    
    // Generate "Hereby" section
    $herebyContent = "";
    foreach ($herebyStatements as $statement) {
        $herebyContent .= "<li>$statement</li>";
    }

    $html = <<<EOD
        <header>
            <p style="text-align:center;">
                <b>RESOLUTION</b><br>
                No. {$resolution['resolution_id']}<br>
                {$date}<br><br>

                The Bids and Awards Committee (BAC) recommends the award for the<br>
                {$description} to {$name} with a contract amount<br>
                of {$amountInWords} (PHP {$resolution['total_amount_figures']}).
            </p>
        </header>
        <br>
        <br>
        <br>
        <br>
        <p>
            <b>Whereas:</b>
            <ol>
                {$whereasContent}
            </ol>    
            <div style="margin-top:30px"></div>
            <b>It is hereby resolved that:</b>
            <ol>
                {$herebyContent}
            </ol>  

            <div style="margin-top:40px"></div>
            <div></div>
            <div></div>
            <b>Recommending Approval:</b><br>

            <p style="text-align:center;">
                ______________________________<br>
                Signature over Printed Name
            </p><br>

            <div style="margin-top:30px"></div>
            <b>Approved:</b><br>

            <p style="text-align:center;">
                ______________________________<br>
                Signature over Printed Name
            </p><br>
        </p>
    EOD;
    // Write the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Clear output buffer
    ob_end_clean();

    // Output PDF document
    $pdf->Output('Resolution.pdf', 'D');
?>