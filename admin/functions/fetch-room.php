<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Query the Section table
$sql = "SELECT room_id, room_name FROM Locations";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Generate options for the select box
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<option value="' . $row['room_id'] . '">' . htmlspecialchars($row['room_name']) . '</option>';
}

// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
