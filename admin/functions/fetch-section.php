<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Query the Section table
$sql = "SELECT section_id, section_name FROM Sections";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Generate options for the select box
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<option value="' . $row['section_id'] . '">' . htmlspecialchars($row['section_name']) . '</option>';
}

// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
