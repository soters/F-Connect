<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['rfid_no'])) {
    $rfid = $_GET['rfid_no'];

    // Delete the faculty record
    $sql = "DELETE FROM Students WHERE rfid_no = ?";
    $stmt = sqlsrv_query($conn, $sql, array($rfid));

    if ($stmt) {
        $message = "Student member deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting student member!";
        $type = "error";
    }
} else {
    $message = "No RFID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
