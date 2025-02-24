<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../../connection/connection.php'); // Ensure SQLSRV connection is properly configured
date_default_timezone_set('Asia/Manila');

if (!isset($_GET['year']) || empty($_GET['year'])) {
    echo json_encode(["error" => "Year parameter is required."]);
    exit;
}

$year = $_GET['year']; // Expected format: YYYY (e.g., 2025)

// Fetch appointments for the given year
$sql = "SELECT a.appointment_code, 
               f.fname AS prof_fname, f.lname AS prof_lname, 
               s.fname AS stud_fname, s.lname AS stud_lname, 
               a.start_time, a.end_time, a.agenda, a.status, 
               FORMAT(a.date_logged, 'yyyy-MM-dd') AS date_logged,
               FORMAT(a.date_logged, 'MMMM') AS month_name  -- Ensure month_name exists
        FROM Appointments a
        JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
        JOIN Students s ON a.stud_rfid_no = s.rfid_no
        WHERE FORMAT(a.date_logged, 'yyyy') = ?
        ORDER BY a.date_logged ASC";  // Oldest to latest

$params = [$year];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(["error" => "Query failed.", "details" => sqlsrv_errors()]);
    exit;
}

$appointments = [];
$monthlyAppointments = [];
$statusCounts = ["Completed" => 0, "Cancelled" => 0, "Declined" => 0, "Accepted" => 0, "Pending" => 0];
$agendaCounts = [
    "Internship or Practical Experience Advice" => 0,
    "Personal Academic Concerns" => 0,
    "Project/Research Discussion" => 0,
    "Mentorship" => 0
];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Format time correctly
    $row['start_time'] = isset($row['start_time']) ? $row['start_time']->format('H:i:s') : null;
    $row['end_time'] = isset($row['end_time']) ? $row['end_time']->format('H:i:s') : null;

    // Count Status
    if (!empty($row['status']) && isset($statusCounts[$row['status']])) {
        $statusCounts[$row['status']]++;
    }

    // Count Agenda
    if (!empty($row['agenda']) && isset($agendaCounts[$row['agenda']])) {
        $agendaCounts[$row['agenda']]++;
    }

    // Group by month
    $month = $row['month_name'] ?? 'Unknown'; // Ensure month_name exists
    unset($row['month_name']); // Remove redundant key from response

    if (!isset($monthlyAppointments[$month])) {
        $monthlyAppointments[$month] = [];
    }
    $monthlyAppointments[$month][] = $row;

    $appointments[] = $row;
}

// Response
$response = [
    "appointments" => $appointments,
    "monthlyAppointments" => $monthlyAppointments,
    "statusCounts" => $statusCounts,
    "agendaCounts" => $agendaCounts
];

echo json_encode($response);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
