<?php
// Include the TCPDF library and database connection
require_once('../../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../config/database.php');

// Check if aoq_id parameter exists
if (!isset($_GET['aoq_id'])) {
    die("AOQ ID is required!");
}

// Get the aoq id from URL
$aoq_id = intval($_GET['aoq_id']);


// Fetch AOQ and related RFQ data
$sql = "SELECT 
            aoq.project_location, 
            aoq.implementing_office, 
            aoq.approved_budget, 
            aoq.prepared_by, 
            aoq.verified_by, 
            aoq.created_at, 
            rfq.project_title, 
            rfq.end_user,
            tups.tup_specification,
            tups.quantity,
            tups.unit
        FROM abstract_of_quotation AS aoq
        JOIN rfq ON aoq.rfq_id = rfq.rfq_id
        JOIN tup_specifications tups ON tups.aoq_id = aoq.aoq_id
        WHERE aoq.aoq_id = ? ORDER BY tup_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $aoq_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('No data found for the given AOQ ID.');
}

$data = $result->fetch_all(MYSQLI_ASSOC);

class MYPDF extends TCPDF
{
    public function Header()
    {
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

// Generate PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Abstract of Quotation');

// Set margins and add page
$pdf->SetMargins(15, 45, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Date and Time
$date = date('m/d/Y', strtotime($data[0]['created_at']));
$time = date('h:i A', strtotime($data[0]['created_at']));

$inWordsApprovedBudget = ucwords(NumberFormatter::create('en', NumberFormatter::SPELLOUT)->format($data[0]['approved_budget']));

// Generate the content

$html = <<<EOD
<h3 style="text-align:center;">ABSTRACT OF QUOTATION</h3>
<table>
    <tr>
        <td width="70%">
            <table cellpadding="3">
                <tr>
                    <th style="border: 1px solid black;"><b>Project:</b></th>
                    <td style="border: 1px solid black;">{$data[0]['project_title']}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid black;"><b>Project Location:</b></th>
                    <td style="border: 1px solid black;">{$data[0]['project_location']}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid black;"><b>Implementing Office:</b></th>
                    <td style="border: 1px solid black;">{$data[0]['implementing_office']}</td>
                </tr>
                <tr>
                    <th style="border: 1px solid black;"><b>Approved Budget for the Contract:</b></th>
                    <td style="border: 1px solid black;">
                        <table>
                            <tr>
                                <td><b>In Words:</b> {$inWordsApprovedBudget}</td>
                            </tr>
                            <tr>
                                <td><b>In Figures:</b> PHP {$data[0]['approved_budget']}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td width="30%">
            <table cellpadding="3">
                <tr>
                    <td style="border: 1px solid black;"><b>Date:</b></td>
                    <td style="border: 1px solid black;">{$date}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>Time:</b></td>
                    <td style="border: 1px solid black;">{$time}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>
<br>
<br>
<table cellpadding="3">
    <tr>
        <th style="text-align:center; background-color: rgb(202, 202, 202);border: 1px solid black;" colspan="3" ><b>SPECIFICATIONS</b></th>
    </tr>
    <tr>
        <th style="border: 1px solid black;"><b>TUP's Specification</b></th>
        <th style="border: 1px solid black;"><b>Qty.</b></th>
        <th style="border: 1px solid black;"><b>Unit</b></th>
    </tr>
    <tr>
        <td style="border: 1px solid black;">{$data[0]['tup_specification']}</td>
        <td style="border: 1px solid black;">{$data[0]['quantity']}</td>
        <td style="border: 1px solid black;">{$data[0]['unit']}</td>
    </tr>
</table>

<br>
<br>
<br>
<h3 style="text-align:center;">COMPANY DETAILS</h3>
EOD;
$new_sql = "WITH consecutive_groups AS (
    SELECT *,
       @group_num := IF(
           @prev_aoq_id = aoq_id,
           @group_num,
           @group_num + 1
       ) AS group_num,
       @prev_aoq_id := aoq_id AS prev_aoq
        FROM (SELECT * FROM company_details ORDER BY company_id DESC) cd,
        (SELECT @prev_aoq_id := NULL, @group_num := 0) vars
    ),
    first_group AS (
        SELECT MIN(group_num) AS first_group_num
        FROM consecutive_groups
    )
    SELECT cd.*
    FROM company_details cd
    JOIN consecutive_groups cg ON cd.company_id = cg.company_id
    JOIN first_group fg ON cg.group_num = fg.first_group_num
    ORDER BY cd.company_id DESC;
";
$stmtt = $conn->prepare($new_sql);
$stmtt->execute();
$res = $stmtt->get_result();

$company_data = $res->fetch_all(MYSQLI_ASSOC);

// Check if company data exists, and if not, provide a default message
if (empty($company_data)) {
    $html .= <<<EOD
    <br>
    <br>
    <br>
    <p style="text-align:center; color:red;"><b>No Company Details Available.</b></p>
EOD;
} else {
    foreach ($company_data as $row) {
        $name = strtoupper($row['company_name']);
        $unit_price = 'PHP ' . number_format($row['unit_price'], 2);
        $total_price = 'PHP ' . number_format($row['total_price'], 2);

        $html .= <<<EOD
        <br>
        <br>
        <br>
        <table cellpadding="3">
            <tr>
                <th style="text-align:center; background-color: rgb(202, 202, 202);border: 1px solid black;" colspan="3" ><b>{$name}</b></th>
            </tr>
            <tr>
                <th style="border: 1px solid black;"><b>Bidder's Specification</b></th>
                <th style="border: 1px solid black;"><b>Unit Price</b></th>
                <th style="border: 1px solid black;"><b>Total Price</b></th>
            </tr>
            <tr>
                <td style="border: 1px solid black;">{$row['bidders_specification']}</td>
                <td style="border: 1px solid black;">{$unit_price}</td>
                <td style="border: 1px solid black;">{$total_price}</td>
            </tr>
        </table>
    EOD;
    }
}

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->AddPage();

$prepared_by = strtoupper($data[0]['prepared_by']);
$verified_by = strtoupper($data[0]['verified_by']);
$new_html .= <<<EOD
    <br><br><br><br><br><br>
    <table cellpadding="3" align="center">
        <tr>
            <td align="left" width="50%" style="text-align: left;">
                <b>Prepared by:</b><br>
                <u><b>{$prepared_by}</b></u>
                <p>Head, Procurement</p>
            </td>
            <td align="left" width="50%" style="text-align: left;">
                <b>Verified by:</b><br>
                <u><b>{$verified_by}</b></u>
                <p>Head, Procurement Office</p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <br><br>
            </td>
        </tr>
        <tr>
            <td align="center" width="33%" style="text-align: center;">
                <u><b>FRANCISCO D. ESPONILLA II ED.D.</b></u>
                <p>Member</p>
            </td>
            <td align="center" width="33%" style="text-align: center;">
                <u><b>ANGELICA B. HARRIS</b></u>
                <p>Member</p>
            </td>
            <td align="center" width="33%" style="text-align: center;">
                <u><b>ELMER M. SANGALANG</b></u>
                <p>Member</p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <br><br>
            </td>
        </tr>
        <tr>
            <td align="center" width="30%" style="text-align: center;">
                <u><b>LOUIE V. SORIANO</b></u>
                <p>Vice Chairperson</p>
            </td>
            <td align="center" width="70%" style="text-align: center;">
                <b><u>LYNDON R. BAGUE, PEE, MEng., ASEAN Engr.</u></b>
                <p>Chairperson</p>
            </td>
        </tr>
    </table>
EOD;
// Write the HTML content
$pdf->writeHTML($new_html, true, false, true, false, '');

// Clear output buffer
ob_end_clean();

// Output the PDF
$pdf->Output('Abstract Of Quotation.pdf', 'D');
