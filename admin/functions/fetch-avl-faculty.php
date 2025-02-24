<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

$today = date('Y-m-d');

// Query to select faculty members who have time_in but no time_out for today
$sql = "
    SELECT f.rfid_no, f.fname, f.mname, f.lname, f.suffix 
    FROM Faculty f
    INNER JOIN AttendanceToday a ON f.rfid_no = a.rfid_no
    WHERE f.archived = 0 
    AND a.date_logged = ? 
    AND a.time_in IS NOT NULL 
    AND a.time_out IS NULL
";
$params = array($today);
$stmt = sqlsrv_query($conn, $sql, $params);

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
