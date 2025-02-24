<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Query the Faculty table
$sql = "SELECT rfid_no, fname, mname, lname, suffix FROM Students WHERE archived = 0";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Generate options for the select box
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Build the full name (including middle name and suffix if they exist)
    $fullName = $row['fname'];
    if (!empty($row['mname'])) {
        $fullName .= ' ' . $row['mname'];
    }
    $fullName .= ' ' . $row['lname'];
    if (!empty($row['suffix'])) {
        $fullName .= ' ' . $row['suffix'];
    }

    // Echo the option element
    echo '<option value="' . htmlspecialchars($row['rfid_no']) . '">' . htmlspecialchars($fullName) . '</option>';
}

// Free the statement and close the connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
