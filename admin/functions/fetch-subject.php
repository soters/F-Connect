<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Query the Section table
$sql = "SELECT subject_code, subject_description FROM Subjects";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Generate options for the select box
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<option value="' . $row['subject_code'] . '">' . htmlspecialchars($row['subject_description']) . '</option>';
}

// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
