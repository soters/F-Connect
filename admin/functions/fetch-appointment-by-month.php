<?php
include('../../connection/connection.php'); // Ensure this sets up SQLSRV connection properly
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['month']) || empty($_GET['month'])) {
    die(json_encode(["error" => "Month parameter is required."]));
}

$month = $_GET['month']; // Expected format: YYYY-MM (e.g., 2025-01)

// Fetch appointments for the given month
$sql = "SELECT a.appointment_code, f.fname, f.lname, 
               s.fname AS stud_fname, s.lname AS stud_lname, 
               a.start_time, a.end_time, a.agenda, a.status, a.date_logged
        FROM Appointments a
        JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
        JOIN Students s ON a.stud_rfid_no = s.rfid_no
        WHERE FORMAT(a.date_logged, 'yyyy-MM') = ?
        ORDER BY a.date_logged ASC";
 // Oldest to latest

$stmt = sqlsrv_query($conn, $sql, [$month]);

if ($stmt === false) {
    die(json_encode(["error" => "Query failed.", "details" => sqlsrv_errors()]));
}

$appointments = [];
$statusCounts = ["Completed" => 0, "Cancelled" => 0, "Declined" => 0, "Accepted" => 0, "Pending" => 0];
$agendaCounts = [
    "Internship or Practical Experience Advice" => 0,
    "Personal Academic Concerns" => 0,
    "Project/Research Discussion" => 0,
    "Mentorship" => 0
];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['start_time'] = $row['start_time']->format('H:i:s');
    $row['end_time'] = $row['end_time']->format('H:i:s');
    $row['date_logged'] = $row['date_logged']->format('Y-m-d'); // Show only the date

    // Count Status
    if (isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']]++;
    }

    // Count Agenda
    if (isset($agendaCounts[$row['agenda']])) {
        $agendaCounts[$row['agenda']]++;
    }

    $appointments[] = $row;
}

// Response
$response = [
    "appointments" => $appointments,
    "statusCounts" => $statusCounts,
    "agendaCounts" => $agendaCounts
];

echo json_encode($response);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>