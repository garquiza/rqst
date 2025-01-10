<?php
// Include database connection
require_once '../config/database.php';

// Include PhpSpreadsheet classes
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Start by checking for output before headers
ob_start();  // Buffer the output

// Get the PPMP ID from the URL
if (!isset($_GET['ppmp_id'])) {
    die("No PPMP ID provided.");
}

$ppmp_id = intval($_GET['ppmp_id']);

// Fetch PPMP data from the database
$query = "SELECT ppmp_id, project_title, approver, date_created, status 
          FROM ppmp_list 
          WHERE ppmp_id = $ppmp_id";
$result = mysqli_query($conn, $query);

// Check if there is data for the given PPMP ID
if (!$result || mysqli_num_rows($result) == 0) {
    die("No data found for the given PPMP ID.");
}

// Fetch the PPMP data
$data = mysqli_fetch_assoc($result);

// Fetch data from ppmp_form table based on the ppmp_id
$formQuery = "SELECT ppmp_form_id, year, code, general_description, quantity_size, estimated_budget, schedule, date_created
              FROM ppmp_form 
              WHERE ppmp_id = $ppmp_id";
$formResult = mysqli_query($conn, $formQuery);

// Check if there is data for the given PPMP ID in ppmp_form table
if (!$formResult || mysqli_num_rows($formResult) == 0) {
    die("No data found in the ppmp_form table for the given PPMP ID.");
}

// Fetch the data from ppmp_form
$ppmpForms = [];
while ($formData = mysqli_fetch_assoc($formResult)) {
    $ppmpForms[] = $formData;  // Store the ppmp_form data in an array for later use
}

// Load the Excel template from assets folder
$templateFile = '../../../assets/excel_files/PPMP.xlsx';
if (!file_exists($templateFile)) {
    die("Template file not found.");
}

$spreadsheet = IOFactory::load($templateFile);
$sheet = $spreadsheet->getActiveSheet();

// Example: Merge the cells B14, C14, D14 and set the project title
$sheet->mergeCells('B14:D14');
$projectTitle = $data['project_title'];
$sheet->setCellValue('B14', $projectTitle);

// Check if there's any ppmp_form data and insert the first quantity_size in E14, estimated_budget in G14, and year in A14
if (count($ppmpForms) > 0) {
    $firstForm = $ppmpForms[0];  // Get the first form data
    $quantitySize = $firstForm['quantity_size'];  // Get the quantity_size value
    $estimatedBudget = $firstForm['estimated_budget'];  // Get the estimated_budget value
    $year = $firstForm['year'];  // Get the year value
    $dateCreated = $firstForm['date_created'];  // Get the date_created value

    // Insert the year into cell A14
    $sheet->setCellValue('A14', $year);

    // Insert the quantity_size into cell E14
    $sheet->setCellValue('E14', $quantitySize);

    // Insert the estimated_budget into cell G14
    $sheet->setCellValue('G14', $estimatedBudget);

    // Insert the estimated_budget again in H14 (as per your code)
    $sheet->setCellValue('H14', $estimatedBudget);

    // Insert the date_created into cell H4
    $sheet->setCellValue('H4', $dateCreated);
}

$sheet->getStyle('H4')->getAlignment()->setWrapText(true);  // Enable text wrapping for H4
$sheet->getRowDimension('4')->setRowHeight(25);  // Adjust row height as needed (e.g., 40)
$sheet->setCellValue('E14', '');  // Clears the content in E14


// Generate Excel file for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="PPMP_' . $ppmp_id . '.xlsx"');
header('Cache-Control: max-age=0');  // Disable caching
header('Cache-Control: max-age=1');  // For compatibility with older browsers

// Save the file directly to the output buffer
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// End the script to ensure no extra output is generated
exit();
?>
