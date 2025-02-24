<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

$sql = "SELECT rfid_no, fname, mname, lname, suffix FROM Faculty WHERE archived = 0";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("SQL Error: " . print_r(sqlsrv_errors(), true));
}

$options = "";
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $fullName = trim($row['fname'] . ' ' . ($row['mname'] ?? '') . ' ' . $row['lname'] . ' ' . ($row['suffix'] ?? ''));
    $options .= '<option value="' . htmlspecialchars($row['rfid_no']) . '">' . htmlspecialchars($fullName) . '</option>';
}

// Debug output
if (empty($options)) {
    echo "<option value=''>No Faculty Found</option>";
} else {
    echo $options;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
