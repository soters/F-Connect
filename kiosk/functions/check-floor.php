<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');

// Get RFID number and room_id from the POST request
$rfid_no = filter_input(INPUT_POST, 'rfid_no', FILTER_SANITIZE_STRING);
$room_id = filter_input(INPUT_POST, 'room_id', FILTER_SANITIZE_NUMBER_INT);

// Query to fetch the floor number from Locations table based on room_id
$locationQuery = "
    SELECT floor
    FROM Locations
    WHERE room_id = ?";
$locationParams = [$room_id];
$locationStmt = sqlsrv_query($conn, $locationQuery, $locationParams);

if ($locationStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$floor = null;
if ($row = sqlsrv_fetch_array($locationStmt, SQLSRV_FETCH_ASSOC)) {
    $floor = $row['floor'];
}

// Free statement resources
sqlsrv_free_stmt($locationStmt);

// Redirect based on the floor number
if ($floor === 1) {
    // Redirect to map_level_1.php
    header("Location: ../map/map_level_1.php?rfid_no=$rfid_no&room_id=$room_id");
    exit();
} elseif ($floor === 2) {
    // Redirect to map_level_2.php
    header("Location: ../map/map_level_2.php?rfid_no=$rfid_no&room_id=$room_id");
    exit();
} else {
    // If the floor is not 1 or 2, handle accordingly (e.g., error page)
    echo "Invalid floor number or room not found.";
    exit();
}
?>
