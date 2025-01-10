<?php
require_once(__DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../config/pdo.php');

// Get RFQ ID from URL
$rfq_id = isset($_GET['id']) ? $_GET['id'] : die('No RFQ ID provided');

// Prepare and execute SQL query to get RFQ data
$sql = "SELECT r.*, 
               r.rfq_id,
               r.project_title,
               r.pr_request_number,
               r.end_user,
               r.date_created,
               r.deadline_submission,
               r.approved_budget,
               r.procurement_mode
        FROM rfq r 
        WHERE r.rfq_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$rfq_id]);
$rfq = $stmt->fetch(PDO::FETCH_ASSOC);

// Get RFQ Items
$sql_items = "SELECT * FROM rfq_items WHERE rfq_id = ? ORDER BY id ASC";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$rfq_id]);
$rfq_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Check if RFQ exists
if (!$rfq) {
    die('RFQ not found');
}

class MYPDF extends TCPDF
{
    public function Header()
    {
        // Set font for university name
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'TECHNOLOGICAL UNIVERSITY OF THE PHILIPPINES', 0, 1, 'C');

        $this->Image('../../../assets/images/logo.jpg', 18, 6, 17);

        // Set font for address and contact details
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Ayala Blvd., Ermita, Manila, 1000, Philippines | Tel No. +632-5301-3001 local 115', 0, 1, 'C');
        $this->Cell(0, 5, 'Fax No. +632-8521-4063 | Email: procurement@tup.edu.ph | Website: www.tup.edu.ph', 0, 1, 'C');

        // Add some space
        $this->Ln(5);

        // Add RFQ Form title
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'REQUEST FOR QUOTATION FORM', 0, 1, 'C');

        // Add more space after the header
        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TUP');
$pdf->SetAuthor('Procurement Office');
$pdf->SetTitle('Request for Quotation #' . $rfq_id);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Add styling
$style = '
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }
    th, td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 10px;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .section-header {
        font-size: 12px;
        font-weight: bold;
        background-color: #e9ecef;
        padding: 5px;
        margin: 10px 0;
        border: 1px solid black;
    }
</style>
';

// Basic Information
$html = $style . '
<table width="100%">
    <tr style="text-align: right">
        <th><b>QUOTATION NUMBER: ' . $rfq_id . '</b></th>
    </tr>
    <tr>
        <td>
            <b>INSTRUCTION TO BIDDERS</b><br>
            1. Please complete the following information in your bid Failure to comply with the mandatory requirements shall render the quotation ineligible / disqualified. <br>
            a. Company Name, Address, Contact No., TIN, Email Address, Bank Name and Account Number<br>
            b. Bidder\'s offer / warranty period (technical specifications / brand) per item.<br>
            c. Unit Price, Total Price, and Total Amount<br>
            d. Name of the Bidder\'s Authorized Representative<br>
            e. Signature and Date<br>
            2. TuPreserves the right to reject any or all bids, to waive any information herein or to accept such bids as may consider most advantageous to the university.<br>
            3. Alternative bids or bids offering multiple price options will be rejected.<br>
            4. Quotation must be inclusive of all applicable government taxes.<br>
            5. Cash on delivery (cod) will not be accepted.<br>
            6. Attach brochure and indicate delivery period<br>
            7. Conduct site inspection prior to submitting the quotation, for goods that require installation.<br>
            8. The bidder\'s line of business should be relevant to the procurement project and have a similar contract within the last 2 years.<br>
            9. The payment for the service rendered shall be made upon issuance of billing statement and the corresponding certificate of satisfactory service by the end-user. Failure to comply with the terms and conditions of the contract will result in the payment of the corresponding penalties/liquidated damages in the amount equal to 10% of the contract prices by the winning bidder.
        </td>
    </tr>
</table>

<div style="margin-top:10px;"></div>
<div class="section-header">FOR COMPLETION BY THE PROCUREMENT PERSONNEL</div>
<table>
    <tr>
        <td><strong>Project Title:</strong></td>
        <td>' . $rfq['project_title'] . '</td>
    </tr>
    <tr>
        <td><strong>PR Request Number:</strong></td>
        <td>' . $rfq['pr_request_number'] . '</td>
    </tr>
    <tr>
        <td><strong>End User:</strong></td>
        <td>' . $rfq['end_user'] . '</td>
    </tr>
    <tr>
        <td><strong>Date Created:</strong></td>
        <td>' . date('F d, Y', strtotime($rfq['date_created'])) . '</td>
    </tr>
    <tr>
        <td><strong>Deadline of Submission:</strong></td>
        <td>' . date('F d, Y', strtotime($rfq['deadline_submission'])) . '</td>
    </tr>
</table>

<div style="margin-top:10px;"></div>
<div class="section-header">BUDGET AND PROCUREMENT DETAILS</div>
<table>
    <tr>
        <td width="30%"><strong>Approved Budget:</strong></td>
        <td width="70%">P' . number_format($rfq['approved_budget'], 2) . '</td>
    </tr>
    <tr>
        <td><strong>Mode of Procurement:</strong></td>
        <td>' . $rfq['procurement_mode'] . '</td>
    </tr>
</table>

<div style="margin-top:10px;"></div>
<div class="section-header">ITEMS REQUESTED</div>
<table>
    <thead>
        <tr>
            <th style="vertical-align:center;">No.</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Item Name</th>
            <th>Technical Specifications</th>
            <th>Unit Cost</th>
            <th>Bidder Offer Spec</th>
            <th>Quoted Price</th>
        </tr>
    </thead>
    <tbody>';

$total = 0;
foreach ($rfq_items as $index => $item) {
    $itemTotal = $item['quantity'] * $item['unit_cost'];
    $total += $itemTotal;
    $html .= '
        <tr>
            <td>' . ($index + 1) . '</td>
            <td>' . $item['quantity'] . '</td>
            <td>' . $item['unit'] . '</td>
            <td>' . $item['general_name'] . '</td>
            <td>' . $item['tech_specification'] . '</td>
            <td align="right">P' . number_format($item['unit_cost'], 2) . '</td>
            <td>' . $item['bidder_offer_specification'] . '</td>
            <td align="right">P' . ($item['quoted_unit_price'] ? number_format($item['quoted_unit_price'], 2) : '-') . '</td>
        </tr>';
}

$html .= '
        <tr>
            <td colspan="7" align="right"><strong>Total Amount:</strong></td>
            <td align="right"><strong>P' . number_format($total, 2) . '</strong></td>
        </tr>
    </tbody>
</table>';
// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->AddPage();
$new_html = $style .  '
<div style="margin-top:10px;"></div>
<div class="section-header">ELIGIBILITY AND OTHER REQUIREMENTS</div>
<table class="requirements-table" style="margin-bottom: 15px;">
    <tr>
        <td width="50%" style="padding: 5px;">
            A. Valid and Current Business/Mayor\'s Permit;<br>
            B. Professional License / Curriculum Vitae (Consulting Service)<br>
            C. Valid and Current PhilGEPS Registration Number or Certificate;<br>
            D. Valid and Current PCAB License (Infrastructure)
        </td>
        <td width="50%" style="padding: 5px;">
            E. Income / Business Tax Return; and<br>
            F. Omnibus Sworn Statement and/or Notarized Secretary\'s Certificate/Board Resolution/PartnershiPResolution/ Special Power of Attorney / DTI Certificate Number, whichever is applicable.
        </td>
    </tr>
</table>

<table style="width: 100%;">
    <tr>
        <td width="60%" style="border: 1px solid #000; padding: 10px;">
            <div style="font-weight: bold;">TO BE FILLED OUT BY THE SUPPLIER</div>
            <table style="width: 100%; margin-top: 10px;">
                <tr>
                    <td width="30%">Name of the Company:</td>
                    <td width="70%" style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Bank Name:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Bank Account Number:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Landline Number:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Mobile Number:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Email Address:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
            </table>
            <div style="margin-top: 15px;">
                <div style="font-weight: bold;">SUPPLIER/CONTRACTOR/SERVICE PROVIDER\'S DECLARATION:</div>
                <Pstyle="font-size: 9px; text-align: justify;">After having carefully read the Instructions and Technical Specifications, I hereby certify to comply with all the above requirements and shall undertake, if our bid is accepted to commence the delivery / service / works as soon as is reasonably possible after the receipt of the Notice of Award or Notice to Proceed and deliver the whole services inclusive of all cost and applicable taxes within the time stated in the TOR/detailed specifications signed by our company\'s authorized representative.</Pstyle=>
                <div style="border-top: 1px solid #000; margin-top: 30px;">&nbsp;</div>
            </div>
            <div style="margin-top:5px;"></div>
            <div style="text-align: center;">
                <div>Bidder\'s Authorized Representative</div>
                <div>_____________________________________________</div>
                <div>(Printed Name and Signature)</div>
            </div>
        </td>
        <td width="40%" style="border: 1px solid #000; padding: 10px;">
            <div style="font-weight: bold;">SIGNATURE OF THE AUTHORIZED PERSONNEL</div>
            <table style="width: 100%; margin-top: 10px;">
                <tr>
                    <td>Date of Canvass:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
                <tr>
                    <td>Canvassed by:</td>
                    <td style="border-bottom: 1px solid #000;">&nbsp;</td>
                </tr>
            </table>
            <div style="margin-top: 20px; text-align: center;">
                <div style="border-bottom: 1px solid #000; margin: 30px 0 5px 0;">&nbsp;</div>
                <div>Buyer\'s Name and Signature</div>
                <div style="text-align: left;">Date: ______________________</div>
            </div>
            <div style="margin-top: 20px;">
                <div style="font-weight: bold; text-align: center;">End-User\'s Acknowledgement</div>
                <div style="border-bottom: 1px solid #000; margin: 30px 0 5px 0;">&nbsp;</div>
                <div style="text-align: center;">Printed Name and Signature</div>
                <div style="text-align: left;">Date: ______________________</div>
            </div>
            <div style="margin-top: 20px;">
                <div style="text-align: center;">By the Authority of the Procurement Office</div>
                <div style="border-bottom: 1px solid #000; margin: 30px 0 5px 0;">&nbsp;</div>
                <div style="text-align: center; font-weight: bold;">PERAGRINO B. AMADOR, JR.</div>
                <div style="text-align: left;">Date: ______________________</div>
            </div>
        </td>
    </tr>
</table>

';

// Output the HTML content
$pdf->writeHTML($new_html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('RFQ_' . $rfq_id . '.pdf', 'D');
