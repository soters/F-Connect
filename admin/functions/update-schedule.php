<?php
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $sched_id = filter_input(INPUT_POST, 'sched_id', FILTER_SANITIZE_NUMBER_INT);
    $rfid_no = filter_input(INPUT_POST, 'rfid_no', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_SANITIZE_NUMBER_INT);
    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_SANITIZE_NUMBER_INT);
    $subject_code = filter_input(INPUT_POST, 'subject_code', FILTER_SANITIZE_STRING);

    // Ensure end_date is the same as start_date
    $end_date = $start_date;

    // Convert times to DateTime for validation
    $startDateTime = new DateTime("$start_date $start_time");
    $endDateTime = new DateTime("$end_date $end_time");
    $interval = $startDateTime->diff($endDateTime);

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
    
    try {
        if (in_array($start_date, $philippineHolidays)) {
            throw new Exception("Cannot schedule on a Philippine holiday.");
        }

        if ($endDateTime <= $startDateTime) {
            throw new Exception("End time must be later than start time.");
        }

        if ($type === 'Break') {
            $minutes = ($interval->h * 60) + $interval->i;
            if ($minutes < 15 || $minutes > 120) {
                throw new Exception("Breaks must be between 15 minutes and 2 hours.");
            }
        }

        $queries = [
            "room" => ["SELECT COUNT(*) AS count FROM Schedules WHERE room_id = ? AND start_date = ? AND end_date = ? AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time)) AND sched_id != ?", [$room_id, $start_date, $end_date, $start_time, $end_time, $sched_id]],
            "faculty" => ["SELECT COUNT(*) AS count FROM Schedules WHERE rfid_no = ? AND start_date = ? AND end_date = ? AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time)) AND sched_id != ?", [$rfid_no, $start_date, $end_date, $start_time, $end_time, $sched_id]],
            "section" => ["SELECT COUNT(*) AS count FROM Schedules WHERE section_id = ? AND start_date = ? AND end_date = ? AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time)) AND sched_id != ?", [$section_id, $start_date, $end_date, $start_time, $end_time, $sched_id]]
        ];

        foreach ($queries as $key => [$sql, $params]) {
            $stmt = sqlsrv_query($conn, $sql, $params);
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row['count'] > 0) {
                throw new Exception(ucfirst($key) . " is already scheduled at this time.");
            }
        }

        // Update schedule in database
        $sql_update = "UPDATE Schedules SET rfid_no = ?, type = ?, start_date = ?, end_date = ?, start_time = ?, end_time = ?, room_id = ?, section_id = ?, subject_code = ? WHERE sched_id = ?";
        $params_update = [$rfid_no, $type, $start_date, $end_date, $start_time, $end_time, $room_id, $section_id, $subject_code, $sched_id];
        $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

        if ($stmt_update === false) {
            throw new Exception(print_r(sqlsrv_errors(), true));
        }

        $message = "Schedule successfully updated.";
        $type = "success";
        sqlsrv_commit($conn);
    } catch (Exception $e) {
        sqlsrv_rollback($conn);
        $message = "Error: " . $e->getMessage();
        $type = "error";
    }

    header("Location: ../pages/admin-schedule.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
