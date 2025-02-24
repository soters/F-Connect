<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['announcement_id'])) {
    $announcement_id = $_GET['announcement_id'];

    // Delete the faculty record
    $sql = "DELETE FROM Announcement WHERE announcement_id = ?";
    $stmt = sqlsrv_query($conn, $sql, params: array($announcement_id));

    if ($stmt) {
        $message = "Announcement deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting announcement!";
        $type = "error";
    }
} else {
    $message = "No Announcement ID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-announcement.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
