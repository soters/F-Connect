<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php'); // Make sure this file includes your SQL Server connection

// Check if the appointment_code is passed via GET request
if (isset($_GET['appointment_code'])) {
    $appointment_code = $_GET['appointment_code'];

    // Prepare the SQL query to update the appointment status to "Cancelled"
    $query = "UPDATE appointments SET status = 'Cancelled' WHERE appointment_code = ?";

    // Prepare and execute the query
    $stmt = sqlsrv_query($conn, $query, array($appointment_code));

    if ($stmt === false) {
        // If there is an error with the query
        $_SESSION['error_message'] = "Failed to cancel the appointment. Please try again.";
        header("Location: ../student/kiosk-cancel-appt.php?appointment_code=" . urlencode($appointment_code));
        exit();
    } else {
        // If the query was successful
        $_SESSION['success_message'] = "Your appointment has been successfully cancelled.";
        header("Location: ../student/kiosk-student.php");
        exit();
    }
} else {
    // If no appointment_code is provided, redirect to the main page
    $_SESSION['error_message'] = "No appointment code found.";
    header("Location: kiosk-student.php");
    exit();
}
?>
