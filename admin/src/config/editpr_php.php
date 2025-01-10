<?php

// Fetch purchase request details
if (isset($_GET['pr_no'])) {
    $pr_no = $_GET['pr_no'];

    // Get purchase request details
    $query = "
        SELECT 
            pr.*, 
            au.first_name AS requested_by_first, 
            au.last_name AS requested_by_last 
        FROM purchase_requests pr
        JOIN admin_users au ON pr.admin_id = au.id
        WHERE pr.pr_no = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $pr_no);
    $stmt->execute();
    $purchase_request = $stmt->get_result()->fetch_assoc();

    // Get items and related details
    $items_query = "
        SELECT 
            pri.department, 
            pri.section, 
            pri.purpose, 
            inv.item_no, 
            inv.material AS item_name, 
            inv.unit_price AS unit_cost, 
            pri.quantity, 
            pri.total_cost
        FROM purchase_request_items pri
        JOIN inventory inv ON pri.inventory_id = inv.inventory_id
        WHERE pri.pr_id = ?
    ";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $purchase_request['pr_id']);
    $stmt->execute();
    $items = $stmt->get_result();

    // Get the first row for section and purpose display
    $first_item = $items->fetch_assoc();
} else {
    die("No purchase request specified.");
}

?>