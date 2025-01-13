<?php
// Include the TCPDF library and database connection
require_once('../../vendor/autoload.php');
require_once('../config/database.php');

ob_start(); // Start output buffering to capture any accidental output


// Get purchase order ID from the URL
$purchase_order_id = isset($_GET['purchase_order_id']) ? (int)$_GET['purchase_order_id'] : null; 

if ($purchase_order_id === null) {
    die("Error: Purchase order ID not provided in the URL.");
}

//SQL query to join the tables
$sql = "SELECT po.*, noa.authorized_representative, noa.designation, noa.company_name, noa.philgeps_reference
FROM purchase_orders po
JOIN notice_of_award noa ON po.noa_id = noa.noa_id
WHERE po.id = ?";

$po_id = $purchase_order_id - 1;

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $po_id);
$stmt->execute();
$result = $stmt->get_result();
$purchase_order = $result->fetch_assoc();

if (!$purchase_order) {
    die("Error: Purchase order not found.");
}

// Fetch purchase order items data (using prepared statement)
$sql_items = "SELECT * FROM purchase_order_items WHERE purchase_order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $purchase_order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$purchase_order_items = $result_items->fetch_all(MYSQLI_ASSOC);


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

// Create an instance of TCPDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);;

// Set document information
$pdf->SetCreator('NTP');
$pdf->SetAuthor('System Administrator');
$pdf->SetTitle('Purchase Order');

// Set margins and add page
$pdf->SetMargins(15, 35, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('helvetica', '', 11);

// Add a page
$pdf->AddPage();

$html = <<<EOD
<style>
    .header-table { width: 100%;}
    .content-table { border: 1px solid black; width: 100%; }
    .footer-table { width: 100%; margin-top: 20px; }
    .border-cell { border: 1px solid black; }
</style>

<h2 style="text-align: center; font-weight: bold;">PURCHASE ORDER</h2>
<br>
<br>
<br>
<br>

<table class="header-table" cellpadding="5">
    <tr>
        <td><b>Supplier:</b> <u>{$purchase_order['supplier']} </u></td>
        <td><b>PhilGEPS Reference No.:</b> <u>{$purchase_order['philgeps_reference']}</u></td>
    </tr>
    <tr>
        <td><b>Address:</b> <u>{$purchase_order['address']}</u></td>
        <td><b>Project Name:</b> <u>{$purchase_order['project_name']}</u></td>
    </tr>
    <tr>
        <td><b>City:</b> <u>{$purchase_order['city']}</u></td>
        <td><b>Location:</b> <u>{$purchase_order['project_location']}</u></td>
    </tr>
    <tr>
        <td><b>Telephone:</b> <u>{$purchase_order['telephone_no']}</u></td>
        <td><b>Mode of Procurement:</b> <u>{$purchase_order['mode_of_procurement']}</u></td>
    </tr>
    <tr>
        <td colspan="2"><b>TIN:</b> <u>{$purchase_order['tin']}</u></td>
    </tr>
    <tr>
        <td><b>Authorized Representative:</b> <u>{$purchase_order['authorized_representative']}</u></td>
        <td><b>Designation:</b> <u>{$purchase_order['designation']}</u></td>
    </tr>
    <tr>
        <td colspan="2"><b>Company Name:</b> <u>{$purchase_order['company_name']}</u></td>
    </tr>
</table>

<br><br>
<br>
<br>

<table class="content-table" cellpadding="5">
    <thead>
        <tr>
            <th class="border-cell" colspan="6">
                <b>Gentlemen/Ladies:</b><br>
                Please furnish this office the following articles subjects to the terms and conditions contained herein:
            </th>
        </tr>
        <tr>
            <th class="border-cell"><b>Place of Delivery:</b></th>
            <th class="border-cell" colspan="2">{$purchase_order['place_of_delivery']}</th>
            <th class="border-cell"><b>Delivery Terms:</b></th>
            <th class="border-cell" colspan="2">{$purchase_order['delivery_terms']}</th>
        </tr>
        <tr>
            <th class="border-cell"><b>Date of Delivery:</b></th>
            <th class="border-cell" colspan="2">{$purchase_order['date_of_delivery']}</th>
            <th class="border-cell"><b>Payment Terms:</b></th>
            <th class="border-cell" colspan="2">{$purchase_order['payment_terms']}</th>
        </tr>
        <tr>
            <th class="border-cell">Stock #</th>
            <th class="border-cell">Unit</th>
            <th class="border-cell">Description</th>
            <th class="border-cell">Qty</th>
            <th class="border-cell">Unit Cost</th>
            <th class="border-cell">Amount</th>
        </tr>
    </thead>
    <tbody>
EOD;

// Append stock data
$stock_index = 1;
$total_amount = 0;

foreach ($purchase_order_items as $item) {
    $total_amount += $item['amount'];;

    $html .= <<<EOD
    <tr>
        <td class="border-cell">$stock_index</td>
        <td class="border-cell">{$item['unit']}</td>
        <td class="border-cell">{$item['description']}</td>
        <td class="border-cell">{$item['quantity']}</td>
        <td class="border-cell">P {$item['unit_cost']}</td>
        <td class="border-cell">P {$item['amount']}</td>
    </tr>
EOD;
    $stock_index++;
}

$total_amount_formatted = number_format($total_amount, 2);

$total_amount_in_words = ucwords(NumberFormatter::create('en', NumberFormatter::SPELLOUT)->format($total_amount));


// Append footer
$html .= <<<EOD
    </tbody>
</table>
<br>
<table cellpadding="5">
    <tr>
        <td class="border-cell" colspan="5" style="text-align:right;"><strong>Total Amount:</strong></td>
        <td class="border-cell">P $total_amount_formatted</td>
    </tr>
    <tr>
        <td class="border-cell" colspan="6"><strong>Total Amount in Words:  </strong> $total_amount_in_words</td>
    </tr>
</table>
<br><br>
EOD;
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->AddPage();

$new_html = <<<EOD
<table class="footer-table" cellpadding="5">
    <tr>
        <td>In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one (1) percent for every day of delay shall be imposed.</td>
    </tr>
    <tr>
        <td style="text-align:right;">
            <p style="text-align:center;">
                <br><br>
                Very truly yours,
            </p>
            <p>
                <br>
                ____________________________________<br>
                <b>ENGR. REYNALDO P. RAMOS, Ph.D., EnP</b> <br>
                President
            </p>
        </td>
    </tr>
    <tr>
        <td width="50%">Conforme: <br>
            <p style="text-align: center;">
                _______________________________________ <br>
                <b>Signature over printed name of the supplier</b>
            </p>
        </td>
        <td width="50%"><br><br>
            <p style="text-align: center;">
                ________________________<br>
                <b>Date</b>
            </p>
        </td>
    </tr>
    <br>
    <br>
    <br>
    <br>
    <tr>
        <td width="50%">Funds Available: <br>
            <p style="text-align: center;">
                __________________________________ <br>
                <b>CATALINO A. FORTES, JR.</b>
                <br>
                <br>
                Head, Accounting Office/Authorized Representative
            </p>
        </td>
        <td width="50%"><br><br>
            <p style="text-align: right;">
                ALOBS No.: ________________________
            </p>
            <p style="text-align: right;">
                Amount: ________________________
            </p>
        </td>
    </tr>
</table>
EOD;

// Output the content to the PDF
$pdf->writeHTML($new_html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('purchase_order.pdf', 'D');

ob_end_flush(); //End output buffering.
// Close database connection
$conn->close();
?>
