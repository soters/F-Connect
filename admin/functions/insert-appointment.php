<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Function to redirect with a message and type
function redirectWithMessage($message, $type, $redirectPage) {
    header("Location: $redirectPage?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Database connection using SQLSRV
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Generate a 12-digit random number for appointment_code
        $appointment_code = str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);

        // Retrieve form data
        $prof_rfid_no = $_POST['prof_rfid_no'];
        $stud_rfid_no = $_POST['stud_rfid_no'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $agenda = $_POST['agenda'];
        $status = "Pending"; // New appointments are initially pending
        $date_logged = date("Y-m-d");

        // -------------------------------
        // New Validation: Ensure start_time is not in the past
        // -------------------------------
        // Combine today's date with the provided start_time to create a full datetime stamp
        $appointmentStartTimestamp = strtotime($date_logged . ' ' . $start_time);
        if ($appointmentStartTimestamp < time()) {
            redirectWithMessage("The appointment start time has already passed. Please select a future time.", "error", "../pages/admin-new-appointment.php");
        }

        // -------------------------------
        // 1. Check for overlapping appointment (Accepted status)
        // -------------------------------
        // Overlap logic: new_start < existing_end AND new_end > existing_start
        $checkSql = "SELECT appointment_code 
                     FROM Appointments 
                     WHERE prof_rfid_no = ? 
                       AND date_logged = ? 
                       AND status = 'Accepted'
                       AND (? < end_time AND ? > start_time)";
        $checkParams = array($prof_rfid_no, $date_logged, $start_time, $end_time);
        $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
        if ($checkStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        if (sqlsrv_has_rows($checkStmt)) {
            redirectWithMessage("This professor already has an accepted appointment scheduled during that time.", "error", "../pages/admin-new-appointment.php");
        }

        // -------------------------------
        // 2. Check for overlapping schedule in Schedules table
        // -------------------------------
        // Check rows where the schedule's start_date equals the current date
        // and the time overlaps with the selected appointment times.
        $checkScheduleSql = "SELECT sched_id 
                             FROM Schedules 
                             WHERE rfid_no = ? 
                               AND start_date = ? 
                               AND (? < end_time AND ? > start_time)";
        $checkScheduleParams = array($prof_rfid_no, $date_logged, $start_time, $end_time);
        $checkScheduleStmt = sqlsrv_query($conn, $checkScheduleSql, $checkScheduleParams);
        if ($checkScheduleStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        if (sqlsrv_has_rows($checkScheduleStmt)) {
            redirectWithMessage("This professor has a scheduled class or activity that overlaps with the selected time.", "error", "../pages/admin-new-appointment.php");
        }

        // -------------------------------
        // 3. Insert new appointment if all checks pass
        // -------------------------------
        $sql = "INSERT INTO Appointments (appointment_code, prof_rfid_no, stud_rfid_no, start_time, end_time, agenda, status, date_logged) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array($appointment_code, $prof_rfid_no, $stud_rfid_no, $start_time, $end_time, $agenda, $status, $date_logged);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            // Success message
            redirectWithMessage("Appointment successfully created!", "success", "../pages/admin-new-appointment.php");
        } else {
            // Error message
            redirectWithMessage("Error creating appointment. Please try again.", "error", "../pages/admin-new-appointment.php");
        }
    } catch (Exception $e) {
        // Exception handling and database error message
        redirectWithMessage("Database error: " . $e->getMessage(), "error", "../pages/admin-new-appointment.php");
    }

    // Close connection
    sqlsrv_close($conn);
}
?>
