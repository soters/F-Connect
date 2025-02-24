<?php
declare(strict_types=1);
session_start();
require_once('../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// RFID number and today's date
$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);
$today = date('Y-m-d');

// Fetch schedules
$schedulesQuery = "
    SELECT s.sched_id, s.start_date, s.end_date, s.start_time, s.end_time, 
           l.room_id, l.room_name, l.floor, sec.section_name, sub.subject_code, sub.subject_description 
    FROM Schedules s
    INNER JOIN Locations l ON s.room_id = l.room_id
    INNER JOIN Sections sec ON s.section_id = sec.section_id
    INNER JOIN Subjects sub ON s.subject_code = sub.subject_code
    WHERE s.rfid_no = ? AND s.start_date = ?";
$schedulesParams = [$rfid_no, $today];
$schedulesStmt = sqlsrv_query($conn, $schedulesQuery, $schedulesParams);

if ($schedulesStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$schedules = [];
while ($row = sqlsrv_fetch_array($schedulesStmt, SQLSRV_FETCH_ASSOC)) {
    $row['start_date'] = $row['start_date']->format('Y-m-d');
    $row['end_date'] = $row['end_date']->format('Y-m-d');
    $row['start_time'] = $row['start_time']->format('H:i:s');
    $row['end_time'] = $row['end_time']->format('H:i:s');
    $schedules[] = $row;
}

// Fetch appointments
$appointmentsQuery = "
    SELECT a.appointment_code, a.date_logged, a.start_time, a.end_time, a.status, a.agenda 
    FROM Appointments a
    WHERE a.prof_rfid_no = ? AND a.date_logged = ? AND a.status IN ('Accepted', 'Completed')";
$appointmentsParams = [$rfid_no, $today];
$appointmentsStmt = sqlsrv_query($conn, $appointmentsQuery, $appointmentsParams);

if ($appointmentsStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$appointments = [];
while ($row = sqlsrv_fetch_array($appointmentsStmt, SQLSRV_FETCH_ASSOC)) {
    $row['date_logged'] = $row['date_logged']->format('Y-m-d');
    $row['start_time'] = $row['start_time']->format('H:i:s');
    $row['end_time'] = $row['end_time']->format('H:i:s');
    $appointments[] = $row;
}

// Fetch faculty details
$facultyQuery = "
    SELECT fname, mname, lname, suffix, picture_path
    FROM Faculty
    WHERE rfid_no = ?";
$facultyParams = [$rfid_no];
$facultyStmt = sqlsrv_query($conn, $facultyQuery, $facultyParams);

if ($facultyStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$faculty = sqlsrv_fetch_array($facultyStmt, SQLSRV_FETCH_ASSOC);

if ($faculty) {
    $name = htmlspecialchars(trim($faculty['fname'] . ' ' . ($faculty['mname'] ? $faculty['mname'] . ' ' : '') . $faculty['lname'] . ' ' . ($faculty['suffix'] ?: '')));
    $picture = htmlspecialchars($faculty['picture_path'] ?: '../assets/images/Male_PF.png');
} else {
    $name = 'Unknown Faculty';
    $picture = '../assets/images/Male_PF.png';
}

if ($rfid_no) {
    // Query to get the note
    $sql = "SELECT note FROM FacultyNotes WHERE rfid_no = ?";
    $params = [$rfid_no];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch the note
    $note = null;
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $note = $row['note'];
    }

    // Free statement resources
    sqlsrv_free_stmt($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <link rel="stylesheet" href="../assets/css/kiosk-design.css" />
    <link rel="shortcut icon" href="../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
</head>

<body class="fade-out">

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>

    <div id="appointment-container">
        <?php
        // Check if both schedules and appointments are empty
        $noSchedules = empty($schedules);
        $noAppointments = empty($appointments);
        ?>

        <?php if ($noSchedules && $noAppointments): ?>
            <!-- Display message and image when no schedules and appointments -->
            <div class="no-schedules-appointments">
                <img src="../assets/images/calendar_3.png" alt="No schedules or appointments" class="no-sched-img">
                <p class="no-sched-app">No schedules and appointments for today.</p>
            </div>
        <?php else: ?>
            <!-- Display action message -->
            <p id="action-message-info">Today's Schedule</p>
            <i>
                <p id="action-message-info-small"></p>
            </i>

            <!-- Display teacher info -->
            <div class="t-info-box">
                <img src="<?php echo $picture; ?>" alt="Profile Img" class="t-avatar">
                <div class="details">
                    <h3 class="t-name"><?php echo $name; ?></h3>
                </div>
            </div>

            <!-- Display the note or "No notes provided." -->
            <?php if ($note): ?>
                <p class="s-note"><strong>Note: </strong> <?= htmlspecialchars($note) ?></p>
            <?php else: ?>
                <p class="s-note">No notes provided.</p>
            <?php endif; ?>

            <!-- Display schedules if available -->
            <?php if (!$noSchedules): ?>
                <i>
                    <p id="action-message-info-small"></p>
                </i>

                <div class="accordion">
                    <?php foreach ($schedules as $schedule): ?>
                        <?php
                        $currentTime = new DateTime();
                        $startTime = new DateTime($schedule['start_time']);
                        $endTime = new DateTime($schedule['end_time']);

                        if ($currentTime > $endTime) {
                            $status = "Finished";
                            $statusClass = "sc-finished";
                        } elseif ($currentTime >= $startTime && $currentTime <= $endTime) {
                            $status = "Ongoing";
                            $statusClass = "sc-ongoing";
                        } else {
                            $status = "Upcoming";
                            $statusClass = "sc-upcoming";
                        }
                        ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>Class at : <?= htmlspecialchars($schedule['section_name']) ?></span>
                                <span><?= date("h:i A", strtotime($schedule['start_time'])) ?> -
                                    <?= date("h:i A", strtotime($schedule['end_time'])) ?></span>
                                <button class="toggle-button">▼</button>
                            </div>
                            <div class="accordion-content">
                                <div class="sc-status <?= $statusClass ?>"> ● <?= $status ?></div>
                                <div class="sc-details">
                                    <p><strong>Location:</strong> <?= htmlspecialchars($schedule['room_name']) ?></p>
                                    <form action="./functions/check-floor.php" method="POST">
                                        <input type="hidden" name="rfid_no" value="<?= $rfid_no; ?>">
                                        <input type="hidden" name="room_id" value="<?= $schedule['room_id']; ?>">
                                        <!-- Pass room_id here -->
                                        <button class="view-map" type="submit">View in Map <i class="bi bi-map"></i></button>
                                    </form>
                                    <p><strong>Subject:</strong> <?= htmlspecialchars($schedule['subject_code']) ?> -
                                        <?= htmlspecialchars($schedule['subject_description']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Display appointments if available -->
            <?php if (!$noAppointments): ?>
                <div class="accordion">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $statusClass = '';
                        if ($appointment['status'] === 'Accepted') {
                            $statusClass = 'status-accepted';
                        } elseif ($appointment['status'] === 'Completed') {
                            $statusClass = 'status-completed';
                        }
                        ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <p><?= htmlspecialchars($appointment['agenda']) ?> at:</p>
                                <span><?= date("h:i A", strtotime($appointment['start_time'])) ?> -
                                    <?= date("h:i A", strtotime($appointment['end_time'])) ?></span>
                                <button class="toggle-button">▼</button>
                            </div>
                            <div class="accordion-content">
                                <div class="sc-details">
                                    <p><strong>Status:</strong> <span
                                            class="<?= $statusClass ?>"><?= htmlspecialchars($appointment['status']) ?></span></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
        </button>
    </div>

    <div id="top-left-button">
        <a href="kiosk-org-chart.php" class="no-underline">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back" data-bs-placement="right">
                <i class="bi bi-arrow-left-short"></i>
            </button>
        </a>
    </div>

    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="../../assets/js/custom-javascript.js"></script>

    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            });
        });

        const today = new Date();
        const formattedDate = today.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        document.getElementById('action-message-info-small').textContent = `Date: ${formattedDate}`;
    </script>
</body>

</html>