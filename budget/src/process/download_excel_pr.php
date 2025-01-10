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

if (isset($_GET['pr_id'])) {
    $pr_id = $_GET['pr_id'];
} else {
    die("Error: PR ID not specified.");
}

$query = "
    SELECT pr.pr_id, pr.pr_number, pr.approver, pr.submitted_date, pr.status, 
           pri.department, pri.section, 
           pri.inventory_id, pri.quantity, pri.unit_cost AS pri_unit_cost, 
           pri.total_cost, pri.purpose, 
           inv.unit AS inventory_unit, inv.item_name,
           CONCAT(e.first_name, ' ', e.last_name) AS end_user_name
    FROM purchase_requests pr
    LEFT JOIN purchase_request_items pri ON pr.pr_id = pri.pr_id
    LEFT JOIN inventory inv ON pri.inventory_id = inv.inventory_id
    LEFT JOIN end_users e ON pr.end_user_id = e.id  -- Join with the end_users table using 'id' from end_users
    WHERE pr.pr_id = ?";

$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $pr_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($result)) {
    die("Error: No data found for the selected PR.");
}

$pdo = null;

$templateFile = '../../../assets/excel_files/PURCHASE-REQUEST.xlsx';
$spreadsheet = IOFactory::load($templateFile);
$sheet = $spreadsheet->getActiveSheet();

if (isset($result[0])) {
    $sections = array_column($result, 'section');

    $sections = array_filter($sections, function ($section) {
        return !is_numeric($section) && !empty($section);
    });

    $section = implode(", ", array_unique($sections));

    $pr_number = $result[0]['pr_number'];
    $department = $result[0]['department'];
    $purpose = $result[0]['purpose'];
    $approver = strtoupper($result[0]['approver']);
    $total_cost = $result[0]['total_cost'];
    $end_user_name = strtoupper($result[0]['end_user_name']);

    $sheet->setCellValue('C7', $department);

    $sheet->setCellValue('C8', $section);

    $sheet->setCellValue('E7', $pr_number);

    $quantity = $result[0]['quantity'];
    $unit_cost = $result[0]['pri_unit_cost'];
    $total_cost = $result[0]['total_cost'];
    $unit = $result[0]['inventory_unit'];
    $item_name = $result[0]['item_name'];

    $sheet->setCellValue('D11', $quantity);
    $sheet->setCellValue('E11', $unit_cost);
    $sheet->setCellValue('F11', $total_cost);

    $sheet->setCellValue('B11', $unit);
    $sheet->setCellValue('C11', $item_name);

    $sheet->mergeCells('C39:C40');
    $sheet->setCellValue('C39', $purpose);

    $sheet->mergeCells('D44:F44');
    $sheet->setCellValue('D44', $approver);

    $sheet->setCellValue('F3', date('Y-m-d'));
    $sheet->getStyle('F3')->getNumberFormat()->setFormatCode('yyyy-mm-dd');


    $sheet->setCellValue('F38', $total_cost);

    $sheet->setCellValue('C44', $end_user_name);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="PurchaseRequest_' . $pr_id . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit();
