<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// Get the selected RFID, student RFID, and appointment code from the URL
$prof_rfid_no = htmlspecialchars($_GET['selected_rfid']);
$stud_rfid_no = htmlspecialchars($_GET['stud_rf']);
$appointment_code = htmlspecialchars($_GET['appointment_code']);

// Get today's date in the format 'YYYY-MM-DD'
$today = date('Y-m-d');

// Query to get appointment details
$sql_appointment = "SELECT a.start_time, a.end_time, a.agenda, a.appointment_code, a.status, 
                    f.fname, f.mname, f.lname, f.suffix, f.acc_type, f.picture_path AS professor_picture_path,
                    s.fname AS student_fname, s.mname AS student_mname, s.lname AS student_lname, 
                    s.student_number, sec.section_name, s.email, s.picture_path AS student_picture_path
                    FROM Appointments a
                    JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
                    JOIN Students s ON a.stud_rfid_no = s.rfid_no
                    JOIN Sections sec ON s.section_id = sec.section_id
                    WHERE a.prof_rfid_no = ? 
                    AND a.stud_rfid_no = ? 
                    AND CONVERT(date, a.date_logged) = ? 
                    AND a.appointment_code = ?";


// Bind parameters
$params = [$prof_rfid_no, $stud_rfid_no, $today, $appointment_code];

// Execute the query
$stmt = sqlsrv_query($conn, $sql_appointment, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the results
$appointment = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$appointment) {
    die('No appointment found.');
}

// Get appointment details
$appointment_code = $appointment['appointment_code'];
$start_time_12hr = date("h:i A", strtotime($appointment['start_time']->format('H:i:s')));
$end_time_12hr = date("h:i A", strtotime($appointment['end_time']->format('H:i:s')));
$agenda = $appointment['agenda'];
$status = $appointment['status'];

// Map statuses to their corresponding classes
$status_classes = [
    'Pending' => 'status-pending',
    'Accepted' => 'status-accepted',
    'Completed' => 'status-completed',
    'Declined' => 'status-declined',
    'Cancelled' => 'status-cancelled',
];

// Get the class for the current status or use a default class
$status_class = $status_classes[$status] ?? 'status-default';

// Get professor's full name, account type, and picture path
$professor_full_name = trim("{$appointment['fname']} {$appointment['mname']} {$appointment['lname']} {$appointment['suffix']}");
$professor_acc_type = $appointment['acc_type'];
$professor_picture_path = $appointment['professor_picture_path'];

// Get student's full name, other details, and picture path
$student_full_name = trim("{$appointment['student_fname']} {$appointment['student_mname']} {$appointment['student_lname']}");
$student_number = $appointment['student_number'];
$student_section = $appointment['section_name'];
$student_email = $appointment['email'];
$student_picture_path = $appointment['student_picture_path'];

// Close the connection
sqlsrv_close($conn);
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
        <form action="" method="POST">
            <p id="action-message-info">Heads Up! Appointment Already Exists</p>
            <i>
                <p id="action-message-info-small">You’ve already scheduled an appointment with
                    <b><?= htmlspecialchars($professor_full_name) ?></b> today.
                </p>
            </i>
            <p id="action-message-info-medium">Kindly check your existing appointment below before proceeding.</p>

            <br>
            <div class="schedule-card">
                <div class="time">
                    <p><?= htmlspecialchars($start_time_12hr) ?></p>
                    <p><?= htmlspecialchars($end_time_12hr) ?></p>
                </div>
                <div class="details">
                    <p class="status-text"> STATUS:</p>
                    <div class="appointment-status <?= htmlspecialchars($status_class) ?>">
                        <p><?= htmlspecialchars($status) ?></p>
                    </div>
                    <h4><?= htmlspecialchars($agenda) ?></h4>
                    <p class="with">With</p>
                    <div class="professor-info">
                        <img src="<?php echo htmlspecialchars($professor_picture_path); ?>" alt="Professor's Picture"
                            class="professor-img">
                        <div>
                            <p class="professor-name"><?= htmlspecialchars($professor_full_name) ?></p>
                            <p class="professor-title">(<?= htmlspecialchars($professor_acc_type) ?>)</p>
                        </div>
                    </div>
                    <p class="appointment-code-small"><strong>Appointment Code:</strong>
                        <?= htmlspecialchars($appointment_code) ?></p>
                </div>
            </div>

            <div class="personal-info-card">
                <h4>Your Personal Information</h4>
                <ul>
                    <li><strong>Name:</strong> <?= htmlspecialchars($student_full_name) ?></li>
                    <li><strong>Stud Number:</strong> <?= htmlspecialchars($student_number) ?></li>
                    <li><strong>Section:</strong> <?= htmlspecialchars($student_section) ?></li>
                    <li><strong>Email:</strong> <?= htmlspecialchars($student_email) ?></li>
                </ul>
            </div>

            <!-- Hidden input to pass the appointment code -->
            <input type="hidden" name="appointment_code" value="<?= htmlspecialchars($appointment_code) ?>">

            <a href="kiosk-student.php" class="no-underline">
                <button class="appoint-btn okay-btn" type="button">
                    <span class="btn-text">OKAY</span>
                </button>
            </a>
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
    <script type="text/javascript" src="../js/custom-javascript.js"></script>

    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>