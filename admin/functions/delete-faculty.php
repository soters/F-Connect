<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['rfid_no'])) {
    $rfid = $_GET['rfid_no'];

    // Delete the faculty record
    $sql = "DELETE FROM Faculty WHERE rfid_no = ?";
    $stmt = sqlsrv_query($conn, $sql, array($rfid));

    if ($stmt) {
        $message = "Faculty member deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting faculty member!";
        $type = "error";
    }
} else {
    $message = "No RFID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-faculty.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
