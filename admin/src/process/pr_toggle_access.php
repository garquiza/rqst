<?php
session_start();

// Check if an action is passed
if (isset($_GET['action'])) {
    // Toggle the access lock based on the action
    if ($_GET['action'] == 'lock') {
        $_SESSION['access_locked'] = true;
    } elseif ($_GET['action'] == 'unlock') {
        $_SESSION['access_locked'] = false;
    }
}

// After toggling, redirect back to the previous page (pr.php)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
