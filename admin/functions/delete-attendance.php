<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['attd_ref']) && isset($_GET['rfid_no'])) {
    $attdRef = $_GET['attd_ref'];
    $rfidNo = $_GET['rfid_no'];

    // Delete the attendance record
    $sql = "DELETE FROM AttendanceRecords WHERE attd_ref = ? AND rfid_no = ?";
    $stmt = sqlsrv_query($conn, $sql, array($attdRef, $rfidNo));

    if ($stmt) {
        $message = "Attendance record deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting attendance record!";
        $type = "error";
    }
} else {
    $message = "Invalid attendance reference!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-attendance-records.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
