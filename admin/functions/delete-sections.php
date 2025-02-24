<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];

    // Delete the faculty record
    $sql = "DELETE FROM Sections WHERE section_id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($section_id));

    if ($stmt) {
        $message = "Sections deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting section!";
        $type = "error";
    }
} else {
    $message = "No RFID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-sections.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
