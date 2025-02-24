<?php
include('../../connection/connection.php'); // Database connection
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_code = trim($_POST['subject_code']);
    $subject_description = trim($_POST['subject_description']);
    $for_year = trim($_POST['for_year']);

    if (empty($subject_code) || empty($subject_description)) {
        $message = "Subject Code and Description are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{2,50}$/", $subject_code)) {
        $message = "Subject Code must be between 2 and 50 characters and contain only letters, numbers, and spaces.";
        $type = "error";
    } elseif (strlen($subject_description) < 5 || strlen($subject_description) > 500) {
        $message = "Subject Description must be between 5 and 500 characters.";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\s.,()\-]+$/", $subject_description)) {
        $message = "Subject Description contains invalid characters.";
        $type = "error";
    } else {
        // Check if subject code already exists
        $check_sql = "SELECT COUNT(*) AS count FROM Subjects WHERE subject_code = ?";
        $check_params = [$subject_code];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
        $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

        if ($row['count'] > 0) {
            $message = "A subject with this code already exists.";
            $type = "error";
        } else {
            // Insert new subject
            $sql = "INSERT INTO Subjects (subject_code, subject_description, for_year, date_created) 
                    VALUES (?, ?, ?, GETDATE())";
            $params = [$subject_code, $subject_description, $for_year];
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                sqlsrv_rollback($conn); // Rollback transaction
                $message = "Failed to insert subject: " . print_r(sqlsrv_errors(), true);
                $type = "error";
            } else {
                $message = "Subject added successfully.";
                $type = "success";
            }
        }
    }

    // Redirect with message in URL
    header("Location: ../pages/admin-subjects.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-subjects.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
