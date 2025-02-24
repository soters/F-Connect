<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// PHP Logic: Retrieve and validate RFID number
$stud_rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

if (!$stud_rfid_no) {
    $_SESSION['error_message'] = 'Invalid RFID. Please try again.';
    header('Location: kiosk-rfid-2.php');
    exit;
}

// Verify RFID exists in Students table
$verify_query = "SELECT 1 FROM Students WHERE rfid_no = ?";
$verify_stmt = sqlsrv_query($conn, $verify_query, [$stud_rfid_no]);

if ($verify_stmt === false || !sqlsrv_fetch_array($verify_stmt)) {
    $_SESSION['error_message'] = 'RFID not found in the system.';
    header('Location: kiosk-rfid-2.php');
    exit;
}

// Fetch appointments for the given RFID and today's date
$query = "
    SELECT 
        a.appointment_code, 
        a.status, 
        a.start_time, 
        a.end_time, 
        a.agenda, 
        CONCAT(f.fname, ' ', f.lname) AS professor_full_name, 
        f.acc_type AS professor_acc_type,
        f.picture_path AS professor_picture_path
    FROM Appointments a
    JOIN Faculty f ON a.prof_rfid_no = f.rfid_no
    WHERE a.stud_rfid_no = ? 
      AND CONVERT(date, a.date_logged) = CONVERT(date, GETDATE())
      AND a.status IN ('Pending', 'Accepted', 'Completed')
";

$params = [$stud_rfid_no];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    $_SESSION['error_message'] = 'Error retrieving appointments. Please try again.';
    header('Location: kiosk-rfid-2.php');
    exit;
}

$appointments = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $appointments[] = [
        'appointment_code' => $row['appointment_code'],
        'status' => $row['status'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'agenda' => $row['agenda'],
        'professor_full_name' => $row['professor_full_name'],
        'professor_acc_type' => $row['professor_acc_type'],
        'professor_picture_path' => $row['professor_picture_path'],
    ];
}

// Map statuses to their corresponding classes
$status_classes = [
    'Pending' => 'status-pending',
    'Accepted' => 'status-accepted',
    'Completed' => 'status-completed',
];
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
        <p id="action-message-info">Your Appointments</p>
        <i>
            <p id="action-message-info-small">Here’s your appointment list. Feel free to browse through it.</p>
            <br>
        </i>
        <?php if (!empty($appointments)): ?>
            <?php foreach ($appointments as $appointment): ?>
                <div class="schedule-card">
                    <div class="time">
                        <p><?= htmlspecialchars($appointment['start_time']->format("h:i A")) ?></p>
                        <p><?= htmlspecialchars($appointment['end_time']->format("h:i A")) ?></p>
                    </div>
                    <div class="details">
                        <p class="status-text">STATUS:</p>
                        <div class="appointment-status <?= $status_classes[$appointment['status']] ?? 'status-default' ?>">
                            <p><?= htmlspecialchars($appointment['status']) ?></p>
                        </div>
                        <h4><?= htmlspecialchars($appointment['agenda']) ?></h4>
                        <p class="with">With</p>
                        <div class="professor-info">
                        <img src="<?php echo htmlspecialchars($appointment['professor_picture_path']); ?>" alt="Professor Picture" class="professor-img">
                            <div>
                                <p class="professor-name"><?= htmlspecialchars($appointment['professor_full_name']) ?></p>
                                <p class="professor-title">(<?= htmlspecialchars($appointment['professor_acc_type']) ?>)</p>
                            </div>
                        </div>
                        <p class="appointment-code-small"><strong>Appointment Code:</strong>
                            <?= htmlspecialchars($appointment['appointment_code']) ?></p>

                        <!-- Cancel Button Form -->
                        <?php if ($appointment['status'] === 'Pending'): ?>
                            <form action="kiosk-cancel-appt.php" method="POST" class="cancel-form">
                                <input type="hidden" name="appointment_code"
                                    value="<?= htmlspecialchars($appointment['appointment_code']) ?>">
                                <button type="submit" class="cancel-button">
                                    Cancel Appointment
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="code-card">
                <img src="../../assets/images/calendar_3.png" alt="" class="faculty-icon-big">
                <p class="code-message">
                    No appointments found for today. Try to create one
                </p>
                <!-- OKAY Button -->
                <button class="appoint-btn" type="button" onclick="window.location.href='kiosk-student.php'">
                    <span class="btn-text">OKAY</span>
                </button>
            </div>
        <?php endif; ?>
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
        document.querySelectorAll('a.no-underline').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetUrl = this.href;
                document.body.classList.add('hidden');
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 500);
            });
        });
    </script>

</body>

</html>