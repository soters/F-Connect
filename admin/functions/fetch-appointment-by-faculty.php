<?php
include('../../connection/connection.php'); // Ensure this sets up SQLSRV connection properly
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['faculty']) || empty($_GET['faculty'])) {
    die(json_encode(["error" => "Invalid or missing faculty_rfid parameter."]));
}

$facultyRFID = $_GET['faculty'];

// Fetch faculty details
$facultySql = "SELECT fname, lname FROM Faculty WHERE rfid_no = ?";
$facultyStmt = sqlsrv_query($conn, $facultySql, [$facultyRFID]);

if ($facultyStmt === false || !($faculty = sqlsrv_fetch_array($facultyStmt, SQLSRV_FETCH_ASSOC))) {
    die(json_encode(["error" => "Faculty not found."]));
}

// Fetch faculty appointments, sorted by `date_logged` (oldest to latest)
$sql = "SELECT a.appointment_code, s.fname AS stud_fname, s.lname AS stud_lname, 
               a.start_time, a.end_time, a.agenda, a.status, a.date_logged
        FROM Appointments a
        JOIN Students s ON a.stud_rfid_no = s.rfid_no
        WHERE a.prof_rfid_no = ?
        ORDER BY a.date_logged ASC";  // Sort by oldest to latest

$stmt = sqlsrv_query($conn, $sql, [$facultyRFID]);

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
    // Convert TIME fields to string format
    $row['start_time'] = $row['start_time']->format('H:i:s');
    $row['end_time'] = $row['end_time']->format('H:i:s');

    // Convert DATE_LOGGED to string
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
    "faculty" => $faculty,
    "appointments" => $appointments,
    "statusCounts" => $statusCounts,
    "agendaCounts" => $agendaCounts
];

echo json_encode($response);

// Free up resources
sqlsrv_free_stmt($facultyStmt);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
