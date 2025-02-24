<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;
    $section_name = trim($_POST['section_name']);
    $dept_id = intval($_POST['dept_id']);

    if (empty($section_id) || empty($section_name) || empty($dept_id)) {
        $message = "All fields are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{3,255}$/", $section_name)) {
        $message = "Section name must be between 3 and 255 characters and contain only letters, numbers, and spaces.";
        $type = "error";
    } else {
        // Check if the section name already exists in the department (excluding the current section)
        $check_sql = "SELECT COUNT(*) AS count FROM Sections WHERE section_name = ? AND dept_id = ? AND section_id != ?";
        $check_params = [$section_name, $dept_id, $section_id];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
        
        if ($check_stmt === false) {
            $message = "Database error: " . print_r(sqlsrv_errors(), true);
            $type = "error";
        } else {
            $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
            
            if ($row['count'] > 0) {
                $message = "A section with this name already exists in the selected department.";
                $type = "error";
            } else {
                // Update the section in the database
                $sql = "UPDATE Sections SET section_name = ?, dept_id = ? WHERE section_id = ?";
                $params = [$section_name, $dept_id, $section_id];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    sqlsrv_rollback($conn); // Rollback transaction
                    $message = "Failed to update section: " . print_r(sqlsrv_errors(), true);
                    $type = "error";
                } else {
                    $message = "Section updated successfully.";
                    $type = "success";
                }
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
