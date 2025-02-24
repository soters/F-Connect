<?php
include('../../connection/connection.php'); // DB Connection

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sched_id'])) {
    $sched_id = filter_input(INPUT_GET, 'sched_id', FILTER_SANITIZE_NUMBER_INT);

    try {
        // Step 1: Retrieve schedule details
        $sql_get_schedule = "SELECT * FROM Schedules WHERE sched_id = ?";
        $stmt_get_schedule = sqlsrv_query($conn, $sql_get_schedule, [$sched_id]);
        $schedule = sqlsrv_fetch_array($stmt_get_schedule, SQLSRV_FETCH_ASSOC);

        if (!$schedule) {
            throw new Exception("Schedule not found.");
        }

        // Step 2: Insert into backup table
        $sql_backup = "INSERT INTO SchedulesBackup (sched_id, type, start_date, end_date, start_time, end_time, rfid_no, room_id, section_id, subject_code, date_created, timed_in, timed_out, status)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params_backup = [
            $schedule['sched_id'], $schedule['type'], $schedule['start_date'], $schedule['end_date'], 
            $schedule['start_time'], $schedule['end_time'], $schedule['rfid_no'], $schedule['room_id'], 
            $schedule['section_id'], $schedule['subject_code'], $schedule['date_created'], 
            $schedule['timed_in'], $schedule['timed_out'], $schedule['status']
        ];
        $stmt_backup = sqlsrv_query($conn, $sql_backup, $params_backup);
        if (!$stmt_backup) {
            throw new Exception("Error backing up schedule: " . print_r(sqlsrv_errors(), true));
        }

        // Step 3: Delete from Schedules
        $sql_delete = "DELETE FROM Schedules WHERE sched_id = ?";
        $stmt_delete = sqlsrv_query($conn, $sql_delete, [$sched_id]);

        if (!$stmt_delete) {
            throw new Exception("Error deleting schedule: " . print_r(sqlsrv_errors(), true));
        }

        $message = "Schedule successfully deleted and backed up.";
        $type = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $type = "error";
    }

    header("Location: ../pages/admin-schedule.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit();
}
?>
