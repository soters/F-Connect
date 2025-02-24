<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']); // Ensure it's an integer

    // Start transaction
    sqlsrv_begin_transaction($conn);

    // Delete the location record
    $sql = "DELETE FROM locations WHERE room_id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($room_id));

    if ($stmt) {
        sqlsrv_commit($conn); // Commit transaction
        $message = "Location deleted successfully!";
        $type = "success";
    } else {
        sqlsrv_rollback($conn); // Rollback transaction if failed
        $message = "Error deleting location: " . print_r(sqlsrv_errors(), true);
        $type = "error";
    }
} else {
    $message = "No room ID provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-locations.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>
