<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if (isset($_GET['appointment_code'])) {
    $appointmentCode = $_GET['appointment_code'];

    // Delete the appointment record
    $sql = "DELETE FROM Appointments WHERE appointment_code = ?";
    $stmt = sqlsrv_query($conn, $sql, array($appointmentCode));

    if ($stmt) {
        $message = "Appointment deleted successfully!";
        $type = "success";
    } else {
        $message = "Error deleting appointment!";
        $type = "error";
    }
} else {
    $message = "No appointment code provided!";
    $type = "error";
}

// Redirect with message
header("Location: ../pages/admin-appointment-history.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit();
?>