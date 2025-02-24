<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['subject_code'])) {
    $subject_code = $_GET['subject_code'];

    // Delete the subject record
    $sql = "DELETE FROM Subjects WHERE subject_code = ?";
    $stmt = sqlsrv_query($conn, $sql, array($subject_code));

    if ($stmt) {
        $message = "Subject deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting subject!";
        $type = "error";
    }
} else {
    $message = "No subject code provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-subjects.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
