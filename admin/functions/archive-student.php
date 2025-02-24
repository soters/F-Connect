<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['rfid_no'])) {
    $rfid = $_GET['rfid_no'];

    // Update the student record to mark as archived
    $sql = "UPDATE Students SET archived = 1 WHERE rfid_no = ?";
    $stmt = sqlsrv_query($conn, $sql, array($rfid));

    if ($stmt) {
        $message = "Student member archived successfully!";
        $type = "success";
    } else {
        $message = "Error archiving student member!";
        $type = "error";
    }
} else {
    $message = "No RFID provided!";
    $type = "error";
}

// Redirect with message in URL
header("Location: ../pages/admin-student.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
