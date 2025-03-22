<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');

// PHP Logic: Retrieve and validate RFID number
$rfid_no = filter_input(INPUT_GET, 'rfid_no', FILTER_SANITIZE_STRING);

if (!$rfid_no) {
    $_SESSION['error_message'] = 'Invalid RFID. Please try again.';
    header('Location: kiosk-student-rfid.php');
    exit;
}

try {
    // Fetch student data based on RFID
    $query = "SELECT student_number, 
                     CONCAT(fname, ' ', COALESCE(mname, ''), ' ', lname, ' ', COALESCE(suffix, '')) AS full_name, 
                     email, 
                     section_name AS section
              FROM Students 
              JOIN Sections ON students.section_id = sections.section_id
              WHERE rfid_no = ?";
    $params = [$rfid_no];

    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    $student = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error_message'] = 'RFID does not exist in the student database.';
        header('Location: kiosk-student-rfid.php');
        exit;
    }

} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred. Please contact the administrator.';
    header('Location: kiosk-student-rfid.php');
    exit;
}

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
        <p id="action-message-info">Your Information</p>
        <i>
            <p id="action-message-info-small">Linked to Your RFID</p>
        </i>
        <br>
        <form id="student-form" method="POST" action="kiosk-choose-faculty.php">
            <!-- Hidden input for RFID -->
            <input type="hidden" name="stud_rf" value="<?= htmlspecialchars($rfid_no, ENT_QUOTES, 'UTF-8'); ?>">

            <p id="input-info">Student Number:</p>
            <input id="student-info" name="student_number"
                value="<?= htmlspecialchars($student['student_number'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

            <p id="input-info">Name:</p>
            <input id="student-info" name="full_name"
                value="<?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

            <p id="input-info">Email:</p>
            <input id="student-info" name="email"
                value="<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

            <p id="input-info">Section:</p>
            <input id="student-info" name="section"
                value="<?= htmlspecialchars($student['section'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

            <!-- Submit Button -->
            <button class="appoint-btn" type="submit">
                <span class="btn-text">NEXT</span>
            </button>
        </form>

    </div>

    <!--div id="top-right-button">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?"
                data-bs-placement="left">
                <i class="bi bi-question-lg"></i>
        </div>-->

    <div id="top-left-button">
        <a href="kiosk-student.php?rfid_no=<?= urlencode($rfid_no) ?>" class="no-underline">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back" data-bs-placement="right">
                <i class="bi bi-arrow-left-short"></i>
            </button>
        </a>
    </div>

    <!--<footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>-->

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