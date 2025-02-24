<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Retrieve student RFID (stud_rf) if it's set
$stud_rf = filter_input(INPUT_POST, 'stud_rf', FILTER_SANITIZE_STRING);

try {
    // Query to fetch attendance records with time_out IS NULL and date_logged is today
    $query = "
    SELECT 
        attd_ref,
        CONCAT(
            COALESCE(fname, ''), 
            ' ', 
            COALESCE(mname, ''), 
            ' ', 
            COALESCE(lname, ''), 
            COALESCE(CONCAT(' ', suffix), ' ')
        ) AS full_name, 
        picture_path, 
        AttendanceToday.rfid_no, 
        Faculty.rfid_no,
        status,
        time_in,
        time_out,
        date_logged
    FROM AttendanceToday
    JOIN Faculty ON AttendanceToday.rfid_no = Faculty.rfid_no
    WHERE time_out IS NULL
    AND CAST(date_logged AS DATE) = CAST(GETDATE() AS DATE)
    AND archived = 0
    ORDER BY date_logged DESC";


    // Execute the query with sqlsrv
    $stmt = sqlsrv_query($conn, $query);

    if ($stmt === false) {
        // If the query fails, output errors and stop execution
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // Fetch all results
    $attendanceRecords = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $attendanceRecords[] = $row;
    }

    // Check if no name is present, replace with suffix or a default value
    foreach ($attendanceRecords as &$record) {
        if (trim($record['full_name']) === '') {
            $record['full_name'] = $record['suffix'] ?? 'No Name Available';
        }
    }
    unset($record); // Unset reference to avoid potential issues

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
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css" />
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
        <p id="action-message-info">Book Appointment</p>
        <i>
            <p id="action-message-info-small">Select a Faculty Member</p>
        </i>
        <br>

        <!-- Faculty Selection Form -->
        <form action="kiosk-choose-time.php" method="POST">
            <!-- Hidden field to pass student RFID -->
            <input type="hidden" name="stud_rf" value="<?= htmlspecialchars($stud_rf); ?>">

            <?php if (!empty($attendanceRecords)): ?>
                <div class="faculty-cards">
                    <?php foreach ($attendanceRecords as $record): ?>
                        <label>
                            <input type="radio" name="selected_rfid" value="<?= htmlspecialchars($record['rfid_no']); ?>"
                                required>
                            <div class="fac-card">
                                <div class="temp">
                                    <img src="<?= htmlspecialchars($record['picture_path'] ?: '../assets/images/default-avatar.png'); ?>"
                                        alt="<?= htmlspecialchars($record['full_name'] ?? 'No Name Available'); ?>">
                                </div>
                                <div class="info">
                                    <p class="fac-name"><?= htmlspecialchars($record['full_name'] ?? 'No Name Available'); ?>
                                    </p>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- NEXT Button -->
                <button class="appoint-btn" type="submit">
                    <span class="btn-text">NEXT</span>
                </button>
            <?php else: ?>
                <div class="code-card">
                    <img src="../../assets/images/no_faculty.png" alt="" class="faculty-icon">
                    <p class="code-message">
                        No faculty members are available at the moment. Feel free to try again later.
                    </p>
                    <!-- OKAY Button -->
                    <button class="appoint-btn" type="button" onclick="window.location.href='kiosk-student.php'">
                        <span class="btn-text">OKAY</span>
                    </button>
                </div>
            <?php endif; ?>

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
        /** RFID Input Auto-Submit */
        const rfidInput = document.getElementById('rfid-id');
        const rfidForm = document.getElementById('rfid-form');

        if (rfidInput && rfidForm) {
            document.addEventListener('keydown', (event) => {
                if (event.target === document.body) {
                    const key = event.key;

                    if (key === 'Enter') {
                        // Submit the form when Enter is pressed
                        if (rfidInput.value.trim() !== '') {
                            rfidForm.submit();
                        }
                    } else {
                        // Append keystrokes to the hidden input field
                        rfidInput.value += key;
                    }
                }
            });
        }
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
</body>

</html>