<?php

require_once('../../vendor/autoload.php');
require_once('../config/database.php');

ob_start(); // Start output buffering to capture any accidental output

//Only execute this if a NOA ID is selected. This prevents errors when no selection is made.
if (isset($_GET['noa_id'])) {
    $noa_id = $_GET['noa_id']; // Get the noa_id from the URL

    // Prepare and execute the SQL query to fetch NOA data
    $sql = "SELECT authorized_representative, designation, company_name, project_title FROM notice_of_award WHERE noa_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $noa_id); // "i" specifies integer type for noa_id
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); //Fetch the first row (assuming only one NOA matches the ID)

        // Set the variables from the database
        $authorizedRepresentative = $row['authorized_representative'];
        $designation = $row['designation'];
        $companyName = $row['company_name'];
        $projectTitle = $row['project_title'];
        $dateToday = date('F d, Y');

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
        $pdf->SetTitle('Notice to Proceed');

        // Set margins and add page
        $pdf->SetMargins(15, 25, 15);
        $pdf->SetHeaderMargin(0);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 12);

        // Add a page
        $pdf->AddPage();
        

        // HTML content using the fetched data
        $html = <<<EOD
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <style>
                body {
                    font-family: helvetica, sans-serif;
                }
                .text-end {
                    text-align: right;
                }
                .fw-bold {
                    font-weight: bold;
                }
                .table {
                    border-collapse: separate;
                    border-spacing: 0;
                    width: 100%;
                    border: 1px solid black;
                }
                .table th,
                .table td {
                    border: 1px solid black;
                    padding: 5px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h2 style="text-align: center; font-weight: bold;">NOTICE TO PROCEED</h2>
            <p class="text-end">$dateToday</p>
            <p>
                $authorizedRepresentative<br>
                $designation<br>
                <strong>$companyName</strong><br>
            </p>
            <p>Sir/Madam:</p>
            <p>
                Please be informed that you are given this <strong>Notice to Proceed</strong> to execute the contract
                for the project <em>"$projectTitle"</em>, a copy of which is hereto attached.
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
            <br>
            <br>
            <div class="signature-section">
                <table class="table" cellpadding="5">
                    <tr>
                        <th colspan="2">Received by:</th>
                    </tr>
                    <tr>
                        <td>
                            <br>
                            <br>
                            _________________________________ <br>
                            (SIGNATURE OVER PRINTED NAME & DATE)
                        </td>
                        <td>
                            <br>
                            <br>
                            ___________________________ <br>
                            NAME OF COMPANY
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <small>(PLEASE RETURN THIS NOTICE TO PROCEED)</small>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        EOD;

        // Add content to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output the PDF
        $pdf->Output('notice_to_proceed.pdf', 'D'); // Or 'D' to download, 'F' to save to server
    } else {
        echo "NOA not found."; // Handle case where no NOA matches the ID.
    }
    $stmt->close(); //Close the statement.
} else {
    echo "Please select a NOA ID.";
}

ob_end_flush(); //End output buffering.
$conn->close(); //Close the database connection.
?>

