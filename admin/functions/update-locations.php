<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id'])) {
    $room_id = intval($_POST['room_id']);
    $room_name = trim($_POST['room_name']);
    $floor = intval($_POST['floor']);
    $type = trim($_POST['type']);
    $x_coord = trim($_POST['x_coord']);
    $y_coord = trim($_POST['y_coord']);
    $bldg_id = 1; // Default building ID

    // Validation
    if (empty($room_name) || empty($floor) || empty($type) || empty($x_coord) || empty($y_coord)) {
        $message = "All fields are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{3,255}$/", $room_name)) {
        $message = "Location name must be between 3 and 255 characters and contain only letters, numbers, and spaces.";
        $type = "error";
    } elseif (!in_array($type, ['Facility', 'Room', 'Office', 'Restroom'])) {
        $message = "Invalid location type selected!";
        $type = "error";
    } elseif (!ctype_digit($x_coord) || !ctype_digit($y_coord)) {
        $message = "Coordinates must be valid numbers!";
        $type = "error";
    } else {
        // Start Transaction
        sqlsrv_begin_transaction($conn);

        // Check if location exists
        $check_sql = "SELECT COUNT(*) AS count FROM locations WHERE room_name = ? AND floor = ? AND type = ? AND bldg_id = ? AND room_id <> ?";
        $check_params = [$room_name, $floor, $type, $bldg_id, $room_id];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
        $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

        if ($row['count'] > 0) {
            $message = "A location with this name already exists on this floor.";
            $type = "error";
            sqlsrv_rollback($conn); // Rollback transaction
        } else {
            // Update database
            $sql = "UPDATE locations SET room_name = ?, floor = ?, type = ?, x_coord = ?, y_coord = ?, bldg_id = ?, date_created = GETDATE() WHERE room_id = ?";
            $params = [$room_name, $floor, $type, $x_coord, $y_coord, $bldg_id, $room_id];
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                sqlsrv_rollback($conn);
                $message = "Failed to update data: " . print_r(sqlsrv_errors(), true);
                $type = "error";
            } else {
                sqlsrv_commit($conn); // Commit transaction
                $message = "Location updated successfully.";
                $type = "success";
            }
        }
    }

    // Redirect with message
    header("Location: ../pages/admin-locations.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {    
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-locations.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
