<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php'); // Update this path if needed

// Get the RFID number from the URL
$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

if (!$rfid_no) {
    $_SESSION['error_message'] = "RFID number is missing!";
    header("Location: ../kiosk-index.php");
    exit;
}

try {
    // Get today's date and current time
    $today = date('Y-m-d');
    $currentTime = date('H:i:s');

    // SQL query to check if the RFID has already timed in and out today
    $attendanceQuery = "
        SELECT time_in, time_out 
        FROM AttendanceToday 
        WHERE rfid_no = ? AND CONVERT(DATE, date_logged) = ?
    ";

    // Prepare and execute the statement for the attendance check
    $attendanceParams = [$rfid_no, $today];
    $attendanceStmt = sqlsrv_query($conn, $attendanceQuery, $attendanceParams);

    if ($attendanceStmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // Fetch the result for the attendance check
    $attendanceResult = sqlsrv_fetch_array($attendanceStmt, SQLSRV_FETCH_ASSOC);

    if ($attendanceResult && $attendanceResult['time_in'] !== null && $attendanceResult['time_out'] !== null) {
        // Time-in and time-out already recorded
        $_SESSION['error_message'] = "We’ve already recorded your Time In and Time Out for today.";
        header("Location: ../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
        exit;
    }

    // SQL query to check if the RFID has a schedule for today
    $scheduleQuery = "
        SELECT COUNT(*) AS count 
        FROM Schedules 
        WHERE rfid_no = ? AND start_date = ?
    ";
    
    // Prepare and execute the statement for the schedule check
    $scheduleParams = [$rfid_no, $today];
    $scheduleStmt = sqlsrv_query($conn, $scheduleQuery, $scheduleParams);

    if ($scheduleStmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // Fetch the result for the schedule check
    $scheduleResult = sqlsrv_fetch_array($scheduleStmt, SQLSRV_FETCH_ASSOC);

    if ($scheduleResult && $scheduleResult['count'] > 0) {
        // Check if the current time is past 9:00 PM
        if ($currentTime > '20:30:00') {
            $_SESSION['error_message'] = "School hours already ended.";
            header("Location: ../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
            exit;
        }

        // Proceed to check FaceData table
        $faceDataQuery = "SELECT COUNT(*) AS count FROM FaceData WHERE rfid_no = ?";
        $faceDataParams = [$rfid_no];
        $faceDataStmt = sqlsrv_query($conn, $faceDataQuery, $faceDataParams);

        if ($faceDataStmt === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        // Fetch the result for the FaceData check
        $faceDataResult = sqlsrv_fetch_array($faceDataStmt, SQLSRV_FETCH_ASSOC);

        if ($faceDataResult && $faceDataResult['count'] > 0) {
            // RFID exists in FaceData table, redirect to kiosk-detect-face.php
            header("Location: ../faculty/kiosk-detect-face.php?rfid_no=" . urlencode($rfid_no));
        } else {
            // RFID does not exist in FaceData table, redirect to kiosk-upload-face.php
            header("Location: ../faculty/kiosk-first-facial.php?rfid_no=" . urlencode($rfid_no));
        }
    } else {
        // No schedule for today, redirect to kiosk-faculty.php with an error message
        $_SESSION['error_message'] = "You do not have any scheduled classes for today.";
        header("Location: ../faculty/kiosk-faculty.php?rfid_no=" . urlencode($rfid_no));
    }
    exit;
} catch (Exception $e) {
    // Handle errors
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: ../kiosk-index.php");
    exit;
}
?>
