<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject_code'])) {
    $subject_code = trim($_POST['subject_code']);
    $subject_description = trim($_POST['subject_description']);

    // Validation
    if (empty($subject_code) || empty($subject_description)) {
        $message = "All fields are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{3,255}$/", $subject_description)) {
        $message = "Subject description must be between 3 and 255 characters and contain only letters, numbers, and dashes.";
        $type = "error";
    } else {
        // Start transaction
        sqlsrv_begin_transaction($conn);

        // Check if the subject description already exists for a different subject_code
        $check_sql = "SELECT COUNT(*) AS count FROM Subjects WHERE subject_description = ? AND subject_code <> ?";
        $check_params = [$subject_description, $subject_code];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);

        if ($check_stmt === false) {
            $message = "Database error: " . print_r(sqlsrv_errors(), true);
            $type = "error";
            sqlsrv_rollback($conn); // Rollback transaction
        } else {
            $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

            if ($row['count'] > 0) {
                $message = "A subject with this description already exists.";
                $type = "error";
                sqlsrv_rollback($conn);
            } else {
                // Update the subject using subject_code
                $sql = "UPDATE Subjects SET subject_description = ?, date_created = GETDATE() WHERE subject_code = ?";
                $params = [$subject_description, $subject_code];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    sqlsrv_rollback($conn);
                    $message = "Failed to update data: " . print_r(sqlsrv_errors(), true);
                    $type = "error";
                } else {
                    sqlsrv_commit($conn); // Commit transaction
                    $message = "Subject updated successfully.";
                    $type = "success";
                }
            }
        }
    }

    // Redirect with message
    header("Location: ../pages/admin-subjects.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-subjects.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
