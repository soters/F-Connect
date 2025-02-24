<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');


// Get POST data
$selected_rfid = $_POST['selected_rfid'];
$stud_rf = $_POST['stud_rf'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$selected_agenda = $_POST['selected_agenda'];

// Convert start_time and end_time to 12-hour format with AM/PM
$start_time_12hr = date("h:i A", strtotime($start_time));
$end_time_12hr = date("h:i A", strtotime($end_time));

// Query for professor's full name and account type
$sql_professor = "SELECT CONCAT(fname, ' ', mname, ' ', lname, ' ', suffix) AS full_name, acc_type 
                  FROM Faculty 
                  WHERE rfid_no = ?";
$params_professor = array($selected_rfid);
$stmt_professor = sqlsrv_query($conn, $sql_professor, $params_professor);

if ($stmt_professor === false) {
    die(print_r(sqlsrv_errors(), true));
}

$professor = sqlsrv_fetch_array($stmt_professor, SQLSRV_FETCH_ASSOC);

// Get professor's full name and account type
$professor_full_name = $professor['full_name'];
$professor_acc_type = $professor['acc_type'];

// Query for student's full name, section name, and other details
$sql_student = "SELECT CONCAT(fname, ' ', mname, ' ', lname, ' ', suffix) AS full_name, student_number, s.section_id, s.email, sec.section_name 
                FROM Students s
                JOIN Sections sec ON s.section_id = sec.section_id
                WHERE s.rfid_no = ?";
$params_student = array($stud_rf);
$stmt_student = sqlsrv_query($conn, $sql_student, $params_student);

if ($stmt_student === false) {
    die(print_r(sqlsrv_errors(), true));
}
$student = sqlsrv_fetch_array($stmt_student, SQLSRV_FETCH_ASSOC);

// Get student's full name and other details
$student_full_name = $student['full_name'];
$student_number = $student['student_number'];
$student_section = $student['section_name']; // Section name from Sections table
$student_email = $student['email'];

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
        <form action="../functions/insert-appointment.php" method="POST">
            <p id="action-message-info">Appointment Summary</p>
            <i>
                <p id="action-message-info-small">Here's What You've Selected</p>
            </i>
            <br>
            <div class="schedule-card">
                <div class="time">
                    <p><?= htmlspecialchars($start_time_12hr) ?></p>
                    <p><?= htmlspecialchars($end_time_12hr) ?></p>
                </div>
                <div class="details">
                    <h4><?= htmlspecialchars($selected_agenda) ?></h4>
                    <p class="with">With</p>
                    <div class="professor-info">
                        <img src="../../assets/images/Prof.png" alt="Professor" class="professor-img">
                        <div>
                            <p class="professor-name"><?= htmlspecialchars($professor_full_name) ?></p>
                            <p class="professor-title">(<?= htmlspecialchars($professor_acc_type) ?>)</p>
                        </div>
                    </div>
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

            <!-- Hidden Inputs -->
            <input type="hidden" name="selected_rfid" value="<?= htmlspecialchars($selected_rfid) ?>">
            <input type="hidden" name="stud_rf" value="<?= htmlspecialchars($stud_rf) ?>">
            <input type="hidden" name="start_time" value="<?= htmlspecialchars($start_time) ?>">
            <input type="hidden" name="end_time" value="<?= htmlspecialchars($end_time) ?>">
            <input type="hidden" name="selected_agenda" value="<?= htmlspecialchars($selected_agenda) ?>">


            <!-- Submit Button -->
            <button class="appoint-btn" type="submit">
                <span class="btn-text">NEXT</span>
            </button>
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