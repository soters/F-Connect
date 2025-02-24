<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $section_name = trim($_POST['section_name']);
    $dept_id = intval($_POST['dept_id']);

    if (empty($section_name) || empty($dept_id)) {
        $message = "All fields are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{3,255}$/", $section_name)) {
        $message = "Section name must be between 3 and 255 characters and contain only letters, numbers, and spaces.";
        $type = "error";
    } else {
        // Check if section name already exists
        $check_sql = "SELECT COUNT(*) AS count FROM Sections WHERE section_name = ? AND dept_id = ?";
        $check_params = [$section_name, $dept_id];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
        $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

        if ($row['count'] > 0) {
            $message = "A section with this name already exists in the selected department.";
            $type = "error";
        } else {
            // Prepare the SQL query
            $sql = "INSERT INTO Sections (section_name, dept_id) VALUES (?, ?)";
            $params = [$section_name, $dept_id];
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                sqlsrv_rollback($conn); // Rollback transaction
                $message = "Failed to insert data: " . print_r(sqlsrv_errors(), true);
                $type = "error";
            } else {
                $message = "Section added successfully.";
                $type = "success";
            }
        }
    }
    
    // Redirect with message in URL
    header("Location: ../pages/admin-sections.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-sections.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
