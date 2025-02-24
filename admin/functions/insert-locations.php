<?php
include('../../connection/connection.php'); // Database connection
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and trim POST data
    $room_name = trim($_POST['room_name']);
    $floor = trim($_POST['floor']);
    $location_type = trim($_POST['type']); // Renamed variable to avoid conflict with PHP's type keyword
    $x_coord = trim($_POST['x_coord']);
    $y_coord = trim($_POST['y_coord']);

    // --- Validation ---
    if (empty($room_name) || empty($floor) || empty($location_type) || empty($x_coord) || empty($y_coord)) {
        $message = "All fields are required!";
        $type = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9\- ]{2,255}$/", $room_name)) {
        $message = "Location Name must be between 2 and 255 characters and contain only letters, numbers, dashes, and spaces.";
        $type = "error";
    } elseif (!in_array($floor, ['1', '2'])) {
        $message = "Invalid floor selection.";
        $type = "error";
    } elseif (!in_array($location_type, ['Facility', 'Room', 'Office', 'Restroom'])) {
        $message = "Invalid location type.";
        $type = "error";
    } elseif (!ctype_digit($x_coord)) {
        $message = "X Coordinate must be a number.";
        $type = "error";
    } elseif (!ctype_digit($y_coord)) {
        $message = "Y Coordinate must be a number.";
        $type = "error";
    } else {
        // --- Duplicate Check: Check if a location with the same name already exists ---
        $check_sql = "SELECT COUNT(*) AS count FROM Locations WHERE room_name = ?";
        $check_params = [$room_name];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);
        if ($check_stmt === false) {
            $message = "Error checking for duplicate location: " . print_r(sqlsrv_errors(), true);
            $type = "error";
        } else {
            $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
            if ($row['count'] > 0) {
                $message = "A location with this name already exists.";
                $type = "error";
            } else {
                // --- Insert new location ---
                // Fixed bldg_id as 1 and use GETDATE() for date_created
                $bldg_id = 1;
                $insert_sql = "INSERT INTO Locations (room_name, floor, type, x_coord, y_coord, bldg_id, date_created)
                               VALUES (?, ?, ?, ?, ?, ?, GETDATE())";
                $params = [$room_name, $floor, $location_type, $x_coord, $y_coord, $bldg_id];
                $stmt = sqlsrv_query($conn, $insert_sql, $params);

                if ($stmt === false) {
                    // Note: sqlsrv_rollback only works if a transaction was started.
                    sqlsrv_rollback($conn); // Rollback transaction if applicable
                    $message = "Failed to insert location: " . print_r(sqlsrv_errors(), true);
                    $type = "error";
                } else {
                    $message = "Location added successfully.";
                    $type = "success";
                }
            }
        }
    }

    // Redirect with message and type as URL parameters
    header("Location: ../pages/admin-locations.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
} else {
    $message = "Invalid request method!";
    $type = "error";
    header("Location: ../pages/admin-locations.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
