<?php
// Include the connection file
include('../../connection/connection.php'); // Adjust the path to your connection.php file

function resetPassword($rfid_no) {
    global $conn; // Using the connection from connection.php

    // Define the new password
    $new_password = password_hash("12345", PASSWORD_DEFAULT); // Hashing for security

    // Begin a transaction
    sqlsrv_begin_transaction($conn);

    try {
        // Step 1: Select email from Faculty table using RFID number
        $query_select = "SELECT email FROM Admin WHERE rfid_no = ?";
        $params_select = [$rfid_no];
        $stmt_select = sqlsrv_query($conn, $query_select, $params_select);

        if ($stmt_select === false || !sqlsrv_has_rows($stmt_select)) {
            throw new Exception("No admin found with the provided RFID number.");
        }

        // Fetch the email
        $row = sqlsrv_fetch_array($stmt_select, SQLSRV_FETCH_ASSOC);
        $email = $row['email'];

        // Step 2: Update the password in FacultyAccount table
        $query_update = "UPDATE AdminAccount SET password = ? WHERE email = ?";
        $params_update = [$new_password, $email];
        $stmt_update = sqlsrv_query($conn, $query_update, $params_update);

        if ($stmt_update === false) {
            throw new Exception("Error updating password: " . print_r(sqlsrv_errors(), true));
        }

        // Commit the transaction
        sqlsrv_commit($conn);

        // Set success message
        $message = "Password reset successfully.";
        $type = "success";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        sqlsrv_rollback($conn);

        // Set error message
        $message = "Failed to reset password: " . $e->getMessage();
        $type = "error";
    }

    // Redirect with message in URL
    header("Location: ../pages/admin-update-admin.php?rfid_no=" . urlencode($rfid_no) . "&message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}

// Call the function if an RFID number is provided
if (isset($_GET['rfid_no'])) {
    resetPassword($_GET['rfid_no']);
}
?>
