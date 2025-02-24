<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $rfid_no = filter_input(INPUT_POST, 'rfid_no', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_SANITIZE_NUMBER_INT);
    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_SANITIZE_NUMBER_INT);
    $subject_code = filter_input(INPUT_POST, 'subject_code', FILTER_SANITIZE_STRING);
    $repeat_frequency = filter_input(INPUT_POST, 'repeat_frequency', FILTER_SANITIZE_STRING);
    $days_of_week = isset($_POST['days_of_week']) ? $_POST['days_of_week'] : [];

    try {
        $startDateTime = new DateTime($start_date);
        $endDateTime = new DateTime($end_date);

        if ($endDateTime < $startDateTime) {
            throw new Exception("End date must be later than start date.");
        }

        // Define Philippine holidays
        $philippineHolidays = [
            '2025-01-01', // New Year's Day
            '2025-01-29', // Chinese New Year
            '2025-02-25', // EDSA People Power Revolution Anniversary
            '2025-04-09', // Araw ng Kagitingan
            '2025-04-17', // Maundy Thursday
            '2025-04-18', // Good Friday
            '2025-04-19', // Black Saturday
            '2025-05-01', // Labor Day
            '2025-06-12', // Independence Day
            '2025-08-21', // Ninoy Aquino Day
            '2025-08-25', // National Heroes Day
            '2025-10-31', // All Saints' Day Eve
            '2025-11-01', // All Saints' Day
            '2025-11-30', // Bonifacio Day
            '2025-12-08', // Feast of the Immaculate Conception of Mary
            '2025-12-24', // Christmas Eve
            '2025-12-25', // Christmas Day
            '2025-12-30', // Rizal Day
            '2025-12-31', // Last Day of the Year
        ];


        sqlsrv_begin_transaction($conn);

        $currentDate = clone $startDateTime;
        while ($currentDate <= $endDateTime) {
            $dayName = $currentDate->format('l');
            $dateFormatted = $currentDate->format('Y-m-d');

            // Skip Philippine holidays
            if (in_array($dateFormatted, $philippineHolidays)) {
                $currentDate->modify('+1 day');
                continue;
            }

            // Skip weekends **only if they are NOT selected**
            if (in_array($dayName, ['Saturday', 'Sunday']) && !in_array($dayName, $days_of_week)) {
                $currentDate->modify('+1 day');
                continue;
            }


            if (
                $repeat_frequency === 'Daily' ||
                ($repeat_frequency === 'Weekly' && in_array($dayName, $days_of_week)) ||
                ($repeat_frequency === 'Specific Days' && in_array($dayName, $days_of_week))
            ) {

                // Ensure end time is later than start time
                $startDateTimeObj = new DateTime("$dateFormatted $start_time");
                $endDateTimeObj = new DateTime("$dateFormatted $end_time");
                if ($endDateTimeObj <= $startDateTimeObj) {
                    throw new Exception("End time must be later than start time.");
                }

                // Break duration check (if applicable)
                $interval = $startDateTimeObj->diff($endDateTimeObj);
                $minutes = ($interval->h * 60) + $interval->i;
                if ($type === 'Break' && ($minutes < 15 || $minutes > 120)) {
                    throw new Exception("Breaks must be between 15 minutes and 2 hours.");
                }

                // Check room availability
                $sql_check_room = "SELECT COUNT(*) AS count FROM Schedules 
                                  WHERE room_id = ? AND start_date = ? 
                                  AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time))";
                $stmt_check_room = sqlsrv_query($conn, $sql_check_room, [$room_id, $dateFormatted, $start_time, $end_time]);
                $row_check_room = sqlsrv_fetch_array($stmt_check_room, SQLSRV_FETCH_ASSOC);
                if ($row_check_room['count'] > 0) {
                    throw new Exception("Room is already booked on " . $dateFormatted);
                }

                // Check faculty availability
                $sql_check_faculty = "SELECT COUNT(*) AS count FROM Schedules 
                      WHERE rfid_no = ? AND start_date = ? 
                      AND (
                          (? > start_time AND ? < end_time)  -- Start time falls inside an existing schedule
                          OR 
                          (? > start_time AND ? < end_time)  -- End time falls inside an existing schedule
                          OR 
                          (? <= start_time AND ? >= end_time) -- New schedule fully contains an existing schedule
                      )";
                $stmt_check_faculty = sqlsrv_query($conn, $sql_check_faculty, [
                    $rfid_no,
                    $dateFormatted,
                    $start_time,
                    $start_time,
                    $end_time,
                    $end_time,
                    $start_time,
                    $end_time
                ]);
                $row_check_faculty = sqlsrv_fetch_array($stmt_check_faculty, SQLSRV_FETCH_ASSOC);

                if ($row_check_faculty['count'] > 0) {
                    throw new Exception("Faculty member is already scheduled at this time.");
                }


                // Check section availability
                $sql_check_section = "SELECT COUNT(*) AS count FROM Schedules 
                                      WHERE section_id = ? AND start_date = ? 
                                      AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time))";
                $stmt_check_section = sqlsrv_query($conn, $sql_check_section, [$section_id, $dateFormatted, $start_time, $end_time]);
                $row_check_section = sqlsrv_fetch_array($stmt_check_section, SQLSRV_FETCH_ASSOC);
                if ($row_check_section['count'] > 0) {
                    throw new Exception("This section already has a scheduled class at this time.");
                }

                // Insert schedule
                $sql_insert = "INSERT INTO Schedules (rfid_no, type, start_date, end_date, start_time, end_time, room_id, section_id, subject_code, repeat_frequency) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params_insert = [$rfid_no, $type, $dateFormatted, $dateFormatted, $start_time, $end_time, $room_id, $section_id, $subject_code, $repeat_frequency];
                $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);
                if ($stmt_insert === false) {
                    throw new Exception(print_r(sqlsrv_errors(), true));
                }
            }

            $currentDate->modify('+1 day');
        }

        sqlsrv_commit($conn);
        $message = "Repeating schedule successfully added.";
        $type = "success";
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        $message = "Error: " . $e->getMessage();
        $type = "error";
    }

    header("Location: ../pages/admin-schedule.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>