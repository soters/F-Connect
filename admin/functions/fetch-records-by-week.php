<?php
error_reporting(E_ALL);
include('../../connection/connection.php'); // Ensure this sets up SQLSRV connection properly
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['week']) || empty($_GET['week'])) {
    die(json_encode(["error" => "Week parameter is required."]));
}

if (!isset($_GET['year']) || empty($_GET['year'])) {
    die(json_encode(["error" => "Year parameter is required."]));
}

if (!isset($_GET['rfid_no']) || empty($_GET['rfid_no'])) {
    die(json_encode(["error" => "RFID parameter is required."]));
}

$week = $_GET['week'];  // Week number (1-53)
$year = $_GET['year'];  // Year (e.g., 2025)
$rfid_no = $_GET['rfid_no']; // Faculty RFID number

// Fetch attendance records for the given week and faculty RFID
$sql = "SELECT a.attd_ref, a.rfid_no, f.fname, f.lname, 
               a.time_in, a.time_out, a.status, a.date_logged
        FROM AttendanceRecords a
        JOIN Faculty f ON a.rfid_no = f.rfid_no
        WHERE DATEPART(WK, a.date_logged) = ? 
          AND YEAR(a.date_logged) = ? 
          AND a.rfid_no = ?
        ORDER BY a.date_logged ASC"; // Ordered by date

$params = [$week, $year, $rfid_no];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(json_encode(["error" => "Query failed.", "details" => sqlsrv_errors()]));
}

$attendanceRecords = [];
$statusCounts = ["Present" => 0, "Absent" => 0, "Late" => 0];
$grandTotalHours = 0; // Grand total of all computed hours

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['date_logged'] = $row['date_logged']->format('Y-m-d'); // Format date

    // Ensure time values are properly formatted as strings
    $row['time_in'] = isset($row['time_in']) ? $row['time_in']->format('H:i:s') : null;
    $row['time_out'] = isset($row['time_out']) ? $row['time_out']->format('H:i:s') : null;

    // Calculate total hours
    $totalHours = 0;
    if ($row['time_in'] !== null && $row['time_out'] !== null) {
        $timeIn = strtotime($row['time_in']);
        $timeOut = strtotime($row['time_out']);
        $totalHours = ($timeOut - $timeIn) / 3600; // Convert seconds to hours
    }

    $row['total_hours'] = number_format($totalHours, 2); // Format to 2 decimal places
    $grandTotalHours += $totalHours; // Add to grand total

    // Count Status
    if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']]++;
    }

    $attendanceRecords[] = $row;
}

// Response
$response = [
    "attendanceRecords" => $attendanceRecords,
    "statusCounts" => $statusCounts,
    "grandTotalHours" => number_format($grandTotalHours, 2) // Format to 2 decimal places
];

echo json_encode($response);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
