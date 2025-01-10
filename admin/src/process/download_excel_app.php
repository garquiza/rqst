<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/pdo.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$templateFile = __DIR__ . '/../../../assets/excel_files/APP.xlsx';

if (!file_exists($templateFile)) {
    die("Error: Template file not found at $templateFile");
}

try {
    $spreadsheet = IOFactory::load($templateFile);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die("Error loading file: " . $e->getMessage());
}

$sheet = $spreadsheet->getActiveSheet();

$yearFilter = isset($_GET['year']) ? $_GET['year'] : '';

$query = "SELECT ppmp_form.code, ppmp_list.ppmp_id, ppmp_list.project_title, 
           app.pmo_end_user, app.early_procurement_activity, app.mode_of_procurement, 
           app.advertisement_posting_ib_rei, app.submission_opening_bids, 
           app.notice_of_award, app.contract_signing, app.source_of_funds, 
           app.total, app.mooe, app.co, app.remarks
         FROM ppmp_list
         LEFT JOIN app ON ppmp_list.ppmp_id = app.ppmp_id
         LEFT JOIN ppmp_form ON ppmp_list.ppmp_id = ppmp_form.ppmp_id
         WHERE ppmp_list.status = 'approved'";


if ($yearFilter) {
    $query .= " AND YEAR(ppmp_list.date_created) = :year";
}

$query .= " ORDER BY ppmp_list.ppmp_id ASC";


$stmt = $pdo->prepare($query);
if ($yearFilter) {
    $stmt->bindParam(':year', $yearFilter, PDO::PARAM_INT);
}
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)) {
    die("Error: No data found for the selected year.");
}

$rowNum = 5;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $row['code'])
        ->setCellValue('B' . $rowNum, $row['project_title'])
        ->setCellValue('C' . $rowNum, $row['pmo_end_user'])
        ->setCellValue('D' . $rowNum, $row['early_procurement_activity'])
        ->setCellValue('E' . $rowNum, $row['mode_of_procurement'])
        ->setCellValue('F' . $rowNum, $row['advertisement_posting_ib_rei'])
        ->setCellValue('G' . $rowNum, $row['submission_opening_bids'])
        ->setCellValue('H' . $rowNum, $row['notice_of_award'])
        ->setCellValue('I' . $rowNum, $row['contract_signing'])
        ->setCellValue('J' . $rowNum, $row['source_of_funds'])
        ->setCellValue('K' . $rowNum, $row['total'])
        ->setCellValue('L' . $rowNum, $row['mooe'])
        ->setCellValue('M' . $rowNum, $row['co'])
        ->setCellValue('N' . $rowNum, $row['remarks']);
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="app_table_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
