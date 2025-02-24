<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Get the selected faculty's RFID
$selected_rfid = filter_input(INPUT_POST, 'selected_rfid', FILTER_SANITIZE_STRING);
$stud_rf = filter_input(INPUT_POST, 'stud_rf', FILTER_SANITIZE_STRING);
$today = date('Y-m-d'); // Get today's date

// Function to convert 24-hour time to 12-hour format
function convertTo12HourFormat($time)
{
    return date("g:i a", strtotime($time));
}

try {
    // SQL Server query to get the faculty's full name
    $query_faculty_name = "
        SELECT fname, mname, lname, suffix
        FROM Faculty
        WHERE rfid_no = ?
    ";

    // Prepare and execute the query
    $params = array($selected_rfid);
    $stmt_name = sqlsrv_query($conn, $query_faculty_name, $params);

    // Check if the query executed successfully
    if ($stmt_name === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch the faculty's full name
    $faculty_name = '';
    if ($row = sqlsrv_fetch_array($stmt_name, SQLSRV_FETCH_ASSOC)) {
        $faculty_name = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'] . ' ' . $row['suffix'];
    }

    // SQL Server query to get the faculty's schedule for today
    $query_schedule = "
        SELECT start_time, end_time
        FROM Schedules
        WHERE rfid_no = ? AND start_date = ?
    ";

    // Prepare and execute the query
    $stmt_schedule = sqlsrv_query($conn, $query_schedule, array($selected_rfid, $today));

    // Check if the query executed successfully
    if ($stmt_schedule === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch all schedules for the selected faculty
    $schedules = [];
    while ($row = sqlsrv_fetch_array($stmt_schedule, SQLSRV_FETCH_ASSOC)) {
        $schedules[] = $row;
    }

    // SQL Server query to get the faculty's appointments for today
    $query_appointments = "
    SELECT start_time, end_time
    FROM Appointments
    WHERE prof_rfid_no = ? 
    AND date_logged = ? 
    AND status IN ('Pending', 'Accepted')
";

    // Prepare and execute the query
    $stmt_appointments = sqlsrv_query($conn, $query_appointments, array($selected_rfid, $today));

    // Check if the query executed successfully
    if ($stmt_appointments === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch all appointments for the selected faculty
    $appointments = [];
    while ($row = sqlsrv_fetch_array($stmt_appointments, SQLSRV_FETCH_ASSOC)) {
        $appointments[] = $row;
    }

    // Create an array to store the unavailable time intervals (from both schedules and appointments)
    $unavailableTimes = [];
    foreach ($schedules as $schedule) {
        $start_time = $schedule['start_time']->format('H:i'); // Convert to 24-hour format
        $end_time = $schedule['end_time']->format('H:i'); // Convert to 24-hour format
        $unavailableTimes[] = ['start' => $start_time, 'end' => $end_time];
    }

    foreach ($appointments as $appointment) {
        $start_time = $appointment['start_time']->format('H:i'); // Convert to 24-hour format
        $end_time = $appointment['end_time']->format('H:i'); // Convert to 24-hour format
        $unavailableTimes[] = ['start' => $start_time, 'end' => $end_time];
    }

    // Generate available time slots
    $availableTimes = [];
    $startOfDay = strtotime("07:00 AM"); // Start time of the day (7:00 AM)
    $endOfDay = strtotime("08:00 PM"); // End time of the day (8:00 PM)

    // Get the current time
    $currentTime = time(); // Get current timestamp

    // Loop through the time slots and check availability
    for ($currentTimeSlot = $startOfDay; $currentTimeSlot < $endOfDay; $currentTimeSlot = strtotime("+1 hour", $currentTimeSlot)) {
        $slotStart = date("H:i", $currentTimeSlot);
        $slotEnd = date("H:i", strtotime("+1 hour", $currentTimeSlot));

        // Check if the current time slot is in the past
        if ($currentTimeSlot < $currentTime) {
            continue; // Skip this slot if it's already in the past
        }

        // Check if the current time slot overlaps with any unavailable time
        $isAvailable = true;
        foreach ($unavailableTimes as $unavailable) {
            // Exclude time slots that are within the range of the schedule (start_time to end_time)
            if (
                ($slotStart >= $unavailable['start'] && $slotStart < $unavailable['end']) ||
                ($slotEnd > $unavailable['start'] && $slotEnd <= $unavailable['end'])
            ) {
                $isAvailable = false;
                break;
            }
        }

        // If the time slot is available, add it to the availableTimes array
        if ($isAvailable) {
            $availableTimes[] = [
                'formatted' => convertTo12HourFormat($slotStart) . ' - ' . convertTo12HourFormat($slotEnd),
                'start_time' => $slotStart,
                'end_time' => $slotEnd
            ];
        }
    }
} catch (Exception $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css"/>
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
</head>

<body class="fade-out">

    <!-- Display Error Message -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="error-message alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>

    <div id="appointment-container">
        <p id="action-message-info">Schedule Your Appointment</p>
        <i>
            <p id="action-message-info-small">Booking an Appointment with
                <strong><?= htmlspecialchars($faculty_name); ?></strong>
            </p>
        </i>
        <br>

        <form action="kiosk-choose-agenda.php" method="POST">
            <div class="time-cards">
                <?php if (!empty($availableTimes)): ?>
                    <?php foreach ($availableTimes as $timeSlot): ?>
                        <label>
                            <input type="radio" name="selected_time" value="<?= htmlspecialchars($timeSlot['start_time']); ?>"
                                required data-start-time="<?= htmlspecialchars($timeSlot['start_time']); ?>"
                                data-end-time="<?= htmlspecialchars($timeSlot['end_time']); ?>"
                                data-formatted="<?= htmlspecialchars($timeSlot['formatted']); ?>">
                            <div class="time-card">
                                <div class="info">
                                    <p class="time-name"><?= htmlspecialchars($timeSlot['formatted']); ?></p>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                    <!-- Submit Button -->
                    <button class="appoint-btn" type="submit">
                        <span class="btn-text">NEXT</span>
                    </button>

                    <!-- Hidden inputs to pass selected rfid, stud_rf, and time slots -->
                    <input type="hidden" name="selected_rfid" value="<?= htmlspecialchars($selected_rfid); ?>">
                    <input type="hidden" name="stud_rf" value="<?= htmlspecialchars($stud_rf); ?>">
                    <input type="hidden" name="start_time" id="start_time">
                    <input type="hidden" name="end_time" id="end_time">
                <?php else: ?>
                    <div class="code-card">
                    <img src="../../assets/images/no_faculty.png" alt="" class="faculty-icon">
                    <p class="code-message">
                        No time slots available for today.
                    </p>
                    <!-- OKAY Button -->
                    <button class="appoint-btn" type="button" onclick="window.location.href='kiosk-student.php'">
                        <span class="btn-text">OKAY</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
        </button>
    </div>

    <div id="top-left-button">
        <a href="kiosk-student.php" class="no-underline">
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
    </script>
    <script>
        // Automatically hide the error message after 2 seconds
        setTimeout(() => {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.transition = 'opacity 0.5s ease';
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove(); // Remove the element completely after fade-out
                }, 500); // Delay to match the fade-out duration
            }
        }, 3000); // 2 seconds delay before hiding
    </script>

    <script>
        document.querySelectorAll('a.no-underline').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent immediate navigation
                const targetUrl = this.href; // Store the URL

                // Add the 'hidden' class to start the fade-out effect
                document.body.classList.add('hidden');

                // Wait for the transition to complete before navigating
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 500); // Match the CSS transition duration
            });
        });
    </script>

    <script>
        // Add an event listener to handle the selection of a radio button
        document.querySelectorAll('input[name="selected_time"]').forEach(function (radioButton) {
            radioButton.addEventListener('change', function () {
                // When a radio button is selected, update the hidden inputs with the corresponding times
                var startTime = this.getAttribute('data-start-time');
                var endTime = this.getAttribute('data-end-time');

                // Update the hidden fields with the selected times
                document.getElementById('start_time').value = startTime;
                document.getElementById('end_time').value = endTime;
            });
        });
    </script>
</body>
</html>