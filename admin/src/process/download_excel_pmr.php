<?php
// Include database connection
require_once '../config/database.php';

// Include PhpSpreadsheet classes
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Specify the file path
$filePath = '../../../assets/excel_files/PMR.xlsx';

// Check if the file exists
if (!file_exists($filePath)) {
    die("The file does not exist at the specified path.");
}

try {
    // Attempt to load the existing Excel file (PMR.xlsx)
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Generate Excel file for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Procurement_Monitoring_Report.xlsx"');
    header('Cache-Control: max-age=0');  // Disable cachingA
    header('Pragma: public'); // Ensures caching headers are set for some browsers

    // Save the spreadsheet directly to the output buffer
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // End the script to ensure no extra output is generated
    exit();
} catch (Exception $e) {
    die("Error loading the Excel file: " . $e->getMessage());
}
