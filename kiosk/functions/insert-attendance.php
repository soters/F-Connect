<?php
session_start();
require_once('../../connection/connection.php');

// Get the RFID number from the request
$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

if (!$rfid_no) {
    die("RFID number is required.");
}

// Get the current date and time
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Check if the RFID already has a record in AttendanceToday
$check_query = "
    SELECT attd_ref, time_in, time_out
    FROM AttendanceToday
    WHERE rfid_no = ? AND date_logged = ?
";
$check_params = [$rfid_no, $current_date];
$check_stmt = sqlsrv_query($conn, $check_query, $check_params);

if ($check_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$attendance = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

if ($attendance && is_null($attendance['time_out'])) {
    // Faculty has already timed in but not timed out
    // Find the latest end_time for the day
    $end_time_query = "
        SELECT MAX(end_time) AS latest_end_time
        FROM Schedules
        WHERE rfid_no = ? AND start_date = ?
    ";
    $end_time_params = [$rfid_no, $current_date];
    $end_time_stmt = sqlsrv_query($conn, $end_time_query, $end_time_params);

    if ($end_time_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $end_time_data = sqlsrv_fetch_array($end_time_stmt, SQLSRV_FETCH_ASSOC);
    $latest_end_time = $end_time_data['latest_end_time']->format('H:i:s');

    if (strtotime($current_time) < strtotime($latest_end_time)) {
        // Redirect to confirmation page
        header("Location: ../faculty/kiosk-confirm-time-out.php?rfid_no=" . urlencode($rfid_no) . "&attd_ref=" . urlencode($attendance['attd_ref']));
        exit();
    }

    // Update the record with the time_out if it's after the scheduled end time
    $update_query = "
        UPDATE AttendanceToday
        SET time_out = ?
        WHERE attd_ref = ?
    ";
    $update_params = [$current_time, $attendance['attd_ref']];
    $update_stmt = sqlsrv_query($conn, $update_query, $update_params);

    if ($update_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Update appointments
    $update_appointments_query = "
        UPDATE Appointments
        SET status = 'Canceled'
        WHERE prof_rfid_no = ? 
          AND date_logged = ?
          AND status IN ('Pending', 'Accepted')
    ";
    $update_appointments_params = [$rfid_no, $current_date];
    $update_appointments_stmt = sqlsrv_query($conn, $update_appointments_query, $update_appointments_params);

    if ($update_appointments_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Close the statements and connection
    sqlsrv_free_stmt($update_stmt);
    sqlsrv_free_stmt($update_appointments_stmt);
    sqlsrv_close($conn);

    // Redirect to success message with attd_ref
    header("Location: ../faculty/kiosk-time-out-info.php?rfid_no=" . urlencode($rfid_no) . "&attd_ref=" . urlencode($attendance['attd_ref']));
    exit();
}


// If no prior attendance record, process time-in logic
// Query to find the earliest schedule for today
$query = "
    SELECT TOP 1 sched_id, start_time, start_date
    FROM Schedules
    WHERE rfid_no = ? AND start_date = ?
    ORDER BY start_time ASC
";
$params = [$rfid_no, $current_date];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$schedule = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$schedule) {
    // No schedule found for today
    $_SESSION['error_message'] = "No schedule found for today.";
    header("Location: ../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
    exit();
}

// Format the start_time as a string
$start_time = $schedule['start_time']->format('H:i:s');

// Add a 15-minute grace period to the start time
$grace_time = date('H:i:s', strtotime($start_time . ' +15 minutes'));

// Determine the status based on the adjusted grace time
$status = (strtotime($current_time) > strtotime($grace_time)) ? 'Late' : 'Present';

// Insert attendance record into AttendanceToday table
$attd_ref = uniqid('ATTD_'); // Generate a unique attendance reference
$insert_query = "
    INSERT INTO AttendanceToday (attd_ref, rfid_no, time_in, status, date_logged)
    VALUES (?, ?, ?, ?, ?)
";
$insert_params = [$attd_ref, $rfid_no, $current_time, $status, $current_date];
$insert_stmt = sqlsrv_query($conn, $insert_query, $insert_params);

if ($insert_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Close the statement and connection
sqlsrv_free_stmt($stmt);
sqlsrv_free_stmt($insert_stmt);
sqlsrv_close($conn);

// Redirect to a success page with attd_ref
header("Location: ../faculty/kiosk-time-in-info.php?rfid_no=" . urlencode($rfid_no) . "&attd_ref=" . urlencode($attd_ref));
exit();
?>
