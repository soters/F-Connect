<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['rfid_no'])) {
    $rfid = $_GET['rfid_no'];

    // Delete the faculty record
    $sql = "DELETE FROM Admin WHERE rfid_no = ?";
    $stmt = sqlsrv_query($conn, $sql, array($rfid));

    if ($stmt) {
        $message = "Admin account deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting admin!";
        $type = "error";
    }
} else {
    $message = "No RFID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-manage.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
