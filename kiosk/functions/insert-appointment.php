<?php
session_start();
require_once('../../connection/connection.php'); // Ensure this file establishes the $conn variable for sqlsrv

try {
    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve POST data
        $selected_rfid = htmlspecialchars($_POST['selected_rfid']);
        $stud_rf = htmlspecialchars($_POST['stud_rf']);
        $start_time = htmlspecialchars($_POST['start_time']);
        $end_time = htmlspecialchars($_POST['end_time']);
        $selected_agenda = htmlspecialchars($_POST['selected_agenda']);

        // Get today's date in the format YYYY-MM-DD
     

        // Check if the user already has two appointments for today
        $count_sql = "SELECT COUNT(*) AS appointment_count FROM Appointments 
                      WHERE stud_rfid_no = ? 
                      AND CONVERT(date, date_logged) = ? 
                      AND status NOT IN ('Cancelled', 'Declined')";
        $count_params = [$stud_rf, $today_date];
        $count_stmt = sqlsrv_query($conn, $count_sql, $count_params);

        if ($count_stmt === false) {
            throw new Exception("Error checking appointment count. " . print_r(sqlsrv_errors(), true));
        }

        $count_result = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC);
        if ($count_result && $count_result['appointment_count'] >= 2) {
            $_SESSION['error_message'] = 'You can only create up to 2 appointments per day. Please try again tomorrow.';
            header("Location: ../student/kiosk-rfid.php");
            exit;
        }

        // Check if an appointment already exists for the same professor, student, today's date
        $check_sql = "SELECT * FROM Appointments 
              WHERE prof_rfid_no = ? 
              AND stud_rfid_no = ? 
              AND CONVERT(date, date_logged) = ? 
              AND (status = 'Pending' OR status = 'Accepted')";
        $check_params = [$selected_rfid, $stud_rf, $today_date];
        $check_stmt = sqlsrv_query($conn, $check_sql, $check_params);

        if ($check_stmt === false) {
            throw new Exception("Error checking existing appointments. " . print_r(sqlsrv_errors(), true));
        }

        // If a matching appointment exists, retrieve the appointment_code and handle redirection
        if ($row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC)) {
            $appointment_code = $row['appointment_code']; // Retrieve the appointment_code
            $status = $row['status']; // Retrieve the status of the appointment

            if ($status === 'Accepted') {
                // Redirect to another page for accepted appointments
                header("Location: ../student/kiosk-accepted-appt.php?selected_rfid=" . urlencode($selected_rfid) .
                    "&stud_rf=" . urlencode($stud_rf) .
                    "&appointment_code=" . urlencode($appointment_code));
                exit;
            } elseif ($status === 'Pending') {
                // Redirect to the existing page for pending appointments
                header("Location: ../student/kiosk-pending-appt.php?selected_rfid=" . urlencode($selected_rfid) .
                    "&stud_rf=" . urlencode($stud_rf) .
                    "&appointment_code=" . urlencode($appointment_code));
                exit;
            }
        }

        // Check for overlapping appointments for the student, excluding Cancelled and Declined statuses
        $overlap_sql = "SELECT * FROM Appointments 
                WHERE stud_rfid_no = ? 
                AND CONVERT(date, date_logged) = ? 
                AND status NOT IN ('Cancelled', 'Declined') 
                AND (
                    (start_time <= ? AND end_time > ?) OR 
                    (start_time < ? AND end_time >= ?) OR 
                    (start_time >= ? AND end_time <= ?)
                )";
        $overlap_params = [
            $stud_rf,
            $today_date,
            $start_time,
            $start_time,
            $end_time,
            $end_time,
            $start_time,
            $end_time
        ];
        $overlap_stmt = sqlsrv_query($conn, $overlap_sql, $overlap_params);

        if ($overlap_stmt === false) {
            throw new Exception("Error checking overlapping appointments. " . print_r(sqlsrv_errors(), true));
        }

        // If an overlapping appointment exists, redirect with an error message
        if (sqlsrv_fetch_array($overlap_stmt, SQLSRV_FETCH_ASSOC)) {
            $_SESSION['error_message'] = 'You already have an appointment that overlaps with the selected time. Please choose a different time.';
            header("Location: ../student/kiosk-rfid.php");
            exit;
        }

        // Generate a 12-digit random number for appointment_code
        $appointment_code = str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);

        // Set the status to "Pending"
        $status = "Pending";

        // Prepare the SQL query to insert the new appointment
        $sql = "INSERT INTO Appointments (appointment_code, prof_rfid_no, stud_rfid_no, start_time, end_time, agenda, status, date_logged)
                VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())";

        // Prepare the parameters for the insert query
        $params = [
            $appointment_code,
            $selected_rfid,
            $stud_rf,
            $start_time,
            $end_time,
            $selected_agenda,
            $status
        ];

        // Execute the insert query
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            throw new Exception("Failed to insert appointment. " . print_r(sqlsrv_errors(), true));
        } else {
            // Redirect to a success page with the appointment_code
            header("Location: ../student/kiosk-code.php?appointment_code=" . urlencode($appointment_code));
            exit;
        }
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    // Handle errors
    echo "Error: " . $e->getMessage();
}
?>
