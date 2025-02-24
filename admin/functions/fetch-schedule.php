<?php
include('../../connection/connection.php');
header('Content-Type: application/json'); // Ensure proper JSON response
date_default_timezone_set('Asia/Manila');

if (isset($_GET['rfid_no'])) {
    $rfid_no = $_GET['rfid_no'];

    $sql = "SELECT 
                s.sched_id AS id, 
                CONCAT(COALESCE(s.subject_code, ''), ' ', s.type) AS title,
                CONCAT(CONVERT(VARCHAR, s.start_date, 23), 'T', CONVERT(VARCHAR, s.start_time, 8)) AS start,
                CONCAT(CONVERT(VARCHAR, s.end_date, 23), 'T', CONVERT(VARCHAR, s.end_time, 8)) AS [end],
                s.type,
                s.start_date,
                s.end_date,
                s.start_time,
                s.end_time,
                s.room_id,
                COALESCE(l.room_name, 'N/A') AS room_name,  -- Handle NULL
                s.section_id,
                COALESCE(sec.section_name, 'N/A') AS section_name, -- Handle NULL
                s.subject_code,
                COALESCE(sub.subject_description, 'N/A') AS subject_description -- Handle NULL
            FROM Schedules s
            LEFT JOIN Locations l ON s.room_id = l.room_id  -- Use LEFT JOIN to include NULLs
            LEFT JOIN Sections sec ON s.section_id = sec.section_id
            LEFT JOIN Subjects sub ON s.subject_code = sub.subject_code
            WHERE s.rfid_no = ?";

    $params = array($rfid_no);
    $query = sqlsrv_query($conn, $sql, $params);

    if ($query === false) {
        echo json_encode(['error' => sqlsrv_errors()]);
        exit;
    }

    $events = array();
    while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
        // Ensure proper formatting
        $events[] = array(
            'id' => $row['id'],
            'title' => ($row['type'] == 'Break') ? 'Break Time' : $row['title'],
            'start' => $row['start'],
            'end' => $row['end'],
            'color' => ($row['type'] == 'Break') ? '#FFA500' : '#3788d8', // Orange for Breaks, Blue for others
            'extendedProps' => [
                'sched_id' => $row['id'], // Include sched_id
                'type' => $row['type'],
                'start_date' => $row['start_date']->format('Y-m-d'),
                'end_date' => $row['end_date']->format('Y-m-d'),
                'start_time' => $row['start_time']->format('H:i:s'),
                'end_time' => $row['end_time']->format('H:i:s'),
                'room_name' => $row['room_name'], 
                'section_name' => $row['section_name'],
                'subject_code' => $row['subject_code'] ?? 'N/A',
                'subject_description' => $row['subject_description']
            ]
        );
    }

    echo json_encode($events);
    exit;
}
?>
