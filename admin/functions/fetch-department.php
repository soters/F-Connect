<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Query the Department table
$sql = "SELECT dept_id, department_name FROM Department";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Generate options for the select box
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<option value="' . $row['dept_id'] . '">' . htmlspecialchars($row['department_name']) . '</option>';
}

// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
